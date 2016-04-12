<?php

namespace xepan\accounts;

class page_accountdetail extends \xepan\base\Page{
	public $title = "Account Detail";
	public $breadcrumb=['Home'=>'index','ACCOUNT'=>'xepan_accounts_account','Detail'=>'#'];
	function init()
	{
		parent::init();
		
		$acctypegroup = $this->app->getConfig('account_template_data');

		$acc_type=$this->app->stickyGET('acc_type');
		$action = $this->api->stickyGET('action')?:'view';
		$account = $this->add('xepan\accounts\Model_Ledger');
		// throw new \Exception($this->acctypegroup[$acc_type], 1);
		
		$account_detail = $this->add('xepan\hr\View_Document',['action'=> $action,'id_field_on_reload'=>'account_id'],null,['view\accountdetail']);
		$account_detail->setModel($account,['name','AccountDisplayName','group','OpeningBalanceDr','OpeningBalanceCr'],['name','AccountDisplayName','group_id','OpeningBalanceDr','OpeningBalanceCr']);
		
		if($action=='add'){
			$account_detail->form->getElement('group_id')->set($this->acctypegroup[$acc_type]['group_id']);
		}
	}
}	