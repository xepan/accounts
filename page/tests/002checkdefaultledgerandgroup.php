<?php
namespace xepan\accounts;



class page_tests_002checkdefaultledgerandgroup extends \xepan\base\Page_Tester {
    public $title = 'Ledger/Group Tests';

    public $proper_responses=[
        '-'=>'-'
    ];

    function init(){
        $this->add('xepan\accounts\page_tests_init');
        parent::init();
    }

    function prepare_loadDefaultBankLedger(){
    	$this->defaultBankLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger();
    	$this->proper_responses['test_loadDefaultBankLedger']=[
    		'name'=>'Your Default Bank Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>'BankAccount',
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Bank Account')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultBankLedger(){
    	$l = $this->defaultBankLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultBankLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}


	function prepare_loadDefaultCashLedger(){
    	$this->defaultCashLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
    	$this->proper_responses['test_loadDefaultCashLedger']=[
    		'name'=>'Cash Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>'CashAccount',
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Cash Account')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultCashLedger(){
    	$l = $this->defaultCashLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultCashLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}


	function prepare_loadDefaultSalesLedger(){
    	$this->defaultSalesLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultSalesLedger();
    	$this->proper_responses['test_loadDefaultSalesLedger']=[
    		'name'=>'Sales Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>'SalesAccount',
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Sales')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultSalesLedger(){
    	$l = $this->defaultSalesLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultSalesLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}

	function prepare_loadDefaultPurchaseLedger(){
    	$this->defaultPurchaseLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultPurchaseLedger();
    	$this->proper_responses['test_loadDefaultPurchaseLedger']=[
    		'name'=>'Purchase Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>'PurchaseAccount',
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Purchase')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultPurchaseLedger(){
    	$l = $this->defaultPurchaseLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultPurchaseLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}


	function prepare_loadDefaultTaxLedger(){
    	$this->defaultTaxLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultTaxLedger();
    	$this->proper_responses['test_loadDefaultTaxLedger']=[
    		'name'=>'Tax Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>null,
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Duties & Taxes')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultTaxLedger(){
    	$l = $this->defaultTaxLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultTaxLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}


	function prepare_loadDefaultDiscountGivenLedger(){
        $this->defaultDiscountLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultDiscountGivenLedger();
        $this->proper_responses['test_loadDefaultDiscountGivenLedger']=[
            'name'=>'Discount Given',   
            'contact_id'=>null,
            'LedgerDisplayName'=>null,
            'is_active'=>1,
            'OpeningBalanceDr'=>0,
            'OpeningBalanceCr'=>0,
            'affectsBalanceSheet'=>1,
            'created_at' => $this->app->today,
            'updated_at'=>$this->app->today,
            'epan_id'=>$this->app->epan->id,
            'related_id'=>null,
            'ledger_type'=>null,
            'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Direct Expenses')->del('fields')->field('id')->getOne()
        ];
    }

    function test_loadDefaultDiscountGivenLedger(){
        $l = $this->defaultDiscountLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultDiscountGivenLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
    }

    function prepare_loadDefaultDiscountRecieveLedger(){
    	$this->defaultDiscountLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultDiscountRecieveLedger();
    	$this->proper_responses['test_loadDefaultDiscountRecieveLedger']=[
    		'name'=>'Discount Recieve',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>null,
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Indirect Income')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultDiscountRecieveLedger(){
    	$l = $this->defaultDiscountLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultDiscountRecieveLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}

	function prepare_loadDefaultRoundLedger(){
    	$this->defaultRoundLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultRoundLedger();
    	$this->proper_responses['test_loadDefaultRoundLedger']=[
    		'name'=>'Round Account',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>null,
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Indirect Income')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultRoundLedger(){
    	$l = $this->defaultRoundLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultRoundLedger'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}

	function prepare_loadDefaultExchangeGain(){
    	$this->defaultGainLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultExchangeGain();
    	$this->proper_responses['test_loadDefaultExchangeGain']=[
    		'name'=>'Exchange Gain',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>null,
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Indirect Income')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultExchangeGain(){
    	$l = $this->defaultGainLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultExchangeGain'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}

	function prepare_loadDefaultExchangeLoss(){
    	$this->defaultLossLedger= $this->add('xepan\accounts\Model_Ledger')->loadDefaultExchangeLoss();
    	$this->proper_responses['test_loadDefaultExchangeLoss']=[
    		'name'=>'Exchange Loss',	
    		'contact_id'=>null,
    		'LedgerDisplayName'=>null,
    		'is_active'=>1,
    		'OpeningBalanceDr'=>0,
    		'OpeningBalanceCr'=>0,
    		'affectsBalanceSheet'=>1,
    		'created_at' => $this->app->today,
    		'updated_at'=>$this->app->today,
    		'epan_id'=>$this->app->epan->id,
    		'related_id'=>null,
    		'ledger_type'=>null,
    		'group_id'=>$this->app->db->dsql()->table('account_group')->where('name','Indirect Expenses')->del('fields')->field('id')->getOne()
    	];
    }

    function test_loadDefaultExchangeLoss(){
    	$l = $this->defaultLossLedger;
        $result=[];
        foreach ($this->proper_responses['test_loadDefaultExchangeLoss'] as $field => $value) {
            $result[$field] = $l[$field];            
        }
        return $result; 
	}


    function prepare_checkRootBankGroup(){
        $this->proper_responses['test_checkRootBankGroup']=[
                'epan_id'=>$this->app->epan->id,
                'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Current Assets')->del('fields')->field('id')->getOne(),
                'name'=>'Bank Account',
                'created_at'=>$this->app->today,
                'parent_group_id'=>null,
                'root_group_id'=>true
        ];
    }

    function test_checkRootBankGroup(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadRootBankGroup();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];

        return $result;
    }

    function prepare_checkRootCashGroup(){
        $this->proper_responses['test_checkRootCashGroup']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Current Assets')->del('fields')->field('id')->getOne(),
            'name'=>'Cash Account',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkRootCashGroup(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadRootCashGroup();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }

    function prepare_checkIndirectExpenses(){
        $this->proper_responses['test_checkIndirectExpenses']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Expenses')->del('fields')->field('id')->getOne(),
            'name'=>'Indirect Expenses',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkIndirectExpenses(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadIndirectExpenses();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }

    function prepare_checkIndirectIncome(){
        $this->proper_responses['test_checkIndirectIncome']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Income')->del('fields')->field('id')->getOne(),
            'name'=>'Indirect Income',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkIndirectIncome(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadIndirectIncome();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }

    function prepare_checkRootSalesGroup(){
        $this->proper_responses['test_checkRootSalesGroup']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Sales')->del('fields')->field('id')->getOne(),
            'name'=>'Sales',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkRootSalesGroup(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadRootSalesGroup();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }

    function prepare_checkRootPurchaseGroup(){
        $this->proper_responses['test_checkRootPurchaseGroup']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Purchase')->del('fields')->field('id')->getOne(),
            'name'=>'Purchase',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkRootPurchaseGroup(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadRootPurchaseGroup();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }

    function prepare_checkDutiesAndTaxes(){
        $this->proper_responses['test_checkDutiesAndTaxes']=[
            'epan_id'=>$this->app->epan->id,
            'balance_sheet_id'=>$this->app->db->dsql()->table('account_balance_sheet')->where('name','Duties & Taxes')->del('fields')->field('id')->getOne(),
            'name'=>'Duties & Taxes',
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>true
        ];
    }

    function test_checkDutiesAndTaxes(){
        $result=[];
        $grp = $this->add('xepan\accounts\Model_Group')->loadDutiesAndTaxes();
        $result =[
            'epan_id'=>$grp['epan_id'],
            'balance_sheet_id'=>$grp['balance_sheet_id'],
            'name'=>$grp['name'],
            'created_at'=>$grp['created_at'],
            'parent_group_id'=>$grp['parent_group_id'],
            'root_group_id'=>$grp['root_group_id']
        ];
        return $result;
    }
}