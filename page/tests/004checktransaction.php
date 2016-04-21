<?php
namespace xepan\accounts;

class page_tests_004checktransaction extends \xepan\base\Page_Tester {
    public $title = 'Transaction Tests';
    public $ledgers;
    public $proper_responses=[
    '-'=>'-'
    ];

    function init(){
        $this->add('xepan\accounts\page_tests_init');
        $this->createLedgers();
        // $this->createNewTransaction();
        parent::init();
    }

    function createLedgers(){
        $ledger=[];

        $ledger[1] = $this->add('xepan\accounts\Model_Ledger');
        $ledger[1]['name'] = 'Ledger A';
        $ledger[1]['ledger_type'] = 'Ledger Type';
        $ledger[1]['related_id'] = 1;
        $ledger[1]['group_id'] = 1;
        $ledger[1]['LedgerDisplayName'] = 'Display';
        $ledger[1]['is_active'] = 1;
        $ledger[1]['OpeningBalanceDr'] = 00;
        $ledger[1]['OpeningBalanceCr'] = 00;
        $ledger[1]['created_at'] = "2016-04-21";
        $ledger[1]['updated_at'] = "2016-04-21";
        $ledger[1]['affectsBalanceSheet'] = 1;
        $ledger[1]->save();

        $ledger[2] = $this->add('xepan\accounts\Model_Ledger');
        $ledger[2]['name'] = 'Ledger B';
        $ledger[2]['ledger_type'] = 'Ledger Type2';
        $ledger[2]['related_id'] = 1;
        $ledger[2]['group_id'] = 1;
        $ledger[2]['LedgerDisplayName'] = 'Display2';
        $ledger[2]['is_active'] = 1;
        $ledger[2]['OpeningBalanceDr'] = 00;
        $ledger[2]['OpeningBalanceCr'] = 00;
        $ledger[2]['created_at'] = "2016-04-21";
        $ledger[2]['updated_at'] = "2016-04-21";
        $ledger[2]['affectsBalanceSheet'] = 1;
        $ledger[2]->save();

        $ledger[3] = $this->add('xepan\accounts\Model_Ledger');
        $ledger[3]['name'] = 'Ledger C';
        $ledger[3]['ledger_type'] = 'Ledger Type3';
        $ledger[3]['related_id'] = 1;
        $ledger[3]['group_id'] = 1;
        $ledger[3]['LedgerDisplayName'] = 'Display3';
        $ledger[3]['is_active'] = 1;
        $ledger[3]['OpeningBalanceDr'] = 00;
        $ledger[3]['OpeningBalanceCr'] = 00;
        $ledger[3]['created_at'] = "2016-04-21";
        $ledger[3]['updated_at'] = "2016-04-21";
        $ledger[3]['affectsBalanceSheet'] = 1;
        $ledger[3]->save();

        $this->ledgers = $ledger;

        return $this;
    }


    function prepare_LedgerA(){
        $this->proper_responses['test_LedgerA']=[
        'name'=>'Ledger A',
        'LedgerDisplayName'=>'Display',
        'is_active'=>1,
        'OpeningBalanceDr'=>00,
        'OpeningBalanceCr'=>00,
        'affectsBalanceSheet'=>1,
        'created_at' => $this->app->today,
        'updated_at'=>$this->app->today,
        'related_id'=>1,
        'ledger_type'=>'Ledger Type',
        'group_id'=>1
        ];
    }

    function test_LedgerA(){
        $this->ledger = $this->add('xepan\accounts\Model_Ledger')
        ->loadBy('name','Ledger A');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_LedgerA'] as $field => $value) {
            $result[$field] = $this->ledger[$field];            
    }
    return $result;   
    }

    function prepare_LedgerB(){
        $this->proper_responses['test_LedgerB']=[
        'name'=>'Ledger B',
        'LedgerDisplayName'=>'Display2',
        'is_active'=>1,
        'OpeningBalanceDr'=>00,
        'OpeningBalanceCr'=>00,
        'affectsBalanceSheet'=>1,
        'created_at' => $this->app->today,
        'updated_at'=>$this->app->today,
        'related_id'=>1,
        'ledger_type'=>'Ledger Type2',
        'group_id'=>1
        ];
    }

    function test_LedgerB(){
        $this->ledger = $this->add('xepan\accounts\Model_Ledger')
        ->loadBy('name','Ledger B');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_LedgerB'] as $field => $value) {
            $result[$field] = $this->ledger[$field];            
    }
    return $result;   
    }

    function prepare_LedgerC(){
        $this->proper_responses['test_LedgerC']=[
        'name'=>'Ledger C',
        'LedgerDisplayName'=>'Display3',
        'is_active'=>1,
        'OpeningBalanceDr'=>00,
        'OpeningBalanceCr'=>00,
        'affectsBalanceSheet'=>1,
        'created_at' => $this->app->today,
        'updated_at'=>$this->app->today,
        'related_id'=>1,
        'ledger_type'=>'Ledger Type3',
        'group_id'=>1
        ];
    }

    function test_LedgerC(){
        $this->ledger = $this->add('xepan\accounts\Model_Ledger')
        ->loadBy('name','Ledger C');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_LedgerC'] as $field => $value) {
            $result[$field] = $this->ledger[$field];            
    }
    return $result;   
    }

    function prepare_Transaction1(){
        $this->proper_responses['test_Transaction1']=[
        'related_id'=>'Transaction 1',
        'related_type'=>'Related1',
        'name'=>1,
        'Narration'=>'this is new',
        'created_at'=>null,
        'updated_at'=>null,
        'exchange_rate'=>1,
        'transaction_type_id'=>1,
        'currency_id'=>1
        ];
    }

    function test_Transaction1(){
        $this->transaction = $this->add('xepan\accounts\Model_Transaction')
        ->tryLoadAny();                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_Transaction1'] as $field => $value) {
            $result[$field] = $this->transaction[$field];            
    }
    return $result;   
    }
    }