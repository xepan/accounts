<?php
namespace xepan\accounts;
class page_amtpaid extends \Page {
	public $title="Account Payment";
	function init(){
		parent::init();

		// ==== CASH PAYMENT ===========
		$paid_to_model = $this->add('xepan\accounts\Model_Ledger');

		$form = $this->add('Form_Stacked',null,'cash_view');
		$form->setLayout('view/form/payment-paid-cash');

		$form->addField('DatePicker','date')->set($this->api->now)->validate('required');
		$cash_field = $form->addField('autocomplete/Basic','cash_account')->validate('required');
		$cash_field->setModel('xepan\accounts\Model_Ledger')->filterCashLedgers();;

		$default_cash_ledger = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
		$cash_field->set($default_cash_ledger->get('id'));

		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validate('required');
		$paid_to_field->setModel($paid_to_model);

		$form->addField('Money','amount')->validate('required');
		$form->addField('Text','narration');
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){
			
				$transaction = $this->add('xepan\accounts\Model_Transaction');
				$transaction->createNewTransaction('CASH PAYMENT', null, $form['date'], $form['narration']);

				$transaction->addCreditLedger($this->add('xepan\accounts\Model_Ledger')->load($form['cash_account']),$form['amount']);
				
				$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->load($form['paid_to']),$form['amount']);

				$transaction->execute();
			
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}



		// ==== BANK PAYMENT ===========
		$paid_to_model = $this->add('xepan\accounts\Model_Ledger');


		$form = $this->add('Form_Stacked',null,'bank_view');
		$form->setLayout('view/form/payment-paid-bank');
		
		/*Received From*/
		$paid_to_field = $form->addField('autocomplete/Basic','paid_to')->validateNotNull(true);
		$paid_to_field->setModel($paid_to_model);
		$form->addField('Money','to_amount')->validateNotNull(true);

		$to_curreny_field=$form->addField('Dropdown','to_currency')->validateNotNull(true);
		$to_curreny_field->setModel('xepan\accounts\Currency');
		$to_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('line','to_exchange_rate')->validateNotNull(true);

		$form->addField('DatePicker','date')->set($this->api->now)->validateNotNull(true);

		$bank_ledgers = $this->add('xepan\accounts\Model_Ledger')->filterBankLedgers();
		/*To Details*/
		$bank_field = $form->addField('autocomplete/Basic','from_bank_account')->validateNotNull(true);
		$bank_field->setModel($bank_ledgers);
		$bank_field->set($this->add('xepan\accounts\Model_Ledger')->loadDefaultBankLedger()->get('id'));

		$from_curreny_field = $form->addField('Dropdown','from_currency')->validateNotNull(true);
		$from_curreny_field->setModel('xepan\accounts\Currency');
		$from_curreny_field->set($this->app->epan->default_currency->id);
		$form->addField('Money','from_amount')->validateNotNull(true);
		$form->addField('line','from_exchange_rate')->validateNotNull(true);
		
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
		$form->addSubmit('Pay Now');

		if($form->isSubmitted()){
		
				//Customer account
				$to_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['paid_to']);
				$to_currency = $this->add('xepan\accounts\Model_Currency')->load($form['to_currency']);

				$transaction = $this->add('xepan\accounts\Model_Transaction');
				$transaction->createNewTransaction('BANK PAYMENT', $related_document=false, $form['date'], $form['narration'], $to_currency, $form['to_exchange_rate'],$related_id=$form['paid_to'],$related_type="xepan\accounts\Model_Ledger");

				
				$transaction->addDebitLedger($to_ledger,$form['to_amount'],$to_currency,$form['to_exchange_rate']);
				// echo "DR From Account: ".$from_ledger->id." :amount= ".$form['from_amount']." :Currency= ".$from_currency->id." :exchange Rate=".$form['from_exchange_rate']."<br/>";

				//one entry for to bank 
				$from_bank_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form['from_bank_account']);
				$from_bank_currency = $this->add('xepan\accounts\Model_Currency')->load($form['from_currency']);

				$transaction->addCreditLedger($from_bank_ledger,$form['from_amount'],$from_bank_currency,$form['from_exchange_rate']);
				// echo "CR To Bank : ".$to_bank_ledger['name']." :amount= ".$form['to_amount']." :Currency= ".$to_bank_currency['name']." :exchange Rate=".$form['to_exchange_rate']."<br/>";

				//entry for to bank other charge
				for ($i=1; $i < 6; $i++) {
					
					$bank_field = "bank_account_charges_".$i;
					$amount_field = "bank_charge_amount_".$i;
					$currency_field = "bank_currency_".$i;
					$exchange_field = "bank_exchange_rate_".$i;

					if(!$form[$bank_field])
						continue;

					
					$bank_other_charge_ledger = $this->add('xepan\accounts\Model_Ledger')->load($form[$bank_field]);
					$bank_other_charge_currency = $this->add('xepan\accounts\Model_Currency')->load($form[$currency_field]);

					$transaction->addCreditLedger($bank_other_charge_ledger,$form[$amount_field],$bank_other_charge_currency,$form[$exchange_field]);
					
				}
				
				$transaction->execute();
			
			
			$form->js(null,$form->js()->reload())->univ()->successMessage('Done')->execute();
		}

	}
	function defaultTemplate(){
		return ['page/amtpaid'];
	}
}