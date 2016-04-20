<?php 

namespace xepan\accounts;

class page_tests_001checkResetDBRows extends \xepan\base\Page_Tester{
	public $title = "Row Count Test";
	public $proper_responses=[
    	'-'=>'-'
    ];

	function init(){
		$this->add('xepan\accounts\page_tests_init');
		parent::init();
	}

	function prepare_rowCount(){
		
		$this->proper_responses['test_rowCount']=[
			'account_transaction_row'=>0,
			'account_transaction'=>0,
			'ledger'=>0,
			'account_group'=>0,
			'account_balance_sheet'=>0,
			'currency'=>1
		];
	}	

	function test_rowCount(){
		$result = [];

		foreach ($this->proper_responses['test_rowCount'] as $table=>$requiredcount ) {
			$result[$table]=$this->app->db->dsql()->table($table)->del('fields')->field('count(*)')->getOne();
		}

		return $result;
	}
}