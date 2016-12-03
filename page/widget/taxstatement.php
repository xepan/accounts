<?php
namespace xepan\accounts;
class page_widget_taxstatement extends \xepan\base\Page {
	
	public $title="Account Statement";
	
	function init(){
		parent::init();

		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		$monthyear = $this->api->stickyGET('monthyear');
		
		$ledger_m = $this->add('xepan\accounts\Model_Ledger');
		$ledger_m->addCondition('group','Tax Payable');
		$ledger_m->addCondition('ledger_type','SalesServiceTaxes');

		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No account statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false
				],null,['view/accountstatement-grid']);

		$transactions = $this->add('xepan\accounts\Model_Transaction');
		$transactions->getElement('exchange_rate')->destroy();
		$trow_j = $transactions->join('account_transaction_row.transaction_id');
		$trow_j->addField('exchange_rate');
		$trow_j->addField('original_amount_dr','_amountDr');
		$trow_j->addField('original_amount_cr','_amountCr');
		$trow_j->addField('ledger_id');

		$transactions->addExpression('amountDr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_dr'),$transactions->getElement('exchange_rate')]));
		$transactions->addExpression('amountCr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_cr'),$transactions->getElement('exchange_rate')]));

		$transactions->addExpression('no')->set(function($m,$q){
			$related_no = $m->add('xepan\commerce\Model_QSP_Master')
									->addCondition('id',$m->getElement('related_id'));
			return $q->expr("[0]",[$related_no->fieldQuery('document_no')]);
		});

		$transactions->addExpression('monthyear')->set('DATE_FORMAT(created_at,"%M %Y")');
		
		$ledger_id_array = $ledger_m->getRows();
		$ids = [];	
		foreach ($ledger_id_array as $key => $value) {
			$ids [] = $value['id'];
		}
		
		$transactions->addCondition('ledger_id',$ids);
		
		if($monthyear)
			$transactions->addCondition('monthyear',$monthyear);

		if(!$crud->isEditing()){
			$grid = $crud->grid;
			$grid->addCurrentBalanceInEachRow();
			$grid->addSno();
			$grid->addHook('formatRow',function($g){
				if($g->model['no']){
					$g->current_row['sales_no'] = " :: " .$g->model['no'];
				}
			});

			$grid->js('click')->_selector('.do-view-attachment')->univ()
				->frameURL('Attachments',[$this->api->url
				('xepan_accounts_accounttransaction_attachment'),'account_transaction_id'=>$this->js()
				->_selectorThis()->closest('[data-id]')->data('id')]);
		}

		$transactions->addExpression('doc_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\base\Model_Document_Attachment')
								->addCondition('document_id',$m->getElement('related_id'));		
			return $doc_attachment_m->count();
		});

		$transactions->addExpression('trans_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\accounts\Model_Transaction_Attachment')
								->addCondition('account_transaction_id',$m->getElement('id'));		
			return $doc_attachment_m->count();
		});

		$transactions->getElement('attachments_count')->destroy();
		$transactions->addExpression('attachments_count')->set(function($m,$q){
			return $q->expr('([0]+[1])',[$m->getElement('doc_attachment_count'), $m->getElement('trans_attachment_count')]);
		});

		$crud->grid->addHook('formatRow',function($g){
			$g->current_row_html['created_at'] = date('F jS Y', strtotime($g->model['created_at']));
			if(!$g->model['transaction_template_id'] && !$this->app->auth->model->isSuperUser()){
				$g->current_row_html['edit'] = '<span class="fa-stack table-link"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>';				
				$g->current_row_html['delete'] = '<span class="table-link fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>';				
			}

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
		});	

		if($crud->isEditing()){
			$transactions->load($crud->id);
		}

		$crud->setModel($transactions);
		$crud->grid->addQuickSearch(['name','Narration','transaction_type','related_type']);
	}	
}