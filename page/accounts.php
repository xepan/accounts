<?php

namespace xepan\accounts;

class page_accounts extends \Page
{
	public $title = "Accounts/Ledgers";
	
	function init()
	{
		parent::init();

		$accounts = $this->add('xepan\accounts\Model_Ledger');

		$crud = $this->add('xepan\hr\CRUD',['action_page'=>'xepan_accounts_accounttemplate', 'edit_page'=>'xepan_accounts_accountdetail'],null,['view/accounts-grid']);
		$crud->setModel($accounts);
		$crud->grid->addPaginator(25);

		$crud->grid->addQuickSearch(['account']);

	}
}