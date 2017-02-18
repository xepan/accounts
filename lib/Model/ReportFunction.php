<?php
namespace xepan\accounts;

class Model_ReportFunction extends \xepan\base\Model_Table{
	public $table="report_function";
	public $acl=false;
	function init(){
		parent::init();

		$this->addField('name')->hint('without space between words');
		$this->addField('type')->enum([
								'HeadBalance',
								'GroupBalance',
								'GroupOnlyBalance',
								'GroupDR',
								'GroupCR',
								'GroupOnlyDR',
								'GroupOnlyCR',
								'LedgerBalance',
								'HeadDR',
								'HeadCR',
								'HeadTransactionSUMDR',
								'HeadTransactionSUMCR',
								'GroupTransactionSUMDR',
								'GroupTransactionSUMCR',
								'GroupOnlyTransactionSUMDR',
								'GroupOnlyTransactionSUMCR',
								'PANDL'
							]);

		$this->addField('group_value');
		$this->addField('head_value')->type('text');
		$this->addField('ledger_value')->type('text');

		$this->addField('start_date')->type('date');
		$this->addField('end_date')->type('date');
		
		// $this->addHook('beforeSave',$this);
	}

	// function beforeSave(){

	// 	if(preg_match('/\s/',$this['name']))
	// 		throw $this->exception('whitespace are not allowed', 'ValidityCheck')->setField('name');

	// }

	function getResult(){
		if(!$this->loaded()) throw new \Exception("layout model must loaded", 1);
		return 10;		
	}	
}


// HeadBalance(HeadName,StartDate,EndDate)
// GroupBalance
// GroupOnlyBalance
// LedgerBalance
// HeadDR
// HeadCR
// GroupDR
// GroupCR
// GroupOnlyDR
// GroupOnlyCR
// HeadTransactionSUMDR
// HeadTransactionSUMCR
// GroupTransactionSUMDR
// GroupTransactionSUMCR
// GroupOnlyTransactionSUMDR
// GroupOnlyTransactionSUMCR
// PANDL