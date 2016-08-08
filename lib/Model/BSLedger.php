<?php



namespace xepan\accounts;


class Model_BSLedger extends Model_Ledger {
	
	public $from_date = '1970-01-01';
	public $to_date = '2017-01-01';
	public $p_and_l=false;

	function init(){
		parent::init();

		$this->addExpression('PreviousTransactionDR')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->addCondition('created_at','<',$this->from_date)->sum('amountDr');
		});

		$this->addExpression('PreviousTransactionCR')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->addCondition('created_at','<',$this->from_date)->sum('amountCr');
		});

		$this->addExpression('openBalanceDRSUM')->set(function($m,$q){
			$m1 = $this->add('xepan\accounts\Model_BSLedger',['table_alias'=>'opdrsum']);
			return $m1->addCondition('balance_sheet_id',$m->getElement('balance_sheet_id'))
					->sum('OpeningBalanceDr');
		});

		$this->addExpression('openBalanceCRSUM')->set(function($m,$q){
			$m1 = $this->add('xepan\accounts\Model_BSLedger',['table_alias'=>'opcrsum']);
			return $m1->addCondition('balance_sheet_id',$m->getElement('balance_sheet_id'))
					->sum('OpeningBalanceCr');
		});

		$this->addExpression('OpeningBalanceDrOnDate')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+ IFNULL([1],0)',[$m->getElement('openBalanceDRSUM'),$m->getElement('PreviousTransactionDR')]);
		});

		$this->addExpression('OpeningBalanceCrOnDate')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)',[$m->getElement('openBalanceCRSUM'),$m->getElement('PreviousTransactionCR')]);
		});

		$this->addExpression('transactionsDR')->set(function($m,$q){
			return $m->refSQL('TransactionRows')
							->addCondition('created_at','>=',$this->from_date)
							->addCondition('created_at','<',$this->app->nextDate($this->to_date))
							->sum('amountDr');
		});

		$this->addExpression('transactionsCR')->set(function($m,$q){
			return $m->refSQL('TransactionRows')
							->addCondition('created_at','>=',$this->from_date)
							->addCondition('created_at','<',$this->app->nextDate($this->to_date))
							->sum('amountCr');
		});

		$this->addExpression('closingBalanceDR')->set(function($m,$q){
			return $q->expr('[0]+[1]',[$m->getElement('OpeningBalanceDrOnDate'),$m->getElement('transactionsDR')]);
		});

		$this->addExpression('closingBalanceCR')->set(function($m,$q){
			return $q->expr('[0]+[1]',[$m->getElement('OpeningBalanceCrOnDate'),$m->getElement('transactionsCR')]);
		});

	}
}