<?php

namespace xepan\accounts;

class page_accounttemplate extends \Page
{
	public $title = "Account Type";
	
	function init()
	{
		parent::init();

		$data =[
					'Customer'=>['name'=>'Customer', 'description'=>'Entries related to customer','group_id'=>$this->add('xepan\accounts\Model_Group')->loadSundryDebtor()->get('id'),'ledger_type'=>'Customer'],
					'Supplier' => ['name'=>'Supplier', 'description'=>'Entries related to Supplier','group_id'=>$this->add('xepan\accounts\Model_Group')->loadSundryCreditor()->get('id'),'ledger_type'=>'Supplier'],
					'outsourceparty' => ['name'=>'Outsource Party', 'description'=>'Entries related to OutsourceParty','group_id'=>$this->add('xepan\accounts\Model_Group')->loadSundryCreditor()->get('id'),'ledger_type'=>'OutsourceParty'],
					'BankCharges' => ['name'=>'Bank Charges', 'description'=>'Any sort of bank chanrges','group_id'=>$this->add('xepan\accounts\Model_Group')->loadIndirectExpenses()->get('id'),'ledger_type'=>'BankCharges'],
					'DutiesAndTaxes' => ['name'=>'Duties And Taxes', 'description'=>'Entries related to DutiesAndTaxes','group_id'=>$this->add('xepan\accounts\Model_Group')->loadDutiesAndTaxes()->get('id'),'ledger_type'=>'DutiesAndTaxes'],
					// 'DirectExpenses' => ['name'=>'Direct Expenses', 'description'=>'Entries related to Expenses','group_id'=>$this->add('xepan\accounts\Model_Group')->loadDirectExpenses->get('id'),'ledger_type'=>'DirectExpenses'],
					'IndirectIncome' => ['name'=>'Indirect Income', 'description'=>'Entries related to Income','group_id'=>$this->add('xepan\accounts\Model_Group')->loadIndirectIncome()->get('id'),'ledger_type'=>'IndirectIncome'],
					'SuspenseLedger' => ['name'=>'Suspense', 'description'=>'Entries related to Income','group_id'=>$this->add('xepan\accounts\Model_Group')->loadSuspenseLedger()->get('id'),'ledger_type'=>'Suspense'],
					'SecuredLoan' => ['name'=>'Loan', 'description'=>'Entries related to Income','group_id'=>$this->add('xepan\accounts\Model_Group')->loadSecuredLoan()->get('id'),'ledger_type'=>'Loan'],
					'FixedAssets' => ['name'=>'Furniture', 'description'=>'Entries related to Income','group_id'=>$this->add('xepan\accounts\Model_Group')->loadFixedAssets()->get('id'),'ledger_type'=>'FixedAssets']
					
		];
		$this->app->setConfig('account_template_data',$data);

		$completelister = $this->add('CompleteLister',null,null,['view\accounttemplate']);
		$completelister->setSource($this->app->getConfig('account_template_data'));
		
	}
}