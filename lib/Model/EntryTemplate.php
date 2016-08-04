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

				$field = $form->addField($field_type,['name'=>'ledger_'.$row->id,'hint'=>'Select Ledger'], $row['title'],null,$spot.'_ledger_'.$row->id);
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
					$exchange_rate = $form->addField('line','to_exchange_rate_'.$row->id,'Currency Rate',null,$spot.'_exchange_rate_'.$row->id)->validateNotNull(true)->addClass('exchange-rate');
				}
				$field = $form->addField('line','amount_'.$row->id,'Amount',null,$spot.'_amount_'.$row->id);
			}
		}

		$form->addSubmit('DO')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			foreach ($transactions as $trans) {
				$transaction = $this->add('xepan\accounts\Model_Transaction');
				$transaction->createNewTransaction($trans['type'], null, $form['date'], $form['narration'],null,null,$related_id, $related_type);

				foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
					$currency=null;
					$exchange_rate = null;
					if($row['is_include_currency']){
						$currency = $this->add('xepan\accounts\Model_Currency')->load($form['bank_currency_'.$row->id]);
						$exchange_rate = $form['to_exchange_rate_'.$row->id];
					}

					if($row['side']=='Cr')
						$transaction->addCreditLedger($this->add('xepan\accounts\Model_Ledger')->load($form['ledger_'.$row->id]),$form['amount_'.$row->id],$currency,$exchange_rate);
					else
						$transaction->addDebitLedger($this->add('xepan\accounts\Model_Ledger')->load($form['ledger_'.$row->id]),$form['amount_'.$row->id],$currency,$exchange_rate);
				}
				$transaction->execute();
				echo "done";
			}	

			$form->js()->reload()->univ()->successMessage('Done')->execute();		
		}

	}

	// As per given rules of this template, ie groups accounts etc.
	function verifyData($data){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}

	function execute($data=[]){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}

	function exportJson(){
		$data = $this->get();
		unset($data['id']);

		$data['transactions']=[];
		foreach ($this->ref('xepan\accounts\EntryTemplateTransaction') as $transaction) {
			$transaction_data = $transaction->get();
			unset($transaction_data['id']);
			unset($transaction_data['template_id']);
			unset($transaction_data['template']);

			$transaction_data['rows']=[];
			foreach ($transaction->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
				$row_data = $row->get();
				unset($row_data['id']);
				unset($row_data['template_transaction_id']);
				unset($row_data['template_transaction_id']);
				$transaction_data['rows'][] = $row_data;
			}

			$data['transactions'][] = $transaction_data;
		}
		return json_encode($data);
	}

	function importJson($json){
		$data=json_decode($json,true);
		// echo "<pre>";
		// // print_r($data['transactions'][0]['rows']);
		// print_r($data['transactions'][0]);
		// echo "</pre>";
		$temp=$this->add('xepan\accounts\Model_EntryTemplate');
		$temp['name']=$data['name'];
		$temp['detail']=$data['detail'];
		$temp['unique_trnasaction_template_code']=$data['unique_trnasaction_template_code'];
		$temp['is_system_default']=$data['is_system_default'];
		$temp['is_favourite_menu_lister']=$data['is_favourite_menu_lister'];
		$temp['is_merge_transaction']=$data['is_merge_transaction'];
		$temp->save();

		foreach ($data['transactions'] as $tr) {
			$transaction=$this->add('xepan\accounts\Model_EntryTemplateTransaction');
			$transaction['template_id']=$temp->id;
			$transaction['name']=$tr['name'];
			$transaction['type']=$tr['type'];
			$transaction->save();

			foreach ($tr['rows'] as  $tr_row) {
				$row=$this->add('xepan\accounts\Model_EntryTemplateTransactionRow');
				$row['template_transaction_id']=$transaction->id;
				$row['title']=$tr_row['title'];
				$row['side']=$tr_row['side'];
				$row['group']=$tr_row['group'];
				$row['balance_sheet']=$tr_row['balance_sheet'];
				$row['parent_group']=$tr_row['parent_group'];
				$row['ledger']=$tr_row['ledger'];
				$row['ledger_type']=$tr_row['ledger_type'];
				$row['is_ledger_changable']=$tr_row['is_ledger_changable'];
				$row['is_allow_add_ledger']=$tr_row['is_allow_add_ledger'];
				$row['is_include_currency']=$tr_row['is_include_currency'];
				$row->save();
			}
		}


	}
}