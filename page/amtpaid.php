<?php
namespace xepan\accounts;
class page_amtpaid extends \Page {
	public $title="Account Payment";
	function init(){
		parent::init();

		// ==== CASH PAYMENT ===========
		$paid_to_model=$this->add('xepan\accounts\Model_Ledger');

		$cash_accounts = $this->add('xepan\accounts\Model_Ledger')->loadCashLedgers();

		$form = $this->add('Form_Stacked',null,'cash_view');
		$form->setLayout('view/form/payment-paid-cash');
		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validateNotNull(true);
		$cash_field->setModel($cash_accounts);

		$cash_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger()->get('id'));

		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validateNotNull(true);
		$paid_to_field->setModel($paid_to_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH PAYMENT', null, $form['date'], $form['narration']);

			$transaction->addCreditLedger($this->add('xepan\accounts\Model_Account')->load($form['cash_account']),$form['amount']);
			
			$transaction->addDebitLedger($this->add('xepan\accounts\Model_Account')->load($form['paid_to']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}



		// ==== BANK PAYMENT ===========
		$paid_to_model=$this->add('xepan\accounts\Model_Ledger');

		$bank_accounts = $this->add('xepan\accounts\Model_Ledger')->loadBankLedgers();

		$form = $this->add('Form_Stacked',null,'bank_view');
		$form->setLayout('view/form/payment-paid-bank');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$bank_field = $form->addField('autocomplete/Basic','bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_accounts);

		$bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validateNotNull(true);
		$paid_to_field->setModel($paid_to_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK PAYMENT', null, $form['date'], $form['narration']);

			$transaction->addCreditLedger($this->add('xepan\accounts\Model_Ledger')->load($form['bank_account']),$form['amount']);
			
			$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->load($form['paid_to']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/amtpaid'];
	}
}