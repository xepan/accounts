<?php
namespace xepan\accounts;
class page_amtreceived extends \Page {
	public $title="Account Receipt";
	function init(){
		parent::init();

		// ==== CASH PAYMENT ===========
		$received_from_model=$this->add('xepan\accounts\Model_Account');

		$cash_accounts = $this->add('xepan\accounts\Model_Account')->loadCashAccounts();

		$form = $this->add('Form_Stacked',null,'cash_view');
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

		$form = $this->add('Form_Stacked',null,'bank_view');
		$form->setLayout('view/form/payment-received-bank');
		
		/*Received From*/	
		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);
		$form->addField('Money','from_amount')->validateNotNull(true);

		$from_curreny_field=$form->addField('Dropdown','from_currency')->validateNotNull(true);
		$from_curreny_field->setModel('xepan\accounts\Currency');
		$from_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('line','from_exchange_rate')->validateNotNull(true);
		
		/*Invoices*/
		$invoice_field=$form->addField('Dropdown','invoice_no')->validateNotNull(true);
		$invoice_field->setModel('xepan\commerce\QSP_Master');
		$invoice_field=$form->addField('line','invoice_amount_1')->validateNotNull(true);
		
		$invoice_field_2=$form->addField('Dropdown','invoice_no_2')->validateNotNull(true);
		$invoice_field_2->setModel('xepan\commerce\QSP_Master');

		$invoice_field_2=$form->addField('line','invoice_amount_2')->validateNotNull(true);
		
		$invoice_field_3=$form->addField('Dropdown','invoice_no_3')->validateNotNull(true);
		$invoice_field_3->setModel('xepan\commerce\QSP_Master');
		$invoice_field_3=$form->addField('line','invoice_amount_3')->validateNotNull(true);
		$invoice_field_4=$form->addField('Dropdown','invoice_no_4')->validateNotNull(true);
		$invoice_field_4->setModel('xepan\commerce\QSP_Master');
		
		$invoice_field_4=$form->addField('line','invoice_amount_4')->validateNotNull(true);
		

		/*To Details*/
		$bank_field = $form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_accounts);
		$bank_field->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));
		$form->addField('DatePicker','to_date')->set($this->api->now)->validateNotNull(true);

		$to_curreny_field=$form->addField('Dropdown','to_currency')->validateNotNull(true);
		
		$to_curreny_field->setModel('xepan\accounts\Currency');
		$to_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('Money','to_amount')->validateNotNull(true);
		$form->addField('line','to_exchange_rate')->validateNotNull(true);
		
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		/*Different Charges*/
		$bank_field_1 = $form->addField('autocomplete/Basic','bank_account_1')->validateNotNull(true);
		$bank_field_1->setModel($bank_accounts);
		$bank_field_1->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));	
		$form->addField('DatePicker','c_date_1')->set($this->api->now)->validateNotNull(true);
		$form->addField('Money','c_amount_1')->validateNotNull(true);

		$bank_field_2 = $form->addField('autocomplete/Basic','bank_account_2')->validateNotNull(true);
		$bank_field_2->setModel($bank_accounts);
		$bank_field_2->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));	
		$form->addField('DatePicker','c_date_2')->set($this->api->now)->validateNotNull(true);
		$form->addField('Money','c_amount_2')->validateNotNull(true);

		$bank_field_3 = $form->addField('autocomplete/Basic','bank_account_3')->validateNotNull(true);
		$bank_field_3->setModel($bank_accounts);
		$bank_field_3->set($this->add('xepan\accounts\Model_Account')->loadDefaultBankAccount()->get('id'));	
		$form->addField('DatePicker','c_date_3')->set($this->api->now)->validateNotNull(true);
		$form->addField('Money','c_amount_3')->validateNotNull(true);

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK RECEIPT', null, $form['date'], $form['narration']);

			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Account')->load($form['bank_account']),$form['amount']);
			
			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Account')->load($form['received_from']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/amtrecevied'];
	}
}