<?php
namespace xepan\accounts;
class page_balancesheetdetail extends \xepan\base\Page{
	public $title="Balance Sheet's Groups ";
	function init(){
		parent::init();

		$balance_sheet_id = $this->app->stickyGET('account_balance_sheet_id');
		// $this->add('xepan\accounts\Model_Group')->addCondition('balance_sheet_id',$balance_sheet_id);


		$transactions = $this->add('xepan\accounts\Model_TransactionRow');

		$transactions->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$transactions->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		$transactions->_dsql()->group('group_id');
		// $transactions->addCondition('created_at','>=',$fy['start']);

		$pandl = $this->add('xepan\accounts\Model_TransactionRow');
		$pandl->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$pandl->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		// $pandl->_dsql()->group('balance_sheet_id','group','ledger']);
		$pandl->addCondition('is_pandl',true);
		// $transactions->addCondition('created_at','>=',$fy['start']);
		// $transactions->addCondition('created_at','<',$naxtday_of_selected_day);
		$pandl->tryLoadAny();

		foreach ($transactions as $tr) {
			$subtract_from = $tr['subtract_from'];
			$subtract = $subtract_from=='DR'?'CR':'DR';
			if(($amount = $tr[$subtract_from] - $tr[$subtract])>=0){
				$side='assets';
			}else{
				$side='liabilities';
			}

			$this->add('View',null,$side.'_name')
				->add('View')
				->setElement('a')
				->setAttr('href','?page=xepan_accounts_balancesheetdetail&account_balance_sheet_id='.$tr['balance_sheet_id'])
				->set($tr['balance_sheet']);
			$this->add('View',null,$side.'_amount')->set(abs($amount));

		}

		$subtract_from = $pandl['subtract_from']?:'DR';
		$subtract = $subtract_from=='DR'?'CR':'DR';
		if(($amount = $pandl[$subtract_from]-$pandl[$subtract]) < 0 ){
			$this->add('View',null,'assets_name')->set('PROFIT');
			$this->add('View',null,'assets_amount')->set(abs($amount));
		}else{
			$this->add('View',null,'liabilities_name')->set('LOSS');
			$this->add('View',null,'liabilities_amount')->set(abs($amount));
		}
	}

	function defaultTemplate(){
		return ['page/balancesheetdetail'];
	}
}