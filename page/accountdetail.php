<?php

namespace xepan\accounts;

class page_accountdetail extends \xepan\base\Page{
	public $title = "Account Detail";
	public $breadcrumb=['Home'=>'index','ACCOUNTS'=>'xepan_accounts_accounts','Detail'=>'#'];
	function init()
	{
		parent::init();

	// $data =[
	// 		'Customer'=>['name'=>'Customer', 'description'=>'Entries related to customer','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Debtor")->get('id'),'ledger_type'=>'Customer'],
	// 		'Supplier' => ['name'=>'Supplier', 'description'=>'Entries related to Supplier','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Creditor")->get('id'),'ledger_type'=>'Supplier'],
	// 		'Outsourceparty' => ['name'=>'Outsource Party', 'description'=>'Entries related to OutsourceParty','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Sundry Creditor")->get('id'),'ledger_type'=>'OutsourceParty'],
	// 		'BankCharges' => ['name'=>'Bank Charges', 'description'=>'Any sort of bank charges','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Bank Charges Expenses")->get('id'),'ledger_type'=>'Bank Charges'],
	// 		'FixedAssets' => ['name'=>'Furniture', 'description'=>'Entries related to Assets','group_id'=>$this->add('xepan\accounts\Model_Group')->load("Furniture & Fixtures")->get('id'),'ledger_type'=>'FixedAssets']
	// 	];
		// $acctypegroup = $this->app->getConfig('account_template_data',$data);
		

		$ledger_type = $this->app->stickyGET('ledger_type');
		$group_id = $this->app->stickyGET('group_id');

		$action = $this->api->stickyGET('action')?:'view';
		$account = $this->add('xepan\accounts\Model_Ledger');
		$m = $account->getElement('contact_id')->getModel();
		$m->title_field = "unique_name";
		$account->tryLoadBy('id',$this->api->stickyGET('ledger_id'));

		$account_detail = $this->add('xepan\hr\View_Document',['action'=> $action,'page_reload'=>true],'account_info',['view/accountdetail','account_info']);
		$account_detail->setIdField('ledger_id');
		$account_detail->setModel($account,
					[
					'name','LedgerDisplayName',
					'group_id','OpeningBalanceDr',
					'OpeningBalanceCr','ledger_type','contact'		
					],
					[
					'name','LedgerDisplayName',
					'group_id','OpeningBalanceDr','OpeningBalanceCr',
					'ledger_type','contact_id'
					]
					);
		
		if($action=='add'){
			if($group_id){
				$account_detail->form->getElement('group_id')->set($group_id);
			}	
			if($ledger_type){
				$this->title = "Add Account/Ledger for ".$ledger_type;
				$account_detail->form->getElement('ledger_type')->set($ledger_type);
			}

			if($account_detail->form->isSubmitted()){
				$account_detail->form->save();
				$js = $this->js()->univ()->notify('Saved','Account/Ledger Saved','success',false);
				$this->js(null,$js)->univ()->location($this->app->url())->execute();
			}
		}
	}

	function defaultTemplate(){
		return ['view/accountdetail'];
	}
}	