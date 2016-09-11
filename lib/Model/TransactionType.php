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

		$this->hasMany('xepan\accounts\Transaction','transaction_type_id');
	}

	function newVoucherNumber(){
		$tr = $this->ref('xepan\accounts\Transaction');
		return $tr->_dsql()->del('fields')->field($this->dsql()->expr('(IFNULL(max(cast([0] as unsigned)),0)+1)',[$tr->getElement('name')]))->getOne();
	}	

	function getReceiptIDs(){
		$type = $this->add('xepan\accounts\Model_TransactionType');

		$type->addCondition(
					$this->dsql->orExpr()
							->where('name','Bank Receipt')
							->where('name','Cash Receipt')
					);
		$array = [];
		foreach ($type as $junk) {
			$array[$type->id] = $type->id;
		}

		return $array;
	}
}