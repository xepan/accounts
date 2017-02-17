<?php

namespace xepan\accounts;

class Initiator extends \Controller_Addon {
	
	public $addon_name = 'xepan_accounts';

	function setup_admin(){
		
		$this->routePages('xepan_accounts');
		$this->addLocation(array('template'=>'templates','js'=>'templates/js','css'=>'templates/css'))
			->setBaseURL('./vendor/xepan/accounts/')
			;

		if($this->app->auth->isLoggedIn()){
		
			$default_currency_id = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'currency_id'=>'DropDown'
							],
					'config_key'=>'FIRM_DEFAULT_CURRENCY_ID',
					'application'=>'accounts'
			]);
			$default_currency_id->tryLoadAny();
			$this->app->epan->default_currency = $this->recall(
											$this->app->epan->id.'_defaultCurrency',
											$this->memorize(
												$this->app->epan->id.'_defaultCurrency',
												$this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$default_currency_id['currency_id'])
												)
											);
			if(!$this->app->isAjaxOutput()){
				$m = $this->app->top_menu->addMenu('Account');

				$m->addItem(['Accounts Chart','icon'=>'fa fa-bar-chart-o'],'xepan_accounts_chartofaccount');
				$m->addItem(['Accounts/Ledgers','icon'=>'fa fa-briefcase'],'xepan_accounts_accounts');
				$m->addItem(['Account Paid','icon'=>'fa fa-cc-visa'],'xepan_accounts_amtpaid');
				$m->addItem(['Payment Received','icon'=>'fa fa-cc-paypal'],'xepan_accounts_amtreceived');
				$m->addItem(['Cash <=> Bank','icon'=>'fa fa-exchange'],'xepan_accounts_contra');
				$m->addItem(['Transaction Lister','icon'=>'fa fa-list'],'xepan_accounts_accounttransactionlister');
				$m->addItem(['Account Statement','icon'=>'fa fa-file-excel-o'],'xepan_accounts_statement');
				$m->addItem(['Account Audit','icon'=>'fa fa-file-excel-o'],'xepan_accounts_audit');
				$m->addItem(['Cash Book','icon'=>'fa fa-book'],'xepan_accounts_cashbook');
				$m->addItem(['Day Book','icon'=>'fa fa-bookmark'],'xepan_accounts_daybook');
				$m->addItem(['Group','icon'=>'fa fa-group'],'xepan_accounts_group');
				$m->addItem(['Balance Sheet','icon'=>'fa fa-balance-scale'],'xepan_accounts_balancesheet');
				$m->addItem(['Profit & Loss','icon'=>'fa  fa-database'],'xepan_accounts_pandl');
				$m->addItem(['Trading','icon'=>'fa fa-exchange'],'xepan_accounts_trading');
				$m->addItem(['Debit/Credit Note','icon'=>'fa fa-sticky-note-o'],'xepan_accounts_debitcreditnote');
				$m->addItem(['Currency Management','icon'=>'fa fa-money'],$this->app->url('xepan_accounts_currency',['status'=>'Active']));
				$m->addItem(['Configuration','icon'=>'fa fa-cog fa-spin'],'xepan_accounts_config');
				$m->addItem(['Reports','icon'=>'fa fa-book'],'xepan_accounts_report');
				
			}

			// $this->addAppDateFunctions();

			$ledger = $this->add('xepan\accounts\Model_Ledger');
			$this->app->addHook('employee_update',[$ledger,'createEmployeeLedger']);
			$this->app->addHook('customer_update',[$ledger,'createCustomerLedger']);
			$this->app->addHook('supplier_update',[$ledger,'createSupplierLedger']);
			$this->app->addHook('outsource_party_update',[$ledger,'createOutsourcePartyLedger']);
			
			$transacton = $this->add('xepan\accounts\Model_Transaction');	
			$this->app->addHook('salary_sheet_approved',[$transacton,'updateSalaryTransaction']);
			$this->app->addHook('salary_sheet_canceled',[$transacton,'deleteSalaryTransaction']);
		}

		$search_ledger = $this->add('xepan\accounts\Model_Ledger');
        $this->app->addHook('quick_searched',[$search_ledger,'quickSearch']);
		
		$this->app->addHook('widget_collection',[$this,'exportWidgets']);
		$this->app->addHook('entity_collection',[$this,'exportEntities']);
		
		return $this;

	}

	function setup_pre_frontend(){
		// $this->addAppDateFunctions();
	}

	function setup_frontend(){
		$default_currency_id = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'currency_id'=>'DropDown'
							],
					'config_key'=>'FIRM_DEFAULT_CURRENCY_ID',
					'application'=>'accounts'
			]);
			$default_currency_id->tryLoadAny();

		$this->app->epan->default_currency = $this->recall(
											$this->app->epan->id.'_defaultCurrency',
											$this->memorize(
												$this->app->epan->id.'_defaultCurrency',
												$this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$default_currency_id['currency_id'])
												)
											);
		return $this;
	}


	function exportWidgets($app,&$array){
        // $array[] = ['xepan\accounts\Widget_DebtorAnlaysis','level'=>'Global','title'=>'Sundry Debtor Account Report'];
        // $array[] = ['xepan\accounts\Widget_CreditorAnlaysis','level'=>'Global','title'=>'Sundry Creditor Account Report'];
        // $array[] = ['xepan\accounts\Widget_PandlAnlaysis','level'=>'Global','title'=>'Pandl Report'];
        $array[] = ['xepan\accounts\Widget_MonthlyTaxes','level'=>'Global','title'=>'Monthly Taxes Information'];
    }

    function exportEntities($app,&$array){
        $array['ledger'] = ['caption'=>'Ledger', 'type'=>'xepan\base\Basic','model'=>'xepan\accounts\Model_Ledger'];
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

		$this->app->addMethod('previousDate',function($app,$date=null){
	        if(!$date) $date = $this->api->today;
	        $date = date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 DAY"));    
	        return $date;
	    });

		$this->app->addMethod('monthFirstDate',function($app,$date=null){
	        if(!$date) $date = $this->api->now;
	        return date('Y-m-01',strtotime($date));
	    });

		$this->app->addMethod('monthLastDate',function($app,$date=null){
	        if(!$date) $date = $this->api->now;
	        return date('Y-m-t',strtotime($date));
	    });

		$this->app->addMethod('isMonthLastDate',function($app,$date=null){
	        if(!$date) $date = $this->api->now;
	        $date = date('Y-m-d',strtotime($date));
	        return strtotime($date) == strtotime($this->monthLastDate());

	    });

		$this->app->addMethod('nextMonth',function($app,$date=null){
	        if(!$date) $date=$this->api->today;
	        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 MONTH"));
	    });

		$this->app->addMethod('previousMonth',function($app,$date=null){
	        if(!$date) $date=$this->api->today;
	        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 MONTH"));
	    });

		$this->app->addMethod('nextYear',function($app,$date=null){
	        if(!$date) $date=$this->api->today;
	        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " +1 YEAR"));
	    });

		$this->app->addMethod('previousYear',function($app,$date=null){
	        if(!$date) $date=$this->api->today;
	        return date("Y-m-d", strtotime(date("Y-m-d", strtotime($date)) . " -1 YEAR"));
	    });

	    $this->app->addMethod('getFinancialYear',function($app,$date=null,$start_end = 'both'){
	        if(!$date) $date = $this->api->now;
	        $month = date('m',strtotime($date));
	        $year = date('Y',strtotime($date));
	        if($month >=1 AND $month <=3  ){
	            $f_year_start = $year-1;
	            $f_year_end = $year;
	        }
	        else{
	            $f_year_start = $year;
	            $f_year_end = $year+1;
	        }

	        if(strpos($start_end, 'start') !==false){
	            return $f_year_start.'-04-01';
	        }
	        if(strpos($start_end, 'end') !==false){
	            return $f_year_end.'-03-31';
	        }

	        return array(
	                'start_date'=>$f_year_start.'-04-01',
	                'end_date'=>$f_year_end.'-03-31'
	            );

    		});

    $this->app->addMethod('getFinancialQuarter',function ($date=null,$start_end = 'both'){
        if(!$date) $date = $this->api->today;

        $month = date('m',strtotime($date));
        $year = date('Y',strtotime($date));
        
        switch ($month) {
            case 1:
            case 2:
            case 3:
                $q_month_start='-01-01';
                $q_month_end='-03-31';
                break;
            case 4:
            case 5:
            case 6:
                $q_month_start='-04-01';
                $q_month_end='-06-30';
                break;
            case 7:
            case 8:
            case 9:
                $q_month_start='-07-01';
                $q_month_end='-09-30';
                break;
            case 10:
            case 11:
            case 12:
                $q_month_start='-10-01';
                $q_month_end='-12-31';
                break;
        }

        
        if(strpos($start_end, 'start') !== false){
            return $year.$q_month_start;
        }
        if(strpos($start_end, 'end') !== false){
            return $year.$q_month_end;
        }

        return array(
                'start_date'=>$year.$q_month_start,
                'end_date'=>$year.$q_month_end
            );

   		});

		$this->app->addMethod('my_date_diff',function($app,$d1,$d2){
	        $d1 = (is_string($d1) ? strtotime($d1) : $d1);
	        $d2 = (is_string($d2) ? strtotime($d2) : $d2);

	        $diff_secs = abs($d1 - $d2);
	        $base_year = min(date("Y", $d1), date("Y", $d2));

	        $diff = mktime(0, 0, $diff_secs, 1, 1, $base_year);
	        return [
			        "years" => date("Y", $diff) - $base_year,
			        "months_total" => (date("Y", $diff) - $base_year) * 12 + date("n", $diff) - 1,
			        "months" => date("n", $diff) - 1,
			        "days_total" => floor($diff_secs / (3600 * 24)),
			        "days" => date("j", $diff) - 1,
			        "hours_total" => floor($diff_secs / 3600),
			        "hours" => date("G", $diff),
			        "minutes_total" => floor($diff_secs / 60),
			        "minutes" => (int) date("i", $diff),
			        "seconds_total" => $diff_secs,
			        "seconds" => (int) date("s", $diff)
	        	];
    	});

	}

	function resetDB(){
		// Clear DB
		// if(!isset($this->app->old_epan)) $this->app->old_epan = $this->app->epan;
  //       if(!isset($this->app->new_epan)) $this->app->new_epan = $this->app->epan;
        
		// $this->app->epan=$this->app->old_epan;
  //       $truncate_models = ['EntryTemplateTransactionRow','EntryTemplateTransaction','EntryTemplate','TransactionType','TransactionRow','Transaction','Ledger','Group','BalanceSheet','Currency'];
  //       foreach ($truncate_models as $t) {
  //           $m=$this->add('xepan\accounts\Model_'.$t);
  //           foreach ($m as $mt) {
  //               $mt->delete();
  //           }
  //       }
		// $this->app->epan=$this->app->new_epan;
		// Orphan currencies
		$d = $this->app->db->dsql();
        $d->sql_templates['delete'] = "delete [table] from  [table] [join] [where]";
        $d->table('currency')->where('document.id is null')->join('document',null,'left')->delete();

       	$default_currency = $this->add('xepan\accounts\Model_Currency')
       			->set('name','Default Currency')
       			->set('value',1)
       			->save();

       	$config_m = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'currency_id'=>'DropDown'
							],
					'config_key'=>'FIRM_DEFAULT_CURRENCY_ID',
					'application'=>'accounts'
			]);
		$config_m->tryLoadAny();

       	$config = $this->app->epan->ref('Configurations')->tryLoadAny();
       	$config_m['currency_id'] = $default_currency->id;
       	$config_m->save();
       	
       	$this->app->epan->default_currency = $this->add('xepan\accounts\Model_Currency')->tryLoadBy('id',$config_m['currency_id']);
       
       /*Default Balance Sheet Heads and groups*/
       $this->add('xepan\accounts\Model_BalanceSheet')->loadDefaults();
       $this->add('xepan\accounts\Model_Group')->loadDefaults();
       $this->add('xepan\accounts\Model_Ledger')->loadDefaults();
	
       	/*Default Account Entry*/
       	
       	$path=realpath(getcwd().'/vendor/xepan/accounts/defaultAccount');
		// throw new \Exception($path, 1);
		
		if(file_exists($path)){
       		foreach (new \DirectoryIterator($path) as $file) {
       			 if($file->isDot()) continue;
       			// echo $path."/".$file;
       			 $json= file_get_contents($path."/".$file);
       			 $import_model = $this->add('xepan\accounts\Model_EntryTemplate');
       			 $import_model->importJson($json);
       		}
       	}	

       	
	}

}
