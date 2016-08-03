<?php

namespace xepan\accounts;

class page_accounttransactionlister extends \xepan\base\Page{
	public $title = "Transaction Lister";
	function init(){
		parent::init();

		$grid = $this->add('xepan\hr\Grid',null,null,['view/grid/account-transaction-lister-run']);
		$transaction_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$transaction_m->add('xepan\hr\Controller_ACL');
		
		$grid->setModel($transaction_m);
		$run_executer = $grid->addColumn('button','Run');

		if($_GET['Run']){
			$this->app->redirect($this->app->url('xepan_accounts_accounttransactionexecuter',['accounts_template_id'=>$_GET['Run']]));
		}
	}
}	