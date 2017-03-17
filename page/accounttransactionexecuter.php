<?php

namespace xepan\accounts;

class page_accounttransactionexecuter extends \xepan\base\Page{
	public $title = "Transaction Executer";
	public $breadcrumb=['Home'=>'index','Transaction Lister'=>'xepan_accounts_accounttransactionlister','Executer'=>'#'];

	function init()
	{
		parent::init();
		$template_id = $this->app->stickyGET('accounts_template_id');

		$model = $this->add('xepan\accounts\Model_EntryTemplate')
			->load($template_id);
		
		$this->title = $model['name'];

		// $model->manageForm($this);
		
		// $model = $this->add('xepan\accounts\Model_Transaction')->load(813);
		$widget = $this->add('xepan\accounts\View_TransactionWidget');
		$widget->setModel($model);
	}
}	