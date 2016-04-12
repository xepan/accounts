<?php

namespace xepan\accounts;

class page_accountdetail extends \xepan\base\Page{
	public $title = "Account Detail";
	public $breadcrumb=['Home'=>'index','ACCOUNT'=>'xepan_accounts_account','Detail'=>'#'];
	function init()
	{
		parent::init();
		
		$acctypegroup = $this->app->getConfig('account_template_data');

		$ledger_type = $this->app->stickyGET('ledger_type');
		$action = $this->api->stickyGET('action')?:'view';
		$account = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('id',$this->api->stickyGET('ledger_id'));

		$account_detail = $this->add('xepan\base\View_Document',['action'=> $action],'account_info',['view/accountdetail','account_info']);
		$account_detail->setIdField('ledger_id');
		$account_detail->setModel($account,['name','LedgerDisplayName','group_id','OpeningBalanceDr','OpeningBalanceCr'],
											['name','LedgerDisplayName','group_id','OpeningBalanceDr','OpeningBalanceCr']);
		
		if($action=='add'){
			$account_detail->form->getElement('group_id')->set($acctypegroup[$ledger_type]['group_id']);
		}
	}

	function defaultTemplate(){
		return ['view/accountdetail'];
	}
}	