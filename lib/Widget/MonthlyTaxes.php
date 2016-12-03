<?php

namespace xepan\accounts;

class Widget_MonthlyTaxes extends \xepan\base\Widget{
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		$this->grid = $this->add('xepan\hr\Grid',null,null,['widget\monthlytax']);
	}

	function recursiveRender(){
		$start_date = $this->report->start_date;
		$end_date = $this->app->nextDate($this->report->end_date);

		$ledger_m = $this->add('xepan\accounts\Model_Ledger');
		$ledger_m->addCondition('group','Tax Payable');
		$ledger_m->addCondition('ledger_type','SalesServiceTaxes');
		
		$ledger_trow_j = $ledger_m->join('account_transaction_row.ledger_id');
		$ledger_trow_j->addField('_amountDr');
		$ledger_trow_j->addField('_amountCr');
		$transaction_trow_j = $ledger_trow_j->join('account_transaction','transaction_id');
		$transaction_trow_j->addField('transaction_date','created_at');
		
		$ledger_m->addExpression('monthyear')->set(function($m,$q){
			return $q->expr('DATE_FORMAT([0],"%M %Y")',
							[
								$m->getElement('transaction_date')
							]);
		});

		$ledger_m->_dsql()->group('monthyear');

		$ledger_m->addExpression('total_amount_dr')->set(function($m,$q){
			return $q->sum($m->getElement('_amountDr'));
		})->type('money');

		$ledger_m->addExpression('total_amount_cr')->set(function($m,$q){
			return $q->sum($m->getElement('_amountCr'));
		})->type('money');

		if($this->report->start_date)
			$ledger_m->addCondition('transaction_date','>=',$start_date);	
		if($this->report->end_date)
			$ledger_m->addCondition('transaction_date','<=',$end_date);	

		$ledger_m->setOrder('transaction_date','desc');
		$ledger_array = $ledger_m->getRows();	
		
		$salesinvoice_m = $this->add('xepan\commerce\Model_SalesInvoice');
		$salesinvoice_m->addCondition('status',['Due','Paid']);
		$salesinvoice_m->setOrder('created_at','desc');
		$salesinvoice_m->addExpression('monthyear')->set('DATE_FORMAT(created_at,"%M %Y")');
		$salesinvoice_m->addExpression('count','count(*)');
		$salesinvoice_m->_dsql()->group('monthyear');
			
		if($this->report->start_date)
			$salesinvoice_m->addCondition('created_at','>=',$start_date);
		if($this->report->end_date)
			$salesinvoice_m->addCondition('created_at','<=',$end_date);

		$sales_invoice_array = $salesinvoice_m->getRows();

		$merged_array = [];
		foreach ($ledger_array as $key => $value) {
			if(!in_array($value['monthyear'], array_keys($merged_array))) $merged_array[$value['monthyear']]=[];
			$merged_array [$value['monthyear']]['monthyear'] = $value['monthyear'];
			$merged_array [$value['monthyear']]['total_amount_cr'] = $value['total_amount_cr'];
			$merged_array [$value['monthyear']]['total_amount_dr'] = $value['total_amount_dr'];
		}

		foreach ($sales_invoice_array as $value) {			
			if(!in_array($value['monthyear'], array_keys($merged_array))) $merged_array[$value['monthyear']]=[];
			$merged_array [$value['monthyear']]['monthyear'] = $value['monthyear'];
			$merged_array [$value['monthyear']]['count'] = $value['count'];
		}

		$this->grid->setSource($merged_array);
		$this->grid->js('click')->_selector('.xepan-sales-invoice')->univ()->frameURL('Invoice',[$this->api->url('xepan_commerce_salesinvoice'),'from_date'=>$start_date,'to_date'=>$end_date,'monthyear'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		$this->grid->js('click')->_selector('.xepan-monthlytaxes-widget')->univ()->frameURL('Statement',[$this->api->url('xepan_accounts_widget_taxstatement'),'from_date'=>$start_date,'to_date'=>$end_date,'monthyear'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);	
		$this->grid->js('click')->_selector('.monthly-widget-amount-cr-detail')->univ()->frameURL('Paid Detail',[$this->api->url('xepan_accounts_widget_taxdetail'),'entity'=>'credit_side','from_date'=>$start_date,'to_date'=>$end_date,'monthyear'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		$this->grid->js('click')->_selector('.monthly-widget-amount-dr-detail')->univ()->frameURL('Payable Detail',[$this->api->url('xepan_accounts_widget_taxdetail'),'entity'=>'debit_side','from_date'=>$start_date,'to_date'=>$end_date,'monthyear'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id')]);
		
		return Parent::recursiveRender();
	}
} 