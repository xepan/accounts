<?php
namespace xepan\accounts;

class Model_TransactionRow extends \xepan\base\Model_Table{
	public $table="account_transaction_row";
	function init(){
		parent::init();
		
		$this->hasOne('xepan\base\Epan','epan_id');

		$this->hasOne('xepan\accounts\Transaction','transaction_id');
		$this->hasOne('xepan\accounts\Ledger','ledger_id');
		$this->hasOne('xepan\accounts\Currency');

		$this->addField('_amountDr')->caption('Debit')->type('money');
		$this->addField('_amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');
		$this->addField('exchange_rate')->type('number');

		$this->addExpression('amountCr')->set($this->dsql()->expr('([0]*[1])',[$this->getElement('_amountCr'),$this->getElement('exchange_rate')]));
		$this->addExpression('amountDr')->set($this->dsql()->expr('([0]*[1])',[$this->getElement('_amountDr'),$this->getElement('exchange_rate')]));

		$this->addExpression('created_at')->set($this->refSQL('transaction_id')->fieldQuery('created_at'));
		$this->addExpression('voucher_no')->set($this->refSQL('transaction_id')->fieldQuery('voucher_no'));
		$this->addExpression('Narration')->set($this->refSQL('transaction_id')->fieldQuery('Narration'));
		$this->addExpression('transaction_type')->set($this->refSQL('transaction_id')->fieldQuery('transaction_type'));

		$this->addExpression('root_group_name')->set(function($m,$q){
			// return ''
			return $l = $m->refSQL('ledger_id')->fieldQuery('root_group');
		});
		
		$this->addHook('beforeDelete',[$this,'deleteTransactionAndRow']);

	}

	function account(){
		return $this->ref('ledger_id');
	}

	/*===TODO This Code Temporary  ====*/
	function deleteTransactionAndRow(){
		// $tra=$this->add('xepan\accounts\Model_Transaction');
		// $tra->load($this['transaction_id']);
		// $tra->ref('TransactionRows')->deleteAll();
		// $tra->deleteAll();
	}

	function transaction(){
		return $this->ref('transaction_id')->tryLoadAny();
	}
	
}