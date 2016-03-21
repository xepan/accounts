<?php
namespace xepan\accounts;
class page_amtpaid extends \Page {
	public $title="Account Payment";
	function init(){
		parent::init();

		$tabs= $this->add('Tabs');
		$cash_tab = $tabs->addTab('Cash Payment');
		$bank_tab = $tabs->addTab('Bank Payment');

		// ==== CASH PAYMENT ===========
		$paid_to_model=$this->add('xepan\accounts\Model_Account');

		$cash_accounts = $this->add('xepan\accounts\Model_Account')->loadCashAccounts();

		$form = $cash_tab->add('Form_Stacked');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validateNotNull(true);
		$cash_field->setModel($cash_accounts);

		$cash_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultCashAccount()->get('id'));

		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validateNotNull(true);
		$paid_to_field->setModel($paid_to_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH PAYMENT', null, $form['date'], $form['narration']);

			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->load($form['cash_account']),$form['amount']);
			
			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->load($form['paid_to']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}



		// ==== BANK PAYMENT ===========
		$paid_to_model=$this->add('xepan\accounts\Model_Account');

		$bank_accounts = $this->add('xepan\accounts\Model_Account')->loadBankAccounts();

		$form = $bank_tab->add('Form_Stacked');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$bank_field = $form->addField('autocomplete/Basic','bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_accounts);

		$bank_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));

		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validateNotNull(true);
		$paid_to_field->setModel($paid_to_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK PAYMENT', null, $form['date'], $form['narration']);

			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->load($form['bank_account']),$form['amount']);
			
			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->load($form['paid_to']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
}