<?php
namespace xepan\accounts;
class page_balancesheet extends \Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();
		$balance_sheet = $this->add('xepan\accounts\Model_BalanceSheet');
		$balance_sheet->addCondition('is_pandl',false);
		$f=$this->add('Form');
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addSubmit('Go');

		$balance_sheet->addExpression('amount_DR')->set(function ($m,$q){
			return $this->add('xepan\accounts\Model_TransactionRow',['table_alias'=>'dr_tra_row'])
			->addCondition('balance_sheet',$m->getElement('id'))->sum('amountDr');
		});
		$balance_sheet->addExpression('amount_CR')->set(function ($m,$q){
			return $this->add('xepan\accounts\Model_TransactionRow',['table_alias'=>'dr_tra_row'])
			->addCondition('balance_sheet',$m->getElement('id'))->sum('amountCr');
		});
		
		$crud = $this->add('xepan\hr\Grid');
		$crud->setModel($balance_sheet);
		
	}
}