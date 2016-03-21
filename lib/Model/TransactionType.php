<?php
namespace xepan\accounts;

class Model_TransactionType extends \xepan\base\Model_Table{
	public $table="account_transaction_types";
	
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');

		$this->addField('name');
		$this->addField('FromAC');
		$this->addField('ToAC');
		$this->addField('Default_Narration');
	}

	function newVoucherNumber(){
		return rand(10000,99999);
	}	

}