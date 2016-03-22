<?php
namespace xepan\accounts;

class Model_TransactionRow extends \xepan\base\Model_Table{
	public $table="account_transaction_row";
	function init(){
		parent::init();
		
		$this->hasOne('xepan\base\Epan','epan_id');

		$this->hasOne('xepan\accounts\Transaction','transaction_id');
		$this->hasOne('xepan\accounts\Account','account_id');
		$this->addField('amountDr')->caption('Debit')->type('money');
		$this->addField('amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');

		$this->addExpression('created_at')->set($this->refSQL('transaction_id')->fieldQuery('created_at'));
		$this->addExpression('voucher_no')->set($this->refSQL('transaction_id')->fieldQuery('voucher_no'));
		$this->addExpression('Narration')->set($this->refSQL('transaction_id')->fieldQuery('Narration'));
		$this->addExpression('transaction_type')->set($this->refSQL('transaction_id')->fieldQuery('transaction_type'));

		$this->addHook('beforeDelete',[$this,'deleteTransactionAndRow']);

	}

	function account(){
		return $this->ref('account_id');
	}
	/*===TODO This Code Temporary  ====*/
	function deleteTransactionAndRow(){
		$tra=$this->add('xepan\accounts\Model_Transaction');
		$tra->load($this['transaction_id']);
		$tra->ref('TransactionRows')->deleteAll();
		$tra->deleteAll();
	}
	// function beforeDelete(){
	// 	if($this['amountCr'])
	// 		$this->account()->creditOnly(-1 * $this['amountCr']);

	// 	if($this['amountDr'])
	// 		$this->account()->debitOnly(-1 * $this['amountDr']);
		
	// }

	
}