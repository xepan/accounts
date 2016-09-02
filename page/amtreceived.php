<?php
namespace xepan\accounts;
class page_amtreceived extends \xepan\base\Page {
	public $title="Account Receipt";
	function init(){
		parent::init();

		// ==== CASH RECEIVED ===========
		$cash_received = $this->add('xepan\accounts\Model_EntryTemplate');
		$cash_received->loadBy('unique_trnasaction_template_code','PARTYCASHRECEIVED');
		
		$cash_received->addHook('afterExecute',function($cash_received,$transaction,$total_amount){
			$cash_received->form->js()->univ()->reload()->successMessage('Done')->execute();
		});

		$view_cash = $this->add('View',null,'cash_view');
		$cash_received->manageForm($view_cash,null,null,null);

		// ==== BANK RECEIVED ===========
		$bank_received = $this->add('xepan\accounts\Model_EntryTemplate');
		$bank_received->loadBy('unique_trnasaction_template_code','PARTYBANKRECEIVED');
		
		$bank_received->addHook('afterExecute',function($bank_received,$transaction,$total_amount){
			$bank_received->form->js()->univ()->reload()->successMessage('Done')->execute();
		});
		$view_bank = $this->add('View',null,'bank_view');
		$bank_received->manageForm($view_bank,null,null,null);
	}
	function defaultTemplate(){
		return ['page/amtrecevied'];
	}
}