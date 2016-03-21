<?php
namespace xepan\accounts;
class page_daybook extends \Page{
	public $title="Account DayBook";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','date')->validateNotNull();
		$form->addSubmit('Open Day Book');

		$day_transaction_model = $this->add('xepan\accounts\Model_Transaction');
		$transaction_row=$day_transaction_model->leftjoin('account_transaction_row.transaction_id');
		$transaction_row->hasOne('xepan\accounts\Account','account_id');
		$transaction_row->addField('amountDr');
		$transaction_row->addField('amountCr');
		
		
		$daybook_lister_crud = $this->add('xepan\hr\CRUD',['grid_class'=>'xepan\accounts\Grid_DayBook']);

		if($this->api->stickyGET('date_selected')){
			$day_transaction_model->addCondition('created_at','>=',$_GET['date_selected']);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($_GET['date_selected']));
		}else{
			$day_transaction_model->addCondition('created_at','>=',$this->api->today);
			$day_transaction_model->addCondition('created_at','<',$this->api->nextDate($this->api->today));

		}
 
		$daybook_lister_crud->setModel($day_transaction_model,['voucher_no','transaction_type','Narration','account','amountDr','amountCr']);
		$daybook_lister_crud->grid->removeColumn('Narration');
		$daybook_lister_crud->grid->removeColumn('transaction_type');

		if($form->isSubmitted()){
			$daybook_lister_crud->js()->reload(['date_selected'=>$form['date']?:0])->execute();
		}

	}
}