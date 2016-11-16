<?php

namespace xepan\accounts;

class page_accounttransactionlister extends \xepan\base\Page{
	public $title = "Transaction Lister";
	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD',['allow_add'=>false],null,['view/grid/account-transaction-lister-run']);
		$transaction_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$transaction_m->add('xepan\hr\Controller_ACL');
		
		$crud->setModel($transaction_m);
		$crud->grid->addQuickSearch(['name']);
		$run_executer = $crud->grid->addColumn('button','Run');

		if($_GET['Run']){
			$this->app->redirect($this->app->url('xepan_accounts_accounttransactionexecuter',['accounts_template_id'=>$_GET['Run']]));
		}
	}
}	