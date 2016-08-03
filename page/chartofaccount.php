<?php

namespace xepan\accounts;

class page_chartofaccount extends \xepan\base\Page{
	public $title = "Chart of Accounts";
	function init()
	{
		parent::init();

		$balance_model = $this->add('xepan\accounts\Model_BalanceSheet')->tryLoadAny();
		$view = $this->add('View',null,null,['page/account-chart-view']);
		$view->setModel($balance_model);
	}

}	