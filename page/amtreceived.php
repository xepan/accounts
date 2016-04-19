<?php
namespace xepan\accounts;
class page_amtreceived extends \Page {
	public $title="Account Receipt";
	function init(){
		parent::init();

		// // ==== Cash PAYMENT ===========

		$received_from_model = $this->add('xepan\accounts\Model_Ledger');

		$cash_ledgers = $this->add('xepan\accounts\Model_Ledger')->loadCashLedgers();
		// $cash_ledgers->filterCashLedger();

		$form = $this->add('Form_Stacked',null,'cash_view');
		$form->setLayout('view/form/payment-received-cash');

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validateNotNull(true);
		$cash_field->setModel($cash_ledgers);

		$cash_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger()->get('id'));

		$received_from_field = $form->addField('autocomplete/Basic','received_from')->validateNotNull(true);
		$received_from_field->setModel($received_from_model);

		$form->addField('Money','amount')->validateNotNull(true);
		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('CASH RECEIPT', null, $form['date'], $form['narration']);

			$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->load($form['cash_account']),$form['amount']);
			
			$transaction->addCreditLedger($this->add('xepan\accounts\Model_Ledger')->load($form['received_from']),$form['amount']);

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
		$form->addField('Money','from_amount')->validateNotNull(true);

		$from_curreny_field=$form->addField('Dropdown','from_currency')->validateNotNull(true);
		$from_curreny_field->setModel('xepan\accounts\Currency');
		$from_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('line','from_exchange_rate')->validateNotNull(true);

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);

		$bank_ledgers = $this->add('xepan\accounts\Model_Ledger')->loadBankLedgers();
		/*To Details*/
		$bank_field = $form->addField('autocomplete/Basic','to_bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_ledgers);
		$bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		$to_curreny_field = $form->addField('Dropdown','to_currency')->validateNotNull(true);
		$to_curreny_field->setModel('xepan\accounts\Currency');
		$to_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('Money','to_amount')->validateNotNull(true);
		$form->addField('line','to_exchange_rate')->validateNotNull(true);
		
		/*Different Charges*/
		for ($i=1; $i < 6; $i++) {
			$bank_field_1 = $form->addField('autocomplete/Basic','bank_account_charges_'.$i);//->validateNotNull(true);
			$bank_field_1->setModel('xepan\accounts\Model_Ledger')->filterBankCharges();
			$bank_field_1_charge_amount = $form->addField('Money','bank_charge_amount_'.$i);//->validateNotNull(true);
			$bank_field_1_currency = $form->addField('Dropdown','bank_currency_'.$i);//->validateNotNull(true);
			$bank_field_1_currency->setModel('xepan\accounts\Currency');
			$bank_field_1_exchange_rate = $form->addField('line','bank_exchange_rate_'.$i);

		}

		$form->addField('Text','narration');
		$form->addSubmit('Receive Now');

		if($form->isSubmitted()){
		

			//Customer account
			$from_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['received_from']);
			$from_currency = $this->add('xepan\accounts\Model_Currency')->load($form['from_currency']);

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->createNewTransaction('BANK RECEIPT', $related_document=false, $form['date'], $form['narration'], $from_currency, $form['from_exchange_rate'],$related_id=$form['received_from'],$related_type="xepan\accounts\Model_Ledger");

			
			$transaction->addCreditLedger($from_ledger,$form['from_amount'],$from_currency,$form['from_exchange_rate']);
			// echo "DR From Account: ".$from_ledger->id." :amount= ".$form['from_amount']." :Currency= ".$from_currency->id." :exchange Rate=".$form['from_exchange_rate']."<br/>";

			//one entry for to bank 
			$to_bank_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['to_bank_account']);
			$to_bank_currency = $this->add('xepan\accounts\Model_Currency')->load($form['to_currency']);

			$transaction->addDebitLedger($to_bank_ledger,$form['to_amount'],$to_bank_currency,$form['to_exchange_rate']);
			// echo "CR To Bank : ".$to_bank_ledger['name']." :amount= ".$form['to_amount']." :Currency= ".$to_bank_currency['name']." :exchange Rate=".$form['to_exchange_rate']."<br/>";

			//entry for to bank other charge
			for ($i=1; $i < 6; $i++) {
				
				$bank_field = "bank_account_charges_".$i;
				$amount_field = "bank_charge_amount_".$i;
				$currency_field = "bank_currency_".$i;
				$exchange_field = "bank_exchange_rate_".$i;

				if(!$form[$bank_field])
					continue;

				//TODO :: check for date, charge_amount, Currency, Exchange_rate

				$r=$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form[$bank_field]);
				$bank_other_charge_currency = $this->add('xepan\accounts\Model_Currency')->load($form[$currency_field]);

				$transaction->addDebitLedger($bank_other_charge_ledger,$form[$amount_field],$bank_other_charge_currency,$form[$exchange_field]);
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