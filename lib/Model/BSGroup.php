<?php


namespace xepan\accounts;

class Model_BSGroup extends Model_Group{

	public $from_date=null;
	public $to_date=null;

	function init(){
		parent::init();

		$this->addExpression('OpeningBalanceDr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			return $ledger->addCondition('group_path','like',$q->getField('path').'%')
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceDr')]));
		});
		$this->addExpression('OpeningBalanceCr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			return $ledger->addCondition('group_path','like',$q->getField('path').'%')
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceCr')]));
		});

		$this->addExpression('PreviousTransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('group_path','like',$q->getField('path').'%')
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('PreviousTransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('group_path','like',$q->getField('path').'%')
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('TransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('group_path','like',$q->getField('path').'%')
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->from_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('TransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('group_path','like',$q->getField('path').'%')
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->from_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('ClosingBalanceDr')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
					$m->getElement('OpeningBalanceDr'),
					$m->getElement('PreviousTransactionsDr'),
					$m->getElement('TransactionsDr')
				]);
		});
		$this->addExpression('ClosingBalanceCr')->set(function($m,$q){
			return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
					$m->getElement('OpeningBalanceCr'),
					$m->getElement('PreviousTransactionsCr'),
					$m->getElement('TransactionsCr')
				]);
		});

	}
}