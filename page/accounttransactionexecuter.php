<?php

namespace xepan\accounts;

class page_accounttransactionexecuter extends \xepan\base\Page{
	public $title = "Transaction Executer";
	function init()
	{
		parent::init();
		$template_id = $this->app->stickyGET('accounts_template_id');

		$this->add('xepan\accounts\Model_EntryTemplate')
			->load($template_id)
			->manageForm($this);

	}
}	