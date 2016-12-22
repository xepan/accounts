<?php
namespace xepan\accounts;

class Model_SalaryLedgerAssociation extends \xepan\base\Model_Table{
	public $table="salary_ledger_association";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\Ledger','ledger_id')->sortable(true);
		$this->hasOne('xepan\hr\Salary','salary_id')->sortable(true);
		$this->addField('code');
	}
}
