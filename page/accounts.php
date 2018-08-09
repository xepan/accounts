<?php

namespace xepan\accounts;

class page_accounts extends \xepan\base\Page
{
	public $title = "Accounts/Ledgers";
	
	function init()
	{
		parent::init();

		$accounts = $this->add('xepan\accounts\Model_Ledger');

		$crud = $this->add('xepan\hr\CRUD',
						['action_page'=>'xepan_accounts_accounttemplate',
						'edit_page'=>'xepan_accounts_accountdetail'],
						null,
						['view/accounts-grid']);
		$crud->setModel($accounts);

		$crud->grid->addPaginator(10);
		$frm=$crud->grid->addQuickSearch(['name']);
		$crud->add('xepan\base\Controller_Avatar');
		$crud->add('xepan\base\Controller_MultiDelete');

		if(!$crud->isEditing()){
			$crud->grid->js('click')->_selector('.do-view-leadger')->univ()->frameURL('Ledgers Details',[$this->api->url('xepan_accounts_statement'),'ledger_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		}

	}
}