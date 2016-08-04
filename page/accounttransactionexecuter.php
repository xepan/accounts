<?php

namespace xepan\accounts;

class page_accounttransactionexecuter extends \xepan\base\Page{
	public $title = "Transaction Executer";
	function init()
	{
		parent::init();
		$template_id = $this->app->stickyGET('accounts_template_id');

		$model = $this->add('xepan\accounts\Model_EntryTemplate')
			->load($template_id);
		
		$this->title = $model['name'];

		$model->manageForm($this);

	}
}	