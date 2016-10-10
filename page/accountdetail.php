<?php

namespace xepan\accounts;

class page_accountdetail extends \xepan\base\Page{
	public $title = "Account Detail";
	public $breadcrumb=['Home'=>'index','ACCOUNTS'=>'xepan_accounts_accounts','Detail'=>'#'];
	function init()
	{
		parent::init();

	$data =[
			'Customer'=>['name'=>'Customer', 'description'=>'Entries related to customer','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Debtor")->get('id'),'ledger_type'=>'Customer'],
			'Supplier' => ['name'=>'Supplier', 'description'=>'Entries related to Supplier','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Creditor")->get('id'),'ledger_type'=>'Supplier'],
			'Outsourceparty' => ['name'=>'Outsource Party', 'description'=>'Entries related to OutsourceParty','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Creditor")->get('id'),'ledger_type'=>'OutsourceParty'],
			'BankCharges' => ['name'=>'Bank Charges', 'description'=>'Any sort of bank charges','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Bank Charges Expenses")->get('id'),'ledger_type'=>'Bank Charges'],
			'FixedAssets' => ['name'=>'Furniture', 'description'=>'Entries related to Assets','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Furniture & Fixtures")->get('id'),'ledger_type'=>'FixedAssets']
		];
		
	$acctypegroup = $this->app->getConfig('account_template_data',$data);

		$ledger_type = $this->app->stickyGET('ledger_type');
		$action = $this->api->stickyGET('action')?:'view';
		$account = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('id',$this->api->stickyGET('ledger_id'));

		$account_detail = $this->add('xepan\base\View_Document',['action'=> $action],'account_info',['view/accountdetail','account_info']);
		$account_detail->setIdField('ledger_id');
		$account_detail->setModel($account,['name','LedgerDisplayName','group_id','OpeningBalanceDr','OpeningBalanceCr','ledger_type'],
											['name','LedgerDisplayName','group_id','OpeningBalanceDr','OpeningBalanceCr','ledger_type']);
		
		if($action=='add'){
			$account_detail->form->getElement('group_id')->set($acctypegroup[$ledger_type]['group_id']);
			$account_detail->form->getElement('ledger_type')->set($acctypegroup[$ledger_type]['ledger_type']);

		}
	}

	function defaultTemplate(){
		return ['view/accountdetail'];
	}
}	