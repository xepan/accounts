<?php
namespace xepan\accounts;

class Model_TransactionRow extends \xepan\base\Model_Table{
	public $table="account_transaction_row";
	function init(){
		parent::init();
		
		$this->hasOne('xepan\base\Epan','epan_id');

		$this->hasOne('xepan\accounts\Transaction','transaction_id');
		$this->hasOne('xepan\accounts\Ledger','ledger_id');
		$this->hasOne('xepan\accounts\Currency','currency_id');

		$this->addField('_amountDr')->caption('Debit')->type('money');
		$this->addField('_amountCr')->caption('Credit')->type('money');
		$this->addField('side');
		$this->addField('accounts_in_side')->type('int');
		$this->addField('exchange_rate')->type('number');

		$this->addExpression('amountCr')->set($this->dsql()->expr('round(([0]*[1]),2)',[$this->getElement('_amountCr'),$this->getElement('exchange_rate')]));
		$this->addExpression('amountDr')->set($this->dsql()->expr('round(([0]*[1]),2)',[$this->getElement('_amountDr'),$this->getElement('exchange_rate')]));
		
		$this->addExpression('original_amount_dr')->set(function($m,$q){
				return $q->expr('(
						IF(
							IFNULL([0],1)<>1,
							CONCAT([1]," ",[2]),
							" "
						)
					)',[$m->getElement('exchange_rate'), $m->getElement('_amountDr'),$m->getElement('currency_id')]);
				
		})->type('money');


		$this->addExpression('original_amount_cr')->set(function($m,$q){
				return $q->expr('(
						IF(
							IFNULL([0],1)<>1,
							CONCAT([1]," ",[2]),
							" "
						)
					)',[$m->getElement('exchange_rate'), $m->getElement('_amountCr'),$m->getElement('currency_id')]);
				
		})->type('money');


		$this->addExpression('created_at')->set($this->refSQL('transaction_id')->fieldQuery('created_at'));
		$this->addExpression('voucher_no')->set($this->refSQL('transaction_id')->fieldQuery('voucher_no'));
		$this->addExpression('Narration')->set($this->refSQL('transaction_id')->fieldQuery('Narration'));
		$this->addExpression('transaction_type')->set($this->refSQL('transaction_id')->fieldQuery('transaction_type'));

		$this->addExpression('group_id')->set(function($m,$q){
			return $m->refSQL('ledger_id')->fieldQuery('group_id');
		});

		$this->addExpression('group')->set(function($m,$q){
			return $m->refSQL('ledger_id')->fieldQuery('group');
		});

		$this->addExpression('root_group_id')->set(function($m,$q){
			return $m->refSQL('ledger_id')->fieldQuery('root_group_id');
		});

		$this->addExpression('root_group')->set(function($m,$q){
			return $l = $m->refSQL('ledger_id')->fieldQuery('root_group');
		});

		$this->addExpression('balance_sheet_id')->set(function($m,$q){
			return  $m->refSQL('ledger_id')->fieldQuery('balance_sheet_id');
		});

		$this->addExpression('balance_sheet')->set(function($m,$q){
			return  $m->refSQL('ledger_id')->fieldQuery('balance_sheet');
		});

		$this->addExpression('is_pandl')->set(function($m,$q){
			return  $m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('is_pandl');
		});

		$this->addExpression('subtract_from')->set(function($m,$q){
			return  $m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('subtract_from');
		});

		$this->addExpression('positive_side')->set(function($m,$q){
			return  $m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('positive_side');
		});
		
		$this->addHook('beforeDelete',[$this,'deleteTransactionAndthis']);

	}

	function account(){
		return $this->ref('ledger_id');
	}

	/*===TODO This Code Temporary  ====*/
	function deleteTransactionAndthis(){
		// $tra=$this->add('xepan\accounts\Model_Transaction');
		// $tra->load($this['transaction_id']);
		// $tra->ref('Transactionthiss')->deleteAll();
		// $tra->deleteAll();
	}

	function transaction(){
		return $this->ref('transaction_id');
	}
	
}