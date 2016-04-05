<?php
namespace xepan\accounts;
class page_balancesheet extends \Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();
		
		
		$f=$this->add('Form');
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addSubmit('Go');


		$transactions = $this->add('xepan\accounts\Model_TransactionRow');
		$transactions->addExpression('balance_sheet_id')->set(function($m,$q){
			return  $m->refSQL('ledger_id')->fieldQuery('balance_sheet_id');
		});

		$transactions->addExpression('balance_sheet')->set(function($m,$q){
			return  $m->refSQL('ledger_id')->fieldQuery('balance_sheet');
		});

		$transactions->addExpression('is_pandl')->set(function($m,$q){
			return  $m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('is_pandl');
		});

		$transactions->addExpression('DR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountDr')]));
		$transactions->addExpression('CR')->set($transactions->dsql()->expr('sum(IFNULL([0],0))',[$transactions->getElement('amountCr')]));
		$transactions->_dsql()->group('balance_sheet_id');


		// $transactions->addCondition('is_pandl',true);

		$grid = $this->add('xepan\hr\Grid');
		$grid->setModel($transactions,['balance_sheet_id','balance_sheet','is_pandl','CR','DR']);
		$grid->js(true)->find('table')->css('width','100%')->attr('border','1px')->attr('cell-padding','0.5em');
		
	}
}