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

			$grid->addHook('formatRow',function($g){
				$g->current_row_html['created_at'] = date('F jS Y', strtotime($g->model['created_at']));				
				
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
			});
		}

		if($crud->isEditing()){
			$transaction->load($crud->id);
		}	

		$crud->setModel($transaction);

		if($form->isSubmitted()){
			$crud->js()->reload(['date_selected'=>$form['date']?:0])->execute();
		}
	}
}