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

		$grid = $this->add('xepan\accounts\Grid_AccountsBase',['no_records_message'=>'No day book statement found'],null,['view/daybookstatement-grid']);

		$transaction_row = $this->add('xepan\accounts\Model_TransactionRow');
			
		$transaction_row->addExpression('related_ledger_name')->set(function($m,$q){
			$model_ledger = $this->add('xepan\accounts\Model_Ledger');
			$model_ledger->addCondition('id',$m->getElement('ledger_id'));
			$model_ledger->setLimit(1);
			return $model_ledger->fieldQuery('name');
		});
		
		if($_GET['date_selected']){
			$transaction_row->addCondition('created_at','>=',$_GET['date_selected']);
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}else{			
			$transaction_row->addCondition('created_at','>=',$this->api->today);
			$transaction_row->addCondition('created_at','<',$this->app->nextDate($this->api->today));
		}
		
		$grid->setModel($transaction_row);
		$grid->addSno();
		$grid->removeColumn('account');

		$grid->addMethod('format_transaction_type',function($g,$f){
			if($g->model->transaction()->customer()){
				$g->current_row_html[$f] = $g->model['transaction_type']." :: ".$g->model->transaction()->customer()->get('organization');
			}else{
				$g->current_row_html[$f] = $g->model['transaction_type'];
			}
		});
		$grid->addFormatter('transaction_type','transaction_type');

		$print_btn = $grid->addButton('Print')->addClass('btn btn-primary');
		if($print_btn->isClicked()){
		}

		if($form->isSubmitted()){
			$grid->js()->reload(['date_selected'=>$form['date']?:0])->execute();
		}
	}
}