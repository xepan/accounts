<?php
namespace xepan\accounts;
class page_balancesheet extends \Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();
		$balance_sheet = $this->add('xepan\accounts\Model_BalanceSheet');
		$crud = $this->add('xepan\hr\CRUD');
		$crud->setModel($balance_sheet);
		
	}
}