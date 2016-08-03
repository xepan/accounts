<?php

namespace xepan\accounts;

class Model_EntryTemplate extends \xepan\base\Model_Table{
	public $table= "custom_account_entries_templates";
	public $acl=true;
	public $acl_type = 'AccountEntryTemplate';

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('detail');
		$this->addField('unique_trnasaction_template_code')->PlaceHolder('If it is default for system, Insert Unique Default Template Transaction Code')->caption('Code')->hint('Place your unique template transaction code ');
		$this->addField('is_system_default')->type('boolean');
		$this->addField('is_favourite_menu_lister')->type('boolean');
		$this->addField('is_merge_transaction')->type('boolean');
		$this->hasMany('xepan\accounts\EntryTemplateTransaction','template_id');
	}

	function manageForm($page, $related_id=null, $related_type=null){
		$transactions = $this->ref('xepan\accounts\EntryTemplateTransaction');

		$template = $this->add('GiTemplate');
		$template->loadTemplate('view/form/entrytransaction');
		$template->trySetHTML('date','{$date}');
		

		foreach ($transactions as $trans) {
			$transaction_template = $this->add('GiTemplate');
			$transaction_template->loadTemplate('view/form/entrytransactionsides');

			$transaction_template->trySetHTML('transaction_name','{$transaction_name_'.$trans->id.'}');

			foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
				if($row['side']=="Dr"){
					$row_left_template = $this->add('GiTemplate');
					$row_left_template->loadTemplate('view/form/entrytransactionsiderows');
					$row_left_template->trySetHTML('ledger','{$left_ledger_'.$row->id.'}');
					$row_left_template->trySetHTML('amount','{$left_amount_'.$row->id.'}');
					$row_left_template->trySetHTML('currency','{$left_currency_'.$row->id.'}');
					$row_left_template->trySetHTML('exchange_rate','{$left_exchange_rate_'.$row->id.'}');
					$transaction_template->appendHTML('transaction_row_left',$row_left_template->render());
					
				}else{
					$row_right_template = $this->add('GiTemplate');
					$row_right_template->loadTemplate('view/form/entrytransactionsiderows');
					$row_right_template->trySetHTML('ledger','{$right_ledger_'.$row->id.'}');
					$row_right_template->trySetHTML('amount','{$right_amount_'.$row->id.'}');
					$row_right_template->trySetHTML('currency','{$right_currency_'.$row->id.'}');
					$row_right_template->trySetHTML('exchange_rate','{$right_exchange_rate_'.$row->id.'}');
					$transaction_template->appendHTML('transaction_row_right',$row_right_template->render());
				}	
				
			}
			$template->appendHTML('transactions',$transaction_template->render());

		}

		// echo (htmlentities($template->render()));
		// exit;

		$template->loadTemplateFromString($template->render());

		$form = $page->add('xepan\accounts\Form_EntryRunner',null,null,['form/stacked']);
		$form->setLayout($template);

		$form->addField('DatePicker','date');
		
		foreach ($transactions as $trans) {
			$form->layout->add('View',null,'transaction_name_'.$trans->id)->set($trans['name']);
			foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {

				if($row['is_allow_add_ledger'])
					$field_type= 'xepan\base\Plus';
				else
					$field_type= 'autocomplete\Basic';
				
				$spot = $row['side']=='Dr'?'left':'right';			

				$field = $form->addField($field_type,'ledger_'.$row->id, $row['title'],null,$spot.'_ledger_'.$row->id);
				$field->show_fields= ['name'];

				$row_ledger_present = $row['ledger']?true:false;
				$row_ledger=null;
				if($row_ledger_present){
					$row_ledger = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('name',$row['ledger']);
				}

				$row_group_present = $row['group']?true:false;
				if($row_group_present){
					$row_group = $this->add('xepan\accounts\Model_Group')->tryLoadBy('name',$row['group']);
				}else{
					$row_group = $row_ledger->ref('group_id');
				}

				$ledger = $this->add('xepan\accounts\Model_Ledger');

				// if($row['is_include_subgroup_ledger_account']){
				// 	$ledger->addCondition('root_group_id',$row_group['root_group_id']);
				// }else{
					$ledger->addCondition('group_id',$row_group->id);
				// }

				if(!$row['is_ledger_changable']){
					$ledger->addCondition('name',$row['ledger']);

					if($row_ledger_present)
						$field->set($row_ledger->id);
				}

				$field->setModel($ledger);
				if($row['is_include_currency']){
					$form_currency = $form->addField('Dropdown','bank_currency_'.$row->id,'Currency Name',null,$spot.'_currency_'.$row->id);
					$form_currency->setModel('xepan\accounts\Currency');
					$exchange_rate = $form->addField('line','to_exchange_rate'.$row->id,'Currency Rate',null,$spot.'_exchange_rate_'.$row->id)->validateNotNull(true)->addClass('exchange-rate');
				}
				$field = $form->addField('line','amount_'.$row->id,'Amount',null,$spot.'_amount_'.$row->id);
			}
		}

		$form->addSubmit('DO');

		if($form->isSubmitted()){
			foreach ($transactions as $trans) {
				$transaction = $this->add('xepan\accounts\Model_Transaction');
				if($form_currency){
					$currency = $this->add('xepan\accounts\Model_Currency')->tryLoad($form['bank_currency_'.$row->id]);
					$transaction->createNewTransaction($trans['type'], null, $form['date'], $form['narration'],$currency,$exchange_rate);
				}else{
					$transaction->createNewTransaction($trans['type'], null, $form['date'], $form['narration']);
				}
				foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
					if($row['side']=='Cr')
						$transaction->addCreditLedger($this->add('xepan\accounts\Model_Ledger')->load($form['ledger_'.$row->id]),$form['amount_'.$row->id]);
					else
						$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->load($form['ledger_'.$row->id]),$form['amount_'.$row->id]);
				}
				$transaction->execute();
			}	

			$form->js()->reload()->univ()->successMessage('Done')->execute();		
		}

	}

	// As per given rules of this template, ie groups accounts etc.
	function verifyData($data){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}

	function execute($data=[]){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}
}