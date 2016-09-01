<?php
namespace xepan\accounts;
class page_amtpaid extends \xepan\base\Page {
	public $title="Account Payment";
	function init(){
		parent::init();

		// ==== CASH PAYMENT ===========
		$cash_payment = $this->add('xepan\accounts\Model_EntryTemplate');
		$cash_payment->loadBy('unique_trnasaction_template_code','PARTYCASHPAYMENT');
		
		$cash_payment->addHook('afterExecute',function($cash_payment,$transaction,$total_amount){
			$this->app->page_action_result = $cash_payment->form->js(true)->univ()->reload()->successMessage('Done');
		});

		$view_cash = $this->add('View',null,'cash_view');
		$cash_payment->manageForm($view_cash,null,null,null);

		// ==== BANK PAYMENT ===========
		$bank_payment = $this->add('xepan\accounts\Model_EntryTemplate');
		$bank_payment->loadBy('unique_trnasaction_template_code','PARTYBANKPAYMENT');
		
		$bank_payment->addHook('afterExecute',function($bank_payment,$transaction,$total_amount){
			$this->app->page_action_result = $bank_payment->form->js(true)->univ()->reload()->successMessage('Done');
		});
		$view_bank = $this->add('View',null,'bank_view');
		$bank_payment->manageForm($view_bank,null,null,null);

	}
	function defaultTemplate(){
		return ['page/amtpaid'];
	}
}