<?php
namespace xepan\accounts;
class page_balancesheet extends \xepan\base\Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();

		$this->add('xepan\accounts\Model_BalanceSheet')->loadDefaults();
       	$this->add('xepan\accounts\Model_Group')->loadDefaults();
       	$this->add('xepan\accounts\Model_Ledger')->loadDefaults();

       	return;
		
		// $m = $this->add('xepan\accounts\Model_BSLedger');
		// $m->addExpression('DR')->set($m->sum('closingBalanceDR'));
		// $m->addExpression('CR')->set($m->sum('closingBalanceCR'));

		// $m->_dsql()->group($m->dsql()->expr('[0]',[$m->getElement('balance_sheet_id')]));

		// $g = $this->add('Grid');
		// $g->setModel($m,['balance_sheet','OpeningBalanceDr','PreviousTransactionDR','OpeningBalanceDrOnDate','OpeningBalanceCrOnDate','transactionsDR','transactionsCR','DR','CR'])
		// ->debug()
		// ;
		
		// return;

		$f=$this->add('Form',null,'form',['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date');
		$r->addField('DatePicker','to_date');
		$f->addSubmit('Go')->addClass('btn btn-primary xepan-push');


		$transactions = $this->add('xepan\accounts\Model_TransactionRow');

		$transactions->addExpression('OpeningDRSUM')->set($transactions->refSQL('ledger_id')->sum('OpeningBalanceDr'));
		// $transactions->addExpression('OpeningCRSUM')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		
		$transactions->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))+IFNULL([1],0)',[$transactions->getElement('amountDr'), $transactions->getElement('OpeningDRSUM')]));
		$transactions->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		$transactions->_dsql()->group('balance_sheet_id');
		$transactions->addCondition('report_name','BalanceSheet');
		// $transactions->addCondition('created_at','>=',$fy['start']);

		$pandl = $this->add('xepan\accounts\Model_TransactionRow');
		$pandl->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$pandl->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		// $pandl->_dsql()->group('balance_sheet_id','group','ledger']);
		$pandl->addCondition('report_name','Profit & Loss');
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

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($pandl,['balance_sheet_id','balance_sheet','is_pandl','group','ledger','CR','DR']);
		$grid->js(true)->find('table')->css('width','100%')->attr('border','1px')->attr('cell-padding','0.5em');

	}

	function defaultTemplate(){
		return ['page/balancesheet'];
	}
}