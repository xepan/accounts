<?php
namespace xepan\accounts;
class page_amtreceived extends \xepan\base\Page {
	public $title="Account Receipt";
	function init(){
		parent::init();

		if(!$this->add('xepan\accounts\Model_Transaction')->add('xepan\hr\Controller_Acl')->canAdd()){
			$this->add('View_Error')->set('You do not have permission to add/execute transaction, please give add transaction permission from Day/Cash book ACL');
			return;
		}

		$tabs = $this->add('Tabs');

		$cash_tab = $tabs->addTab('Cash','cash');
		// ==== CASH RECEIVED ===========
		$cash_received = $cash_tab->add('xepan\accounts\Model_EntryTemplate');
		$cash_received->loadBy('unique_trnasaction_template_code','PARTYCASHRECEIVED');
		
		// $cash_received->addHook('afterExecute',function($cash_received,$transaction,$total_amount){
		// 	$cash_received->form->js()->univ()->reload()->successMessage('Done')->execute();
		// });

		// $view_cash = $this->add('View',null,'cash_view');
		// $cash_received->manageForm($view_cash,null,null,null);

		$widget = $cash_tab->add('xepan\accounts\View_TransactionWidget');
		$widget->setModel($cash_received);

		// ==== BANK RECEIVED ===========
		$bank_tab = $tabs->addTab('Bank','bank');
		$bank_received = $bank_tab->add('xepan\accounts\Model_EntryTemplate');
		$bank_received->loadBy('unique_trnasaction_template_code','PARTYBANKRECEIVED');
		
		// $bank_received->addHook('afterExecute',function($bank_received,$transaction,$total_amount){
		// 	$bank_received->form->js()->univ()->reload()->successMessage('Done')->execute();
		// });
		// $view_bank = $this->add('View',null,'bank_view');
		// $bank_received->manageForm($view_bank,null,null,null);
		$widget = $bank_tab->add('xepan\accounts\View_TransactionWidget');
		$widget->setModel($bank_received);
	}
	// function defaultTemplate(){
	// 	return ['page/amtrecevied'];
	// }
}