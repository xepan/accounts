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
			$m->addItem('Create Account','xepan_accounts_accounts');
			$m->addItem('Account Paid','xepan_accounts_amtpaid');
			$m->addItem('Payment Received','xepan_accounts_amtreceived');
			$m->addItem('Cash & Bank','xepan_accounts_contra');
			$m->addItem('Account Statement','xepan_accounts_statement');
			$m->addItem('Cash Book','xepan_accounts_cashbook');
			$m->addItem('Day Book','xepan_accounts_daybook');
			$m->addItem('Ledgers','xepan_accounts_ledgers');
			$m->addItem('Debit/Credit Note','xepan_accounts_debitcreditnote');
			$m->addItem('Configuration','xepan_accounts_config');

			$this->app->epan->default_currency = $this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$this->app->epan->config->getConfig('DEFAULT_CURRENCY_ID'));

		}

		$this->app->addHook('tax_update',['xepan\accounts\Model_Ledger','createTaxLedger']);
		
		$this->addAppDateFunctions();

		$this->app->addHook('customer_update',['xepan\accounts\Model_Ledger','createCustomerLedger']);
		$this->app->addHook('supplier_update',['xepan\accounts\Model_Ledger','createSupplierLedger']);
		
	}

	function addAppdateFunctions(){
		$this->app->addMethod('nextDate',function($date=null){
			if(!$date) $date = $this->api->today;
	        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 DAY"));    
	        return $date;
		});

		$this->app->addMethod('setDate',function($date){
	        $this->api->memorize('current_date',$date);
	        $this->now = date('Y-m-d H:i:s',strtotime($date));
	        $this->today = date('Y-m-d',strtotime($date));
    	
    	});

	}

}
