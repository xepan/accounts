<?php


namespace xepan\accounts;

class Model_BSGroup extends Model_Group{

	public $from_date=null;
	public $to_date=null;
	public $no_sub_groups=false;

	function init(){
		parent::init();

		$this->addExpression('OpeningBalanceDr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			if($this->no_sub_groups)
				$ledger->addCondition('group_id',$q->getField('id'));
			else
				$ledger->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));
			
			return $ledger->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceDr')]));
		});

		$this->addExpression('OpeningBalanceCr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			if($this->no_sub_groups)
				$ledger->addCondition('group_id',$q->getField('id'));
			else
			 	$ledger->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));

			return	$ledger->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceCr')]));
		});

		$this->addExpression('PreviousTransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			if($this->no_sub_groups)
				$transaction->addCondition('group_id',$q->getField('id'));
			else
				$transaction->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));

			return $transaction->addCondition('created_at','<',$this->from_date)
						->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('PreviousTransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			if($this->no_sub_groups)
				$transaction->addCondition('group_id',$q->getField('id'));
			else
				$transaction->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));
			
			return $transaction->addCondition('created_at','<',$this->from_date)
					->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('TransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			if($this->no_sub_groups)
				$transaction->addCondition('group_id',$q->getField('id'));
			else
				$transaction->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));
			
			return $transaction->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});

		$this->addExpression('TransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			if($this->no_sub_groups)
				$transaction->addCondition('group_id',$q->getField('id'));
			else
				$transaction->addCondition('group_path','like',$q->expr('CONCAT([0],"%")',[$q->getField('path')]));

			return $transaction->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('ClosingBalanceDr')->set(function($m,$q){
			return $q->expr('
				IF([3]="BalanceSheet",
				IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
				IFNULL([2],0))',[
					$m->getElement('OpeningBalanceDr'),
					$m->getElement('PreviousTransactionsDr'),
					$m->getElement('TransactionsDr'),
					$m->getElement('report_name')
				]);
		});
		$this->addExpression('ClosingBalanceCr')->set(function($m,$q){
			return $q->expr('
				IF([3]="BalanceSheet",
				IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
				IFNULL([2],0))',[
					$m->getElement('OpeningBalanceCr'),
					$m->getElement('PreviousTransactionsCr'),
					$m->getElement('TransactionsCr'),
					$m->getElement('report_name')
				]);
		})->type('money');

		// $this->addExpression('ClosingBalanceDr')->set(function($m,$q){
		// 	return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
		// 			$m->getElement('OpeningBalanceDr'),
		// 			$m->getElement('PreviousTransactionsDr'),
		// 			$m->getElement('TransactionsDr')
		// 		]);
		// });
		// $this->addExpression('ClosingBalanceCr')->set(function($m,$q){
		// 	return $q->expr('IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0)',[
		// 			$m->getElement('OpeningBalanceCr'),
		// 			$m->getElement('PreviousTransactionsCr'),
		// 			$m->getElement('TransactionsCr')
		// 		]);
		// });

	}
}