<?php 

namespace xepan\accounts;

class Widget_DebtorAnlaysis extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('ledger');
		$this->report->enableFilterEntity('date_range');
		// $this->chart = $this->add('xepan\base\View_Chart');
		$this->grid = $this->add('xepan\base\Grid');
	}

	function recursiveRender(){
		$ledgrofdebtor_m = $this->add('xepan\accounts\Model_Ledger');
		$ledgrofdebtor_m->addCondition('group','Sundry Debtor');
		$trans_row_m = $this->add('xepan\accounts\Model_TransactionRow');
		if(isset($this->report->ledger)){
			$ledgrofdebtor_m->addCondition('id',$this->report->ledger);
			$trans_row_m->addCondition('ledger_id',$this->report->ledger);
		}

		$ledgrofdebtor_m->getElement('created_at')->destroy();
		$trow_j = $ledgrofdebtor_m->join('account_transaction_row.ledger_id');
		$trow_j->addField('ledger_id');
		$trow_j->addField('transaction_id');
		$trow_j->addField('original_amount_dr','_amountDr');
		$trow_j->addField('original_amount_cr','_amountCr');
		$trow_j->addField('exchange_rate');

		$trns = $trow_j->join('account_transaction','transaction_id');
		$trns->addField('transaction_type_id');
		$trns->addField('created_at');

		$ledgrofdebtor_m->addExpression('type_of_trans')->set(function($m,$q){
			$trans_type = $this->add('xepan\accounts\Model_TransactionType');
			$trans_type->addCondition('id',$m->getElement('transaction_type_id'))
				;
			return $trans_type->fieldQuery('name');
		});

		$ledgrofdebtor_m->_dsql()->group('type_of_trans');

		$ledgrofdebtor_m->addExpression('amountDr')->set($ledgrofdebtor_m->dsql()->expr('round(([0]*[1]),2)',[$ledgrofdebtor_m->getElement('original_amount_dr'),$ledgrofdebtor_m->getElement('exchange_rate')]));
		$ledgrofdebtor_m->addExpression('amountCr')->set($ledgrofdebtor_m->dsql()->expr('round(([0]*[1]),2)',[$ledgrofdebtor_m->getElement('original_amount_cr'),$ledgrofdebtor_m->getElement('exchange_rate')]));

		$ledgrofdebtor_m->addExpression('total_amount_cr')->set(function($m,$q){
			return $q->sum($m->getElement('amountCr'));
		})->type('money');

		$ledgrofdebtor_m->addExpression('total_amount_dr')->set(function($m,$q){
			return $q->sum($m->getElement('amountDr'));
		})->type('money');
		$ledgrofdebtor_m->addExpression('trans_type')->set(function($m,$q){
			$trans_type = $this->add('xepan\accounts\Model_TransactionType');
			$trans_type->addCondition('name',$m->getElement('type_of_trans'))
				;
			return $trans_type->fieldQuery('name');
		});


		if(isset($this->report->start_date))
			$ledgrofdebtor_m->addCondition('created_at','>',$this->report->start_date);
		if(isset($this->report->end_date))
			$ledgrofdebtor_m->addCondition('created_at','<',$this->app->nextDate($this->report->end_date));			
		
		$this->grid->setModel($ledgrofdebtor_m,['ledger_id','transaction_id','type_of_trans','amountDr','amountCr',
			'total_amount_cr','total_amount_dr','trans_type']);
		
			// $this->chart->setType('bar')
	  //    		        ->setModel($ledgrofdebtor_m,'trans_type',['type_of_trans'])
	  //    		        ->setGroup(['trans_type','type_of_trans'])
	  //    		        ->setTitle('Ledger Info');
		return parent::recursiveRender();
	}
}