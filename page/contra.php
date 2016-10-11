<?php
namespace xepan\accounts;
class page_contra extends \xepan\base\Page {
	public $title="Account Cash & Bank" ;
	function init(){
		parent::init();

		// ============ CASH => BANK =================
		$cash_to_bank = $this->add('xepan\accounts\Model_EntryTemplate');
		$cash_to_bank->loadBy('unique_trnasaction_template_code','CASHDEPOSITINBANK');
		
		$cash_to_bank->addHook('afterExecute',function($cash_to_bank,$transaction,$total_amount){
			$cash_to_bank->form->js()->univ()->reload()->successMessage('Done')->execute();
		});

		$view_bank = $this->add('View',null,'bank_view');
		$cash_to_bank->manageForm($view_bank,null,null,null);

		// ============ BANK => CASH =================
		$bank_to_cash = $this->add('xepan\accounts\Model_EntryTemplate');
		$bank_to_cash->loadBy('unique_trnasaction_template_code','CASHWITHDRAWFROMBANK');
		
		$bank_to_cash->addHook('afterExecute',function($bank_to_cash,$transaction,$total_amount){
			$bank_to_cash->form->js()->univ()->reload()->successMessage('Done')->execute();
		});
		$view_cash = $this->add('View',null,'cash_view');
		$bank_to_cash->manageForm($view_cash,null,null,null);


		// ============ CASH => BANK =================
		// $cash_to_bank_form = $this->add('Form_Stacked',null,'bank_view');

		// $bank_accounts = $this->add('xepan\accounts\Model_Ledger');
		// $bank_accounts->filterBankLedgers();
		// $cash_to_bank_form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		// $to_bank_field = $cash_to_bank_form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		// $to_bank_field->setModel($bank_accounts);
		// $to_bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		// $cash_to_bank_form->addField('Money','amount_submitted')->validateNotNull(true);
		// // $cash_to_bank_form->addField('Checkbox','allow_negative');
		// $cash_to_bank_form->addField('Text','narration');

		// for ($i=1; $i < 4; $i++) {
		// 	$bank_field_1 = $cash_to_bank_form->addField('autocomplete/Basic','bank_account_charges_'.$i);
		// 	$bank_field_1->setModel('xepan\accounts\Model_Ledger')->filterBankCharges();
		// 	$bank_field_1_charge_amount = $cash_to_bank_form->addField('Money','bank_charge_amount_'.$i);
		// }
		// // $cash_to_bank_form->addField('Money','bank_charges')->setFieldHint('Any Charges due to cash submission or out city submission');
		
		// $cash_to_bank_form->addSubmit('Execute')->addClass('btn btn-primary');

		// if($cash_to_bank_form->isSubmitted()){
		// 	try{
		// 		$this->app->db->beginTransaction();
		// 		$transaction1 = $this->add('xepan\accounts\Model_Transaction');
		// 		$transaction1->createNewTransaction('CASH TO BANK', null , $cash_to_bank_form['date'], $cash_to_bank_form['narration']);

		// 		$bank_ledger = $this->add('xepan\accounts\Model_Ledger')
		// 						->load($cash_to_bank_form['to_bank_account']);

		// 		$cash_ledger = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
		// 		$transaction1->addCreditLedger($cash_ledger,$cash_to_bank_form['amount_submitted']);
		// 		$transaction1->addDebitLedger($bank_ledger,$cash_to_bank_form['amount_submitted']);
		// 		$transaction1->execute();

		// 		$transaction2 = $this->add('xepan\accounts\Model_Transaction');
		// 		$transaction2->createNewTransaction('BANK CHARGES', null , $cash_to_bank_form['date'], $cash_to_bank_form['narration']);
				
		// 		$charges = 0;

		// 		for ($i=1; $i < 4; $i++) {
					
		// 			$bank_field = "bank_account_charges_".$i;
		// 			$amount_field = "bank_charge_amount_".$i;
					
		// 			if(!$cash_to_bank_form[$bank_field])
		// 				continue;

		// 			$charges +=  $cash_to_bank_form[$amount_field];
		// 			$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($cash_to_bank_form[$bank_field]);
		// 			$transaction2->addDebitLedger($bank_other_charge_ledger,$cash_to_bank_form[$amount_field]);
					
		// 		}
		// 		$transaction2->addCreditLedger($bank_ledger,$charges);
		// 		if($charges>0)
		// 			$transaction2->execute();
		// 		$this->app->db->commit();
		// 	}catch(\Exception $e){
		// 		$this->app->db->rollback();
		// 		throw $e;
		// 	}

		// 	$cash_to_bank_form->js(null, $cash_to_bank_form->js()->reload())->univ()->successMessage('Done')->execute();
		// }

		// // ============ BANK => CASH =================

		// $bank_to_cash_form = $this->add('Form_Stacked',null,'cash_view');

		// $bank_to_cash_form->addField('DatePicker','date')->set($this->api->now)->validate('required');
		
		
		// $from_bank_field = $bank_to_cash_form->addField('autocomplete/Basic','from_bank_account')->validate('required');
		// $from_bank_field->setModel('xepan\accounts\Model_Ledger')->filterBankLedgers();

		// $from_bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		// $bank_to_cash_form->addField('Money','amount_withdraw')->validate('required|number|>0');
		// $bank_to_cash_form->addField('Text','narration');

		// for ($i=1; $i < 4; $i++) {
		// 	$bank_field_1 = $bank_to_cash_form->addField('autocomplete/Basic','bank_account_charges_'.$i);
		// 	$bank_field_1->setModel('xepan\accounts\Model_Ledger')->filterBankCharges();
		// 	$bank_field_1_charge_amount = $bank_to_cash_form->addField('Money','bank_charge_amount_'.$i);
		// }
		
		// $bank_to_cash_form->addSubmit('Execute')->addClass('btn btn-primary');

		// if($bank_to_cash_form->isSUbmitted()){
		// 	try{	
		// 		$transaction1 = $this->add('xepan\accounts\Model_Transaction');
		// 		$transaction1->createNewTransaction('CASH WITHDRAW', null , $bank_to_cash_form['date'], $bank_to_cash_form['narration']);

		// 		$bank_ledger = $this->add('xepan\accounts\Model_Ledger')->load($bank_to_cash_form['from_bank_account']);
		// 		$default_cash_ledger = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();

		// 		$transaction2 = $this->add('xepan\accounts\Model_Transaction');
		// 		$transaction2->createNewTransaction('BANK CHARGES', null , $bank_to_cash_form['date'], $bank_to_cash_form['narration']);
				
		// 		$charges = 0;
		// 		for ($i=1; $i < 4; $i++) {

		// 			$bank_charge_field = "bank_account_charges_".$i;
		// 			$amount_field = "bank_charge_amount_".$i;
					
		// 			if(!$bank_to_cash_form[$bank_charge_field])
		// 				continue;

		// 			$charges +=  $bank_to_cash_form[$amount_field];

		// 			$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($bank_to_cash_form[$bank_charge_field]);
		// 			$transaction2->addDebitLedger($bank_other_charge_ledger,$bank_to_cash_form[$amount_field]);
					
		// 		}
				
		// 		$transaction1->addDebitLedger($default_cash_ledger,$bank_to_cash_form['amount_withdraw'] - $charges);
		// 		$transaction1->addCreditLedger($bank_ledger,$bank_to_cash_form['amount_withdraw']  - $charges);
		// 		$transaction2->addCreditLedger($bank_ledger,$charges);
				
		// 		$transaction1->execute();
		// 		if($charges > 0)
		// 			$transaction2->execute();
		// 			$transaction2->execute();
		// 		$this->app->db->commit();
		// 	}catch(\Exception $e){
		// 		$this->app->db->rollback();
		// 		throw $e;
		// 	}

		// 	$bank_to_cash_form->js(null, $bank_to_cash_form->js()->reload())->univ()->successMessage('Done')->execute();
		// }

	}
	function defaultTemplate(){
		return ['page/contra'];
	}
}