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

	function getReceiptIDs(){
		$type = $this->add('xepan\accounts\Model_TransactionType');

		$type->addCondition(
					$this->dsql->orExpr()
							->where('name','BANK RECEIPT')
							->where('name','CASH RECEIPT')
					);
		$array = [];
		foreach ($type as $junk) {
			$array[$type->id] = $type->id;
		}

		return $array;
	}
}