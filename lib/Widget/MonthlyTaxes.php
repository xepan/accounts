<?php

namespace xepan\accounts;

class Widget_MonthlyTaxes extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->grid = $this->add('xepan\hr\Grid',null,null,['view\widget\monthlytax']);
	}

	function recursiveRender(){
		$start_date = $this->report->start_date;
		$end_date = $this->app->nextDate($this->report->end_date);

		$bs_group = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
		$bs_group->addCondition([['name','Tax Payable'],['name','Tax Receivable']]);
		$bs_group->addExpression('monthyear')->set('DATE_FORMAT(created_at,"%M %Y")');
		
		$dr_bal = 0;
		$cr_bal = 0;

		foreach ($bs_group as $bsg) {
			$dr_bal += $bsg['ClosingBalanceDr']; 
			$cr_bal += $bsg['ClosingBalanceCr'];
		}

		$infoarray = [];
		foreach ($bs_group as $group){
			$infoarray[] = ['name'=>$group['name'],'monthyear'=>$group['monthyear'],'type'=>'group','id'=>$group['id'],'balancecr'=>$group['ClosingBalanceCr'],'balancedr'=>$group['ClosingBalanceDr']]; 
		}


		// $bs_ledger = $this->add('xepan\accounts\Model_BSLedger',['from_date'=>$start_date,'to_date'=>$end_date]);
		// $bs_ledger->addCondition([['name','Tax Payable'],['name','Tax Receivable']]);

		// foreach ($bs_ledger as $led) {
		// 	$dr_bal += $led['ClosingBalanceDr']; 
		// 	$cr_bal += $led['ClosingBalanceCr'];
		// }

		// foreach ($bs_ledger as $ledger){
		// 	$subgroupandledger[] = ['name'=>$ledger['name'],'type'=>'ledger','id'=>$ledger['id'],'class'=>'xepan-accounts-ledger','balancecr'=>$ledger['ClosingBalanceCr'],'balancedr'=>$ledger['ClosingBalanceDr']]; 
		// }

		// $this->grid->setSource($subgroupandledger);
		$this->grid->setSource($infoarray);

		return Parent::recursiveRender();
	}
} 