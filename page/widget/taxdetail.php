<?php

namespace xepan\accounts;

class page_widget_taxdetail extends \xepan\base\Page{
	function init(){
		parent::init();

		$from_date = $this->app->stickyGet('from_date');
		$to_date = $this->app->stickyGet('to_date');
		$monthyear = $this->app->stickyGet('monthyear');
		$entity = $this->app->stickyGet('entity');
		
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

		$ledger_m->addCondition('monthyear',$monthyear);
		$ledger_m->setOrder('transaction_date','desc');
		$ledger_m->_dsql()->group('name');

		if($entity == 'credit_side'){
			$ledger_m->addExpression('total_amount')->set(function($m,$q){
				return $q->sum($m->getElement('_amountCr'));
			})->type('money');
		}
			
		if($entity == 'debit_side'){
			$ledger_m->addExpression('total_amount')->set(function($m,$q){
				return $q->sum($m->getElement('_amountDr'));
			})->type('money');
		}	

		$grid = $this->add('xepan\hr\Grid',null,null,['page\widget\taxdetail']);
		$grid->setModel($ledger_m,['name','total_amount']);
	}
}