<?php
namespace xepan\accounts;
class page_amtpaid extends \xepan\base\Page {
	public $title="Account Payment";
	
	function init(){
		parent::init();

		if(!$this->add('xepan\accounts\Model_Transaction')->add('xepan\hr\Controller_Acl')->canAdd()){
			$this->add('View_Error')->set('You do not have permission to add/execute transaction, please give add transaction permission from Day/Cash book ACL');
			return;
		}

		$tabs = $this->add('Tabs');

		$cash_tab = $tabs->addTab('Cash','cash');
		// ==== CASH PAYMENT ===========
		$cash_payment = $cash_tab->add('xepan\accounts\Model_EntryTemplate');
		$cash_payment->loadBy('unique_trnasaction_template_code','PARTYCASHPAYMENT');
		
		// $cash_payment->addHook('afterExecute',function($cash_payment,$transaction,$total_amount){
		// 	$cash_payment->form->js()->univ()->reload()->successMessage('Done')->execute();
		// });

		// $view_cash = $this->add('View',null,'cash_view');
		// $cash_payment->manageForm($view_cash,null,null,null);
		// $model->manageForm($this);
		
		$widget = $cash_tab->add('xepan\accounts\View_TransactionWidget');
		$widget->setModel($cash_payment);

		// ==== BANK PAYMENT ===========
		$bank_tab = $tabs->addTab('Bank','bank');
		$bank_payment = $bank_tab->add('xepan\accounts\Model_EntryTemplate');
		$bank_payment->loadBy('unique_trnasaction_template_code','PARTYBANKPAYMENT');
		
		// $bank_payment->addHook('afterExecute',function($bank_payment,$transaction,$total_amount){
		// 	$bank_payment->form->js()->univ()->reload()->successMessage('Done')->execute();
		// });
		// $view_bank = $this->add('View',null,'bank_view');
		// $bank_payment->manageForm($view_bank,null,null,null);

		$widget = $bank_tab->add('xepan\accounts\View_TransactionWidget');
		$widget->setModel($bank_payment);

	}
	// function defaultTemplate(){
	// 	return ['page/amtpaid'];
	// }
}