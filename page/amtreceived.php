<?php
namespace xepan\accounts;
class page_amtreceived extends \Page {
	public $title="Account Receipt";
	function init(){
		parent::init();
		$tabs= $this->add('Tabs');
		$cash_tab = $tabs->addTab('Cash Received');
		$bank_tab = $tabs->addTab('Bank Received');

		// ==== CASH PAYMENT ===========
		$received_from_model=$this->add('xepan\accounts\Model_Account');

		$cash_accounts = $this->add('xepan\accounts\Model_Account')->loadCashAccounts();

		$form = $cash_tab->add('Form_Stacked');
		$form->setLayout('view/form/payment-received-cash');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validateNotNull(true);
		$cash_field->setModel($cash_accounts);

		$cash_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultCashAccount()->get('id'));

		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH RECEIPT', null, $form['date'], $form['narration']);

			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->load($form['cash_account']),$form['amount']);
			
			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->load($form['received_from']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}



		// ==== BANK PAYMENT ===========
		$received_from_model=$this->add('xepan\accounts\Model_Account');

		$bank_accounts = $this->add('xepan\accounts\Model_Account')->loadBankAccounts();

		$form = $bank_tab->add('Form_Stacked');
		$form->setLayout('view/form/payment-received-bank');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$bank_field = $form->addField('autocomplete/Basic','bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_accounts);

		$bank_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));

		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);

		$form->addField('Money','amount')->validateNotNull(true);
		
		$invoice_field=$form->addField('Dropdown','invoice_no')->validateNotNull(true);
		$invoice_field->setModel('xepan\commerce\Model_QSP_Master');
		$invoice_field_2=$form->addField('Dropdown','invoice_no_2')->validateNotNull(true);
		$invoice_field_2->setModel('xepan\commerce\Model_QSP_Master');
		$invoice_field_3=$form->addField('Dropdown','invoice_no_3')->validateNotNull(true);
		$invoice_field_3->setModel('xepan\commerce\Model_QSP_Master');
		$invoice_field_4=$form->addField('Dropdown','invoice_no_4')->validateNotNull(true);
		$invoice_field_4->setModel('xepan\commerce\Model_QSP_Master');
		
		$curreny_field=$form->addField('Dropdown','currency')->validateNotNull(true);
		$curreny_field->setModel('xepan\commerce\Model_Currency');
		$form->addField('line','exchange_rate')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK RECEIPT', null, $form['date'], $form['narration']);

			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->load($form['bank_account']),$form['amount']);
			
			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->load($form['received_from']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
}