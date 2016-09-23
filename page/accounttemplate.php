<?php

namespace xepan\accounts;

class page_accounttemplate extends \Page
{
	public $title = "Account Type";
	
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
		$this->app->setConfig('account_template_data',$data);

		$completelister = $this->add('CompleteLister',null,null,['view\accounttemplate']);
		$completelister->setSource($this->app->getConfig('account_template_data'));
		
	}
}