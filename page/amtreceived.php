<?php
namespace xepan\accounts;
class page_amtreceived extends \Page {
	public $title="Account Receipt";
	function init(){
		parent::init();

		// // ==== Cash PAYMENT ===========

		$received_from_model = $this->add('xepan\accounts\Model_Ledger');

		$cash_accounts = $this->add('xepan\accounts\Model_Ledger')->loadCashAccounts();

		$form = $this->add('Form_Stacked',null,'cash_view');
		$form->setLayout('view/form/payment-received-cash');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validateNotNull(true);
		$cash_field->setModel($cash_accounts);

		$cash_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultCashAccount()->get('id'));

		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH RECEIPT', null, $form['date'], $form['narration']);

			$transaction->addDebitAccount($this->add('xepan\accounts\Model_Ledger')->load($form['cash_account']),$form['amount']);
			
			$transaction->addCreditAccount($this->add('xepan\accounts\Model_Ledger')->load($form['received_from']),$form['amount']);

			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}



		// ==== BANK PAYMENT ===========
		$received_from_model = $this->add('xepan\accounts\Model_Ledger');


		$form = $this->add('Form_Stacked',null,'bank_view');
		$form->setLayout('view/form/payment-received-bank');
		
		/*Received From*/
		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);
		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$form->addField('Money','from_amount')->validateNotNull(true);

		$from_curreny_field=$form->addField('Dropdown','from_currency')->validateNotNull(true);
		$from_curreny_field->setModel('xepan\accounts\Currency');
		$from_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('line','from_exchange_rate')->validateNotNull(true);


		$bank_accounts = $this->add('xepan\accounts\Model_Ledger')->loadBankAccounts();
		/*To Details*/
		$bank_field = $form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_accounts);
		$bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));
		$form->addField('DatePicker','to_date')->set($this->api->now)->validateNotNull(true);

		$to_curreny_field = $form->addField('Dropdown','to_currency')->validateNotNull(true);
		$to_curreny_field->setModel('xepan\accounts\Currency');
		$to_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('Money','to_amount')->validateNotNull(true);
		$form->addField('line','to_exchange_rate')->validateNotNull(true);
		
		/*Different Charges*/
		$bank_field_1 = $form->addField('autocomplete/Basic','bank_account_1');//->validateNotNull(true);
		$bank_field_1->setModel($bank_accounts);
		$bank_field_1->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));
		$bank_field_1_charge_date = $form->addField('DatePicker','bank_charge_date_1')->set($this->api->now);//->validateNotNull(true);
		$bank_field_1_charge_amount = $form->addField('Money','bank_charge_amount_1');//->validateNotNull(true);
		$bank_field_1_currency = $form->addField('Dropdown','bank_currency_1');//->validateNotNull(true);
		$bank_field_1_currency->setModel('xepan\accounts\Currency');
		$bank_field_1_exchange_rate = $form->addField('line','bank_exchange_rate_1');

		$bank_field_2 = $form->addField('autocomplete/Basic','bank_account_2');//->validateNotNull(true);
		$bank_field_2->setModel($bank_accounts);
		$bank_field_2->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));	
		$bank_field_2_charge_date = $form->addField('DatePicker','bank_charge_date_2')->set($this->api->now);//->validateNotNull(true);
		$bank_field_2_charge_amount = $form->addField('Money','bank_charge_amount_2');//->validateNotNull(true);
		$bank_field_2_currency = $form->addField('Dropdown','bank_currency_2');//->validateNotNull(true);
		$bank_field_2_currency->setModel('xepan\accounts\Currency');
		$bank_field_2_exchange_rate = $form->addField('line','bank_exchange_rate_2');

		$bank_field_3 = $form->addField('autocomplete/Basic','bank_account_3');//->validateNotNull(true);
		$bank_field_3->setModel($bank_accounts);
		$bank_field_3->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));	
		$bank_field_3_charge_date = $form->addField('DatePicker','bank_charge_date_3')->set($this->api->now);//->validateNotNull(true);
		$bank_field_3_charge_amount = $form->addField('Money','bank_charge_amount_3');//->validateNotNull(true);
		$bank_field_3_currency = $form->addField('Dropdown','bank_currency_3');//->validateNotNull(true);
		$bank_field_3_currency->setModel('xepan\accounts\Currency');
		$bank_field_3_exchange_rate = $form->addField('line','bank_exchange_rate_3');

		$bank_field_4 = $form->addField('autocomplete/Basic','bank_account_4');//->validateNotNull(true);
		$bank_field_4->setModel($bank_accounts);
		$bank_field_4->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));	
		$bank_field_4_charge_date = $form->addField('DatePicker','bank_charge_date_4')->set($this->api->now);//->validateNotNull(true);
		$bank_field_4_charge_amount = $form->addField('Money','bank_charge_amount_4');//->validateNotNull(true);
		$bank_field_4_currency = $form->addField('Dropdown','bank_currency_4');//->validateNotNull(true);
		$bank_field_4_currency->setModel('xepan\accounts\Currency');
		$bank_field_4_exchange_rate = $form->addField('line','bank_exchange_rate_4');

		$bank_field_5 = $form->addField('autocomplete/Basic','bank_account_5');//->validateNotNull(true);
		$bank_field_5->setModel($bank_accounts);
		$bank_field_5->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankAccount()->get('id'));
		$bank_field_5_charge_date = $form->addField('DatePicker','bank_charge_date_5')->set($this->api->now);//->validateNotNull(true);
		$bank_field_5_charge_amount = $form->addField('Money','bank_charge_amount_5');//->validateNotNull(true);
		$bank_field_5_currency = $form->addField('Dropdown','bank_currency_5');//->validateNotNull(true);
		$bank_field_5_currency->setModel('xepan\accounts\Currency');
		$bank_field_5_exchange_rate = $form->addField('line','bank_exchange_rate_5');

		
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){
			
			//from customer
			//1// amount with currency and exchange rate
				// CR (customer amount deposite the money) transaction
			//2//
				//Dr in To Bank Account with 
				//Dr in Bank Other change 1 
				//Dr in Bank Other change 2 
				//Dr in Bank Other change 3 

			// 3 or 4 not come here because accounts don't know invoices
			//3// it's just for managing how many and which one invoice are paid using this payment received transaction using invoive transaction association we calculate the you are in profit or loss
				//marke one or more invoice complete
				//invoice currency must be same as currency invoice send
				//each invoice has check box about adjust profit/loss amount
			//4//
				//association entry in invoice_transaction_association_table


			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK RECEIPT', $related_document=false, $form['date'], $form['narration'], $Currency=null, $exchange_rate=1.00,$related_id=$form['received_from'],$related_type="xepan\accounts\Model_Ledger");

			//Customer account
			$from_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['received_from']);
			$from_currency = $this->add('xepan\accounts\Model_Currency')->load($form['from_currency']);
			
			$transaction->addDebitAccount($from_ledger,$form['from_amount'],$from_currency,$form['from_exchange_rate']);
			// echo "DR From Account: ".$from_ledger->id." :amount= ".$form['from_amount']." :Currency= ".$from_currency->id." :exchange Rate=".$form['from_exchange_rate']."<br/>";

			//one entry for to bank 
			$to_bank_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['to_bank_account']);
			$to_bank_currency = $this->add('xepan\accounts\Model_Currency')->load($form['to_currency']);

			$transaction->addCreditAccount($to_bank_ledger,$form['to_amount'],$to_bank_currency,$form['to_exchange_rate']);
			// echo "CR To Bank : ".$to_bank_ledger['name']." :amount= ".$form['to_amount']." :Currency= ".$to_bank_currency['name']." :exchange Rate=".$form['to_exchange_rate']."<br/>";

			//entry for to bank other charge
			for ($i=1; $i < 6; $i++) {
				$bank_field = "bank_account_".$i;
				$date_field = "bank_charge_date_".$i;
				$amount_field = "bank_charge_amount_".$i;
				$currency_field = "bank_currency_".$i;
				$exchange_field = "bank_exchange_rate_".$i;

				if(!$form[$bank_field])
					continue;

				//TODO :: check for date, charge_amount, Currency, Exchange_rate

				$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form[$bank_field]);
				$bank_other_charge_currency = $this->add('xepan\accounts\Model_Currency')->load($form[$currency_field]);

				$transaction->addCreditAccount($bank_other_charge_ledger,$form[$amount_field],$bank_other_charge_currency,$form[$exchange_field]);
				// echo "CR To Bank Other Charge: ".$bank_other_charge_ledger['name']." :amount= ".$form[$amount_field]." :Currency= ".$bank_other_charge_currency['name']." :exchange Rate=".$form[$exchange_field]."<br/>";

			}
			
			$transaction->execute();
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/amtrecevied'];
	}
}