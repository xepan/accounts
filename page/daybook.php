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

		// $daybook_crud = $this->add('xepan\hr\CRUD',['grid_class'=>'xepan\accounts\Grid_DayBook']);
		$grid = $this->add('xepan\accounts\Grid_AccountsBase',['no_records_message'=>'No day book statement found'],null,['view/daybookstatement-grid']);

		$transaction_row = $this->add('xepan\accounts\Model_TransactionRow');
			
		// Remove Cash entries from day book
		// $group=$this->add('xepan\accounts\Model_Group')->loadRootCashGroup();
		// $transaction_row->addCondition('root_group_id','<>',$group['id']);
		
		if($_GET['date_selected']){
			$transaction_row->addCondition('created_at','>=',$_GET['date_selected']);
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}else{			
			$transaction_row->addCondition('created_at','>=',$this->api->today);
			$transaction_row->addCondition('created_at','<',$this->app->nextDate($this->api->today));
		}
		// $grid=$daybook_crud->grid;
		
		$grid->setModel($transaction_row);
		// ,['voucher_no','transaction_type','created_at','Narration','account','amountDr','amountCr','root_group_name']);
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

		// $grid->addTotals(array('amountCr','amountDr'));
		if($form->isSubmitted()){
			$grid->js()->reload(['date_selected'=>$form['date']?:0])->execute();
		}


	}
}