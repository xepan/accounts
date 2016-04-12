<?php

namespace xepan\accounts;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_accounts';

	function init(){
		parent::init();
		
		$this->routePages('xepan_accounts');
		$this->addLocation(array('template'=>'templates'));

		if($this->app->is_admin){
			$m = $this->app->top_menu->addMenu('Account');
			$m->addItem(['Create Account','icon'=>'fa fa-edit'],'xepan_accounts_accounts');
			$m->addItem(['Account Paid','icon'=>'fa fa-credit-card'],'xepan_accounts_amtpaid');
			$m->addItem(['Payment Received','icon'=>'fa fa-check'],'xepan_accounts_amtreceived');
			$m->addItem(['Cash <=> Bank','icon'=>'fa fa-money'],'xepan_accounts_contra');
			$m->addItem(['Account Statement','icon'=>'fa fa-qrcode'],'xepan_accounts_statement');
			$m->addItem(['Cash Book','icon'=>'fa fa-list'],'xepan_accounts_cashbook');
			$m->addItem(['Day Book','icon'=>'fa fa-th'],'xepan_accounts_daybook');
			$m->addItem(['Group','icon'=>'fa fa-list'],'xepan_accounts_group');
			$m->addItem(['Balance Sheet','icon'=>'fa fa-list-alt'],'xepan_accounts_balancesheet');
			$m->addItem(['Profit & Loss','icon'=>'fa fa-columns'],'xepan_accounts_pandl');
			$m->addItem(['Debit/Credit Note','icon'=>'fa fa-credit-card'],'xepan_accounts_debitcreditnote');
			$m->addItem(['Configuration','icon'=>'fa fa-anchor'],'xepan_accounts_config');
			$this->app->epan->default_currency = $this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$this->app->epan->config->getConfig('DEFAULT_CURRENCY_ID'));
			$this->addAccountTemplates();
		}
		$this->addAppDateFunctions();

		$ledger = $this->add('xepan\accounts\Model_Ledger');
		$this->app->addHook('customer_update',[$ledger,'createCustomerLedger']);
		$this->app->addHook('supplier_update',[$ledger,'createSupplierLedger']);
		$this->app->addHook('outsource_party_update',[$ledger,'createOutsourcePartyLedger']);
		$this->app->epan->default_currency = $this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$this->app->epan->config->getConfig('DEFAULT_CURRENCY_ID'));
		
	}

	function addAccountTemplates(){
		$data =[
					'customer'=>['name'=>'Customer', 'description'=>'Entries related to customer','group_id'=>37,'ledger_type'=>'SundryDebtor'],
					'supplier' => ['name'=>'Supplier', 'description'=>'Entries related to Supplier','group_id'=>38,'ledger_type'=>'SundryCreditor'],
					'outsourceparty' => ['name'=>'OutsourceParty', 'description'=>'Entries related to OutsourceParty','group_id'=>9,'ledger_type'=>'outsourceparty'],
					'dutiesandtaxes' => ['name'=>'DutiesAndTaxes', 'description'=>'Entries related to DutiesAndTaxes','group_id'=>0,'ledger_type'=>'dutiesandtaxes'],
					'directexpenses' => ['name'=>'DirectExpenses', 'description'=>'Entries related to Expenses','group_id'=>32,'ledger_type'=>'Direct Expenses'],
					'indirectincome' => ['name'=>'IndirectIncome', 'description'=>'Entries related to Income','group_id'=>33,'ledger_type'=>'Indirect Income']
					
		];
		$this->app->setConfig('account_template_data',$data);
	}

	function addAppdateFunctions(){
		$this->app->addMethod('nextDate',function($app,$date=null){
			
			if(!$date) $date = $this->api->today;
	        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
	        return $date;
		});

		$this->app->addMethod('setDate',function($app,$date){
	        $this->api->memorize('current_date',$date);
	        $this->now = date('Y-m-d H:i:s',strtotime($date));
	        $this->today = date('Y-m-d',strtotime($date));
    	
    	});

	}

}
