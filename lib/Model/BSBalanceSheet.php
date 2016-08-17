<?php


namespace xepan\accounts;

class Model_BSBalanceSheet extends Model_BalanceSheet{

	public $from_date=null;
	public $to_date=null;

	function init(){
		parent::init();

		$this->addExpression('OpeningBalanceDr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			return $ledger->addCondition('balance_sheet_id',$q->getField('id'))
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceDr')]));
		});
		$this->addExpression('OpeningBalanceCr')->set(function($m,$q){
			$ledger = $m->add('xepan\accounts\Model_Ledger');
			return $ledger->addCondition('balance_sheet_id',$q->getField('id'))
						->sum($q->expr('IFNULL([0],0)',[$ledger->getElement('OpeningBalanceCr')]));
		});

		$this->addExpression('PreviousTransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('balance_sheet_id',$q->getField('id'))
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('PreviousTransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('balance_sheet_id',$q->getField('id'))
								->addCondition('created_at','<',$this->from_date)
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('TransactionsDr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('balance_sheet_id',$q->getField('id'))
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountDr')]));
		});
		$this->addExpression('TransactionsCr')->set(function($m,$q){
			$transaction =  $m->add('xepan\accounts\Model_TransactionRow');
			return $transaction->addCondition('balance_sheet_id',$q->getField('id'))
								->addCondition('created_at','>=',$this->from_date)
								->addCondition('created_at','<',$this->app->nextDate($this->to_date))
								->sum($q->expr('IFNULL([0],0)',[$transaction->getElement('amountCr')]));
		});

		$this->addExpression('ClosingBalanceDr')->set(function($m,$q){
			return $q->expr('
				IF(report_name="BalanceSheet",
				IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
				IFNULL([2],0))',[
					$m->getElement('OpeningBalanceDr'),
					$m->getElement('PreviousTransactionsDr'),
					$m->getElement('TransactionsDr')
				]);
		});
		$this->addExpression('ClosingBalanceCr')->set(function($m,$q){
			return $q->expr('
				IF(report_name="BalanceSheet",
				IFNULL([0],0)+IFNULL([1],0)+IFNULL([2],0),
				IFNULL([2],0))',[
					$m->getElement('OpeningBalanceCr'),
					$m->getElement('PreviousTransactionsCr'),
					$m->getElement('TransactionsCr')
				]);
		})->type('money');

		$this->addExpression('is_left')->set(function($m,$q){
			return $q->expr('IF(([0]-[1])>=0 AND [2]="LT",1,0)',[
					$m->getElement('ClosingBalanceDr'),
					$m->getElement('ClosingBalanceCr'),
					$m->getElement('positive_side'),

				]);
		})->type('money');

		$this->setOrder('order');

	}

	function getTradingBalance($from_date,$to_date){
		$bsbalancesheet = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bsbalancesheet->addCondition('report_name','Trading');

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;
		$gross_profit = 0;
		$gross_loss = 0;

		foreach ($bsbalancesheet as $bs) {
			$side='CR';
			if(strtolower($bs['subtract_from'])=='cr'){
				$amount  = $bs['ClosingBalanceCr'] - $bs['ClosingBalanceDr'];
			}else{
				$side='DR';
				$amount  = $bs['ClosingBalanceDr'] - $bs['ClosingBalanceCr'];
			}

			if($amount >=0 && $side == $bs['subtract_from']){
				if($bs['positive_side']=='LT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
					$gross_loss += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
					$gross_profit += abs($amount);
				}
			}else{
				if($bs['positive_side']=='RT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
					$gross_loss += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
					$gross_profit += abs($amount);
				}
			}
		}

		// var_dump($gross_profit);
		// var_dump($gross_loss);
		if($gross_profit > $gross_loss){
			$gross_profit -= $gross_loss;
			$gross_loss = 0;
		}else{
			$gross_loss -= $gross_profit;
			$gross_profit = 0;
		}

		if($gross_profit >= 0){
			$left[] = ['name'=>'Gross Profit','amount'=>abs($gross_profit),'id'=>'gross_profit'];
			$left_sum += $gross_profit;	
		}

		if($gross_loss > 0){
			$right[] = ['name'=>'Gross Loss','amount'=>abs($gross_loss),'id'=>'gross_loss'];
			$right_sum += $gross_loss;
		}

		return ['left'=>$left,'right'=>$right,'left_sum'=>$left_sum,'right_sum'=>$right_sum, 'gross_profit'=>$gross_profit,'gross_loss'=>$gross_loss];
	}

	function getPandL($from_date,$to_date){
		$bsbalancesheet = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bsbalancesheet->addCondition('report_name','Profit & Loss');

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;

		foreach ($bsbalancesheet as $bs) {
			$side='CR';
			if($bs['subtract_from']=='CR'){
				$amount  = $bs['ClosingBalanceCr'] - $bs['ClosingBalanceDr'];
			}else{
				$side='DR';
				$amount  = $bs['ClosingBalanceDr'] - $bs['ClosingBalanceCr'];
			}

			if($amount >=0 && $side == $bs['subtract_from']){
				if($bs['positive_side']=='LT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}else{
				if($bs['positive_side']=='RT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}
		}
		
		// get Trading
		$trading = $this->getTradingBalance($from_date,$to_date);
		$gross_profit = $trading['gross_profit'];
		$gross_loss = $trading['gross_loss'];
		
		// var_dump($trading);
		// exit;

		if($gross_profit >= 0){
			$right[] = ['name'=>'Gross Profit','amount'=>abs($gross_profit),'id'=>'gross_profit'];
			$right_sum += $gross_profit;
		}

		if($gross_loss > 0){
			$left[] = ['name'=>'Gross Loss','amount'=>abs($gross_loss),'id'=>'gross_loss'];
			$left_sum += $gross_loss;
		}

		$net_profit = 0;
		$net_loss = 0;

		if($right_sum > $left_sum){
			$net_profit = $right_sum - $left_sum;
			$left[] = ['name'=>'Net Profit','amount'=>abs($net_profit),'id'=>'net_profit'];
			$left_sum += $net_profit;
		}else{
			$net_loss = $left_sum - $right_sum;
			$left[] = ['name'=>'Net Loss','amount'=>abs($net_loss),'id'=>'net_loss'];
			$right_sum += $net_loss;
		}

		return ['left'=>$left,'right'=>$right,'left_sum'=>$left_sum,'right_sum'=>$right_sum,'net_profit'=>$net_profit,'net_loss'=>$net_loss, 'gross_profit'=>$gross_profit,'gross_loss'=>$gross_loss];
	}

	function getBalanceSheet($from_date,$to_date){
		$bsbalancesheet = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bsbalancesheet->addCondition('report_name','BalanceSheet');

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;

		$openning_balances_dr=0;
		$openning_balances_cr=0;

		foreach ($bsbalancesheet as $bs) {
			$side='CR';
			if($bs['subtract_from']=='CR'){
				$amount  = $bs['ClosingBalanceCr'] - $bs['ClosingBalanceDr'];
				$openning_balances_cr += $bs['OpeningBalanceCr'];
			}else{
				$side = 'DR';
				$amount  = $bs['ClosingBalanceDr'] - $bs['ClosingBalanceCr'];
				$openning_balances_dr += $bs['OpeningBalanceDr'];
			}
			if($amount >=0 && $side == $bs['subtract_from']){
				if($bs['positive_side']=='LT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}else{
				if($bs['positive_side']=='RT'){
					$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$left_sum += abs($amount);
				}else{
					$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
					$right_sum += abs($amount);
				}
			}
			// echo $bs['name'] . ' - ' . $amount . '<br/>';
		}

		// var_dump($left,$right);
		// exit;

		$pandl = $this->getPandL($from_date,$to_date);
		$net_profit = $pandl['net_profit'];
		$net_loss = $pandl['net_loss'];

		$gross_profit = $pandl['gross_profit'];
		$gross_loss = $pandl['gross_loss'];

		if($net_profit >= 0){
			$left[] = ['name'=>'Profit','amount'=>abs($net_profit),'id'=>'net_profit'];
			$left_sum += $net_profit;
		}

		if($net_loss > 0){
			$right[] = ['name'=>'Loss','amount'=>abs($net_loss),'id'=>'net_loss'];
			$right_sum += $net_loss;
		}

		$opening_balance_diff=$openning_balances_dr - $openning_balances_cr;
		
		if($opening_balance_diff>0){
			$left[] = ['name'=>'Opp. Balance Diff','amount'=>abs($opening_balance_diff),'id'=>'opening_balnce_diff'];
			$left_sum += $opening_balance_diff;
		}

		if($opening_balance_diff<0){
			$right[] = ['name'=>'Opp. Balance Diff','amount'=>abs($opening_balance_diff),'id'=>'opening_balnce_diff'];
			$right_sum += $opening_balance_diff;
		}

		return ['left'=>$left,'right'=>$right,'left_sum'=>$left_sum,'right_sum'=>$right_sum,'net_profit'=>$net_profit,'net_loss'=>$net_loss,'gross_profit'=>$gross_profit,'gross_loss'=>$gross_loss,'openning_balances_dr'=>$openning_balances_dr,'openning_balances_cr'=>$openning_balances_cr];

	}
}