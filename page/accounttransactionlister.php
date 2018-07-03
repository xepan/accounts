<?php

namespace xepan\accounts;

class page_accounttransactionlister extends \xepan\base\Page{
	public $title = "Transaction Lister";
	function init(){
		parent::init();

		$this->app->stickyGET('cut_page');

		$v = $this->add('View');
		$crud = $this->add('xepan\hr\CRUD',['allow_add'=>false],null,['view/grid/account-transaction-lister-run']);
		$transaction_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$transaction_m->add('xepan\hr\Controller_ACL');
		
		$crud->setModel($transaction_m);

		$crud->grid->setFormatter('detail','text');
		//$crud->grid->addQuickSearch(['name','unique_trnasaction_template_code']);
		if($this->add('xepan\accounts\Model_Transaction')->add('xepan\hr\Controller_Acl')->canAdd())
			$run_executer = $crud->grid->addColumn('button','Run');
		else{
			$v->add('View_Error')->set('You do not have permission to add/execute transaction, please give add transaction permission from Day/Cash book ACL');
		}

		if($_GET['Run'] && !$_GET['cut_page']){
			$this->app->redirect($this->app->url('xepan_accounts_accounttransactionexecuter',['accounts_template_id'=>$_GET['Run']]));
		}

		if($_GET['Run'] && $_GET['cut_page']){
			$transaction_m->load($_GET['Run']);
			$this->js()->univ()->frameURL($transaction_m['name'],$this->app->url('xepan_accounts_accounttransactionexecuter',['accounts_template_id'=>$_GET['Run']]))->execute();
		}

		$this->js(true)
             ->_load('searchInput');

	}
}	