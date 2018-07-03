<?php
namespace xepan\accounts;
class page_daybook extends \xepan\base\Page{
	public $title="Account DayBook";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->setLayout('view/form/daybookstatement-grid-info-form');
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addSubmit('Open Day Book')->addClass('btn btn-primary');

		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No day book statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false
				],null,['view/daybookstatement-grid']);
			
		$transaction = $this->add('xepan\accounts\Model_Transaction');
		$transaction->getElement('exchange_rate')->destroy();
		$transaction_r_j = $transaction->join('account_transaction_row.transaction_id','id');
		$transaction_r_j->addField('ledger_id');		
		$transaction_r_j->addField('exchange_rate');
		$transaction_r_j->addField('original_amount_dr','_amountDr');
		$transaction_r_j->addField('original_amount_cr','_amountCr');

		$transaction->addExpression('amountDr')->set($transaction->dsql()->expr('round(([0]*[1]),2)',[$transaction->getElement('original_amount_dr'),$transaction->getElement('exchange_rate')]));
		$transaction->addExpression('amountCr')->set($transaction->dsql()->expr('round(([0]*[1]),2)',[$transaction->getElement('original_amount_cr'),$transaction->getElement('exchange_rate')]));
		$transaction->addExpression('related_ledger_name')->set(function($m,$q){
			return $this->add('xepan\accounts\Model_Ledger')
						->addCondition('id',$m->getElement('ledger_id'))
						->setLimit(1)
						->fieldQuery('name');
		});	

		$transaction->addExpression('doc_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\base\Model_Document_Attachment')
								->addCondition('document_id',$m->getElement('related_id'));		
			return $doc_attachment_m->count();
		});

		$transaction->addExpression('trans_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\accounts\Model_Transaction_Attachment')
								->addCondition('account_transaction_id',$m->getElement('id'));		
			return $doc_attachment_m->count();
		});

		$transaction->getElement('attachments_count')->destroy();
		$transaction->addExpression('attachments_count')->set(function($m,$q){
			return $q->expr('([0]+[1])',[$m->getElement('doc_attachment_count'), $m->getElement('trans_attachment_count')]);
		});

		if($_GET['date_selected']){
			$transaction->addCondition('created_at','>=',$_GET['date_selected']);
			$transaction->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}else{			
			$transaction->addCondition('created_at','>=',$this->api->today);
			$transaction->addCondition('created_at','<',$this->app->nextDate($this->api->today));
		}
		
		if(!$crud->isEditing()){
			$grid = $crud->grid;
			$grid->addSno();
			$grid->removeColumn('account');

			$grid->js('click')->_selector('.do-view-attachment')->univ()
					->frameURL('Attachments',[$this->api->url
					('xepan_accounts_accounttransaction_attachment'),'account_transaction_id'=>$this->js()
					->_selectorThis()->closest('[data-id]')->data('id')]);
					
			$grid->current_transaction_id=null;
			
			$grid->addHook('formatRow',function($g){

				$g->current_row_html['created_at'] = date('d-M-y',strtotime($g->model['created_at']));

				if(!$g->model['transaction_template_id']){
					$g->current_row_html['edit'] = '<span class="fa-stack table-link"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>';				
					$g->current_row_html['delete'] = '<span class="table-link fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>';				
				}

				if($g->model->customer()){
					$g->current_row_html['transaction_type']=$g->model['transaction_type']." :: ".$g->model->customer()->get('organization_name');
				}else
					$g->current_row_html['transaction_type']=$g->model['transaction_type'];

				if(!$g->model['original_amount_cr']){								
					$g->current_row_html['currency_cr'] = ' ';
				}else{
					$g->current_row_html['currency_cr'] = $g->model['currency'];
				}

				if(!$g->model['original_amount_dr']){								
					$g->current_row_html['currency_dr'] = ' ';
				}else{
					$g->current_row_html['currency_dr'] = $g->model['currency'];
				}

				if($g->model['currency_id'] == $this->app->epan->default_currency->id){
					$g->current_row_html['currency_dr'] = ' ';
					$g->current_row_html['original_amount_dr'] = ' ';
					$g->current_row_html['currency_cr'] = ' ';
					$g->current_row_html['original_amount_cr'] = ' ';
				}

				if($g->current_transaction_id == $g->model->id){
					$g->current_row_html['created_at']='';
					$g->current_row_html['voucher_no']='';
					$g->current_row_html['s_no']='';
					$g->current_row_html['transaction_type']='';
					$g->current_row_html['Narration']='';
					$g->current_row_html['edit']=' ';
					$g->current_row_html['delete']=' ';
					$g->sno--;
				}

				$g->current_transaction_id = $g->model->id;

			});
		}

		if($crud->isEditing()){
			$transaction->load($crud->id);
		}	


		$crud->setModel($transaction);
		if($crud->acl_controller->canEdit()){
			$crud->grid->add('VirtualPage')
		 		->addColumn('edit_transaction')
				->set(function($page){
					$id = $_GET[$page->short_name.'_id'];
					$model = $page->add('xepan\accounts\Model_Transaction')->load($id);
					$widget = $page->add('xepan\accounts\View_TransactionWidget');
					$widget->setModel($model);
				});
		}

		$crud->grid->addQuickSearch(['name','Narration','transaction_type','related_type']);

		if($form->isSubmitted()){
			$crud->js()->reload(['date_selected'=>$form['date']?:0])->execute();
		}
	}
}