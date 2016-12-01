<?php 

namespace xepan\accounts;

class Widget_CreditorAnlaysis extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('ledger');
		$this->report->enableFilterEntity('date_range');
		$this->grid = $this->add('xepan\base\Grid');
	}

	function recursiveRender(){
		$ledgrofcreditor_m = $this->add('xepan\accounts\Model_Ledger');
		$ledgrofcreditor_m->addCondition('group','Sundry Creditor');
		$trans_row_m = $this->add('xepan\accounts\Model_TransactionRow');
		if(isset($this->report->ledger)){
			$ledgrofcreditor_m->addCondition('id',$this->report->ledger);
			$trans_row_m->addCondition('ledger_id',$this->report->ledger);
		}
		$ledgrofcreditor_m->getElement('created_at')->destroy();
		$trow_j = $ledgrofcreditor_m->join('account_transaction_row.ledger_id');
		$trow_j->addField('ledger_id');
		$trow_j->addField('transaction_id');
		$trow_j->addField('original_amount_dr','_amountDr');
		$trow_j->addField('original_amount_cr','_amountCr');
		$trow_j->addField('exchange_rate');

		$trns = $trow_j->join('account_transaction','transaction_id');
		$trns->addField('transaction_type_id');
		$trns->addField('created_at');

		$ledgrofcreditor_m->addExpression('type_of_trans')->set(function($m,$q){
			$trans_type = $this->add('xepan\accounts\Model_TransactionType');
			$trans_type->addCondition('id',$m->getElement('transaction_type_id'))
				;
			return $trans_type->fieldQuery('name');
		});
		$ledgrofcreditor_m->_dsql()->group('type_of_trans');
		
		$ledgrofcreditor_m
				->addExpression('amountDr')
				->set($ledgrofcreditor_m->dsql()
				->expr('round(([0]*[1]),2)',[$ledgrofcreditor_m->getElement('original_amount_dr'),$ledgrofcreditor_m->getElement('exchange_rate')]));
		$ledgrofcreditor_m
				->addExpression('amountCr')
				->set($ledgrofcreditor_m->dsql()
				->expr('round(([0]*[1]),2)',[$ledgrofcreditor_m->getElement('original_amount_cr'),$ledgrofcreditor_m->getElement('exchange_rate')]));

		$ledgrofcreditor_m->addExpression('total_amount_cr')->set(function($m,$q){
			return $q->sum($m->getElement('amountCr'));
		})->type('money');

		$ledgrofcreditor_m->addExpression('total_amount_dr')->set(function($m,$q){
			return $q->sum($m->getElement('amountDr'));
		})->type('money');


		if(isset($this->report->start_date))
			$ledgrofcreditor_m->addCondition('created_at','>',$this->report->start_date);
		if(isset($this->report->end_date))
			$ledgrofcreditor_m->addCondition('created_at','<',$this->app->nextDate($this->report->end_date));
		$this->grid->setModel($ledgrofcreditor_m,
					['ledger_id','transaction_id',
					'type_of_trans','amountDr','amountCr',
					'total_amount_cr','total_amount_dr']);

		return parent::recursiveRender();
	}
}