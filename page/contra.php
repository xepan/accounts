<?php
namespace xepan\accounts;
class page_contra extends \Page {
	public $title="Account Cash & Bank" ;
	function init(){
		parent::init();


		// ============ CASH => BANK =================
		$cash_to_bank_form = $this->add('Form_Stacked',null,'bank_view');

		$bank_accounts = $this->add('xepan\accounts\Model_Ledger');
		$bank_accounts->loadBankLedgers();
		$cash_to_bank_form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$to_bank_field = $cash_to_bank_form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		$to_bank_field->setModel($bank_accounts);
		$to_bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		$cash_to_bank_form->addField('Money','amount_submitted')->validateNotNull(true);
		// $cash_to_bank_form->addField('Checkbox','allow_negative');
		$cash_to_bank_form->addField('Text','narration');

		for ($i=1; $i < 4; $i++) {
			$bank_field_1 = $cash_to_bank_form->addField('autocomplete/Basic','bank_account_charges_'.$i);
			$bank_field_1->setModel('xepan\accounts\Model_Ledger')->filterBankCharges();
			$bank_field_1_charge_amount = $cash_to_bank_form->addField('Money','bank_charge_amount_'.$i);
		}
		// $cash_to_bank_form->addField('Money','bank_charges')->setFieldHint('Any Charges due to cash submission or out city submission');
		
		$cash_to_bank_form->addSubmit('Execute');

		if($cash_to_bank_form->isSubmitted()){
			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH TO BANK', null , $cash_to_bank_form['date'], $Narration=null);

			$bank_ledger = $this->add('xepan\accounts\Model_Ledger')
							->load($cash_to_bank_form['to_bank_account']);

			$cash_ledger = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
			$transaction->addCreditLedger($cash_ledger,$cash_to_bank_form['amount_submitted']);

			$charges = 0;

			for ($i=1; $i < 4; $i++) {
				
				$bank_field = "bank_account_charges_".$i;
				$amount_field = "bank_charge_amount_".$i;
				
				if(!$cash_to_bank_form[$bank_field])
					continue;

				$charges +=  $cash_to_bank_form[$amount_field];
				$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($cash_to_bank_form[$bank_field]);
				$transaction->addDebitLedger($bank_other_charge_ledger,$cash_to_bank_form[$amount_field]);
				
			}
			
			$amount_submitted = $cash_to_bank_form['amount_submitted'];

			$transaction->addDebitLedger($bank_ledger,$amount_submitted - $charges);
			
			$transaction->execute();

			$cash_to_bank_form->js(null, $cash_to_bank_form->js()->reload())->univ()->successMessage('Done')->execute();
		}

		// ============ BANK => CASH =================

		$bank_to_cash_form = $this->add('Form_Stacked',null,'cash_view');

		$bank_accounts = $this->add('xepan\accounts\Model_Ledger');
		$bank_accounts->loadBankLedgers();
		$bank_to_cash_form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$from_bank_field = $bank_to_cash_form->addField('autocomplete/Basic','from_bank_account')->validateNotNull(true);
		$from_bank_field->setModel($bank_accounts);
		$from_bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		$bank_to_cash_form->addField('Money','amount_withdraw')->validateNotNull(true);
		// $cash_to_bank_form->addField('Checkbox','allow_negative');
		$bank_to_cash_form->addField('Text','narration');

		for ($i=1; $i < 4; $i++) {
			$bank_field_1 = $bank_to_cash_form->addField('autocomplete/Basic','bank_account_charges_'.$i);
			$bank_field_1->setModel('xepan\accounts\Model_Ledger')->filterBankCharges();
			$bank_field_1_charge_amount = $bank_to_cash_form->addField('Money','bank_charge_amount_'.$i);
		}
		
		// $bank_to_cash_form->addField('Money','bank_charges')->setFieldHint('Any Charges due to cash withdraw or out city withdraw');
		$bank_to_cash_form->addSubmit('Execute');

		if($bank_to_cash_form->isSUbmitted()){
			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH WITHDRAW', null , $transaction_date=$bank_to_cash_form['date'], $Narration=null);

			$bank_ledger = $this->add('xepan\accounts\Model_Ledger')->load($bank_to_cash_form['from_bank_account']);

			$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger(),$bank_to_cash_form['amount_withdraw']);

			$charges = 0;

			for ($i=1; $i < 4; $i++) {
				
				$bank_field = "bank_account_charges_".$i;
				$amount_field = "bank_charge_amount_".$i;
				
				if(!$bank_to_cash_form[$bank_field])
					continue;

				$charges +=  $bank_to_cash_form[$amount_field];

				$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($bank_to_cash_form[$bank_field]);
				$transaction->addDebitLedger($bank_other_charge_ledger,$bank_to_cash_form[$amount_field]);
				
			}
			
			$amount_credited = $bank_to_cash_form['amount_withdraw'];
			
			$transaction->addCreditLedger($bank_ledger,$amount_credited  + $charges);
			
			$transaction->execute();

			$bank_to_cash_form->js(null, $bank_to_cash_form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/contra'];
	}
}