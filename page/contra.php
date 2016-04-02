<?php
namespace xepan\accounts;
class page_contra extends \Page {
	public $title="Account Cash & Bank" ;
	function init(){
		parent::init();


		// ============ CASH => BANK =================
		$cash_to_bank_form = $this->add('Form_Stacked',null,'bank_view');

		$bank_accounts = $this->add('xepan\accounts\Model_Ledger');
		$bank_accounts->loadBankAccounts();
		$cash_to_bank_form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$to_bank_field = $cash_to_bank_form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		$to_bank_field->setModel($bank_accounts);
		$to_bank_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));

		$cash_to_bank_form->addField('Money','amount_submitted')->validateNotNull(true);
		// $cash_to_bank_form->addField('Checkbox','allow_negative');
		$cash_to_bank_form->addField('Text','narration');

		$cash_to_bank_form->addField('Money','bank_charges')->setFieldHint('Any Charges due to cash submission or out city submission');
		$cash_to_bank_form->addSubmit('Execute');

		if($cash_to_bank_form->isSUbmitted()){
			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH TO BANK', null , $cash_to_bank_form['date'], $Narration=null);

			$bank_account_model = $this->add('xepan\accounts\Model_Account')->load($cash_to_bank_form['to_bank_account']);

			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->loadDefaultCashAccount(),$cash_to_bank_form['amount_submitted']);

			$amount_submitted = $cash_to_bank_form['amount_submitted'];
			if($cash_to_bank_form['bank_charges']){
				$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->loadDefaultBankChargesAccount(),$cash_to_bank_form['bank_charges']);
				$amount_submitted = $cash_to_bank_form['amount_submitted'] - $cash_to_bank_form['bank_charges'];
			}
			
			$transaction->addDebitAccount($bank_account_model,$amount_submitted);

			$transaction->execute();

			$cash_to_bank_form->js(null, $cash_to_bank_form->js()->reload())->univ()->successMessage('Done')->execute();
		}

		// ============ BANK => CASH =================

		$bank_to_cash_form = $this->add('Form_Stacked',null,'cash_view');

		$bank_accounts = $this->add('xepan\accounts\Model_Account');
		$bank_accounts->loadBankAccounts();
		$bank_to_cash_form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$from_bank_field = $bank_to_cash_form->addField('autocomplete/Basic','from_bank_account')->validateNotNull(true);
		$from_bank_field->setModel($bank_accounts);
		$from_bank_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));

		$bank_to_cash_form->addField('Money','amount_withdraw')->validateNotNull(true);
		// $cash_to_bank_form->addField('Checkbox','allow_negative');
		$bank_to_cash_form->addField('Text','narration');

		$bank_to_cash_form->addField('Money','bank_charges')->setFieldHint('Any Charges due to cash withdraw or out city withdraw');
		$bank_to_cash_form->addSubmit('Execute');

		if($bank_to_cash_form->isSUbmitted()){
			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH WITHDRAW', null , $transaction_date=$bank_to_cash_form['date'], $Narration=null);

			$bank_account_model = $this->add('xepan\accounts\Model_Account')->load($bank_to_cash_form['from_bank_account']);

			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->loadDefaultCashAccount(),$bank_to_cash_form['amount_withdraw']);

			$amount_credited = $bank_to_cash_form['amount_withdraw'];
			if($bank_to_cash_form['bank_charges']){
				$amount_credited = $bank_to_cash_form['amount_withdraw'] + $bank_to_cash_form['bank_charges'];
			}
			
			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->loadDefaultBankChargesAccount(),$bank_to_cash_form['bank_charges']);
			$transaction->addCreditAccount($bank_account_model,$amount_credited);

			$transaction->execute();

			$bank_to_cash_form->js(null, $bank_to_cash_form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/contra'];
	}
}