<?php
namespace xepan\accounts;
class page_pandl extends \Page{
	public $title="Account Profit & Loss";
	function init(){
		parent::init();
		
		
		$f=$this->add('Form');
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addSubmit('Go');


		$transactions = $this->add('xepan\accounts\Model_TransactionRow');

		$transactions->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$transactions->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		$transactions->_dsql()->group('balance_sheet_id');
		$transactions->addCondition('is_pandl',true);
		// $transactions->addCondition('created_at','>=',$fy['start']);

		$pandl = $this->add('xepan\accounts\Model_TransactionRow');
		$pandl->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$pandl->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		$pandl->_dsql()->group(['balance_sheet_id','group','ledger']);
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

			$this->add('View',null,$side.'_name')->set($tr['balance_sheet']);
			$this->add('View',null,$side.'_amount')->set(abs($amount));

		}

		$subtract_from = $pandl['subtract_from']?:'DR';
		$subtract = $subtract_from=='DR'?'CR':'DR';
		if(($amount = $pandl[$subtract_from]-$pandl[$subtract]) > 0 ){
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