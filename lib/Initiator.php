<?php

namespace xepan\accounts;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_accounts';

	function setup_admin(){
		
		$this->routePages('xepan_accounts');
		$this->addLocation(array('template'=>'templates'));

		$this->app->epan->default_currency = $this->recall(
										$this->app->epan->id.'_defaultCurrency',
										$this->memorize(
											$this->app->epan->id.'_defaultCurrency',
											$this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$this->app->epan->config->getConfig('DEFAULT_CURRENCY_ID'))
											)
										);
		if(!$this->app->isAjaxOutput()){
			$m = $this->app->top_menu->addMenu('Account');

			$m->addItem(['Account','icon'=>'fa fa-briefcase'],'xepan_accounts_accounts');
			$m->addItem(['Account Paid','icon'=>'fa fa-cc-visa'],'xepan_accounts_amtpaid');
			$m->addItem(['Payment Received','icon'=>'fa fa-cc-paypal'],'xepan_accounts_amtreceived');
			$m->addItem(['Cash <=> Bank','icon'=>'fa fa-exchange'],'xepan_accounts_contra');
			$m->addItem(['Account Statement','icon'=>'fa fa-file-excel-o'],'xepan_accounts_statement');
			$m->addItem(['Cash Book','icon'=>'fa fa-book'],'xepan_accounts_cashbook');
			$m->addItem(['Day Book','icon'=>'fa fa-bookmark'],'xepan_accounts_daybook');
			$m->addItem(['Group','icon'=>'fa fa-group'],'xepan_accounts_group');
			$m->addItem(['Balance Sheet','icon'=>'fa fa-balance-scale'],'xepan_accounts_balancesheet');
			$m->addItem(['Profit & Loss','icon'=>'fa  fa-database'],'xepan_accounts_pandl');
			$m->addItem(['Debit/Credit Note','icon'=>'fa fa-sticky-note-o'],'xepan_accounts_debitcreditnote');
			$m->addItem(['Currency Management','icon'=>'fa fa-money'],'xepan_accounts_currency');
			$m->addItem(['Configuration','icon'=>'fa fa-cog fa-spin'],'xepan_accounts_config');
			
		}

		$this->addAppDateFunctions();

		$ledger = $this->add('xepan\accounts\Model_Ledger');
		$this->app->addHook('employee_update',[$ledger,'createEmployeeLedger']);
		$this->app->addHook('customer_update',[$ledger,'createCustomerLedger']);
		$this->app->addHook('supplier_update',[$ledger,'createSupplierLedger']);
		$this->app->addHook('outsource_party_update',[$ledger,'createOutsourcePartyLedger']);
		
		return $this;

	}

	function setup_frontend(){
		return $this;
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

	function resetDB(){
		// Clear DB
		if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
        if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;
        
		$this->app->epan=$this->app->old_epan;
        $truncate_models = ['TransactionRow','Transaction','Ledger','Group','BalanceSheet','Currency'];
        foreach ($truncate_models as $t) {
            $m=$this->add('xepan\accounts\Model_'.$t);
            foreach ($m as $mt) {
                $mt->delete();
            }
        }
		$this->app->epan=$this->app->new_epan;

		// Orphan currencies
		$d = $this->app->db->dsql();
        $d->sql_templates['delete'] = "delete [table] from  [table] [join] [where]";
        $d->table('currency')->where('document.id is null')->join('document',null,'left')->delete();

       	$default_currency = $this->add('xepan\accounts\Model_Currency')
       			->set('name','Default Currency')
       			->set('value',1)
       			->save();

       	$config = $this->app->epan->ref('Configurations')->tryLoadAny();
       	$config->setConfig('DEFAULT_CURRENCY_ID',$default_currency->id,'accounts');
       	$this->app->epan->default_currency = $this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$config->getConfig('DEFAULT_CURRENCY_ID'));
	}

}
