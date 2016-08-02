<?php

namespace xepan\accounts;

class Model_EntryTemplate extends \xepan\base\Model_Table{
	public $table= "custom_account_entries_templates";
	public $acl=false;

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('detail');
		$this->addField('is_favourite_menu_lister')->type('boolean');
		$this->addField('is_merge_transaction')->type('boolean');
		$this->hasMany('xepan\accounts\EntryTemplateTransaction','template_id');
	}

	function manageForm($page){
		$transactions = $this->ref('xepan\accounts\EntryTemplateTransaction');

		$form = $page->add('xepan\accounts\Form_EntryRunner',null,null,['form/empty']);
		$form->setLayout(['view/form/accounttransactionexecuter']);

		$form->addField('DatePicker','date');
		
		foreach ($transactions as $trans) {
			foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {

				if($row['is_allow_add_ledger'])
					$field_type= 'xepan\base\Plus';
				else
					$field_type= 'autocomplete\Basic';
				
				$spot = $row['side']=='Dr'?'left':'right';			

				$field = $form->addField($field_type,'ledger_'.$row->id, $row['title'],null,$spot);
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
					$form_currency = $form->addField('Dropdown','bank_currency_'.$row->id,'Currency Name',null,$spot."_currency");
					$form_currency->setModel('xepan\accounts\Currency');
					$exchange_rate = $form->addField('line','to_exchange_rate'.$row->id,'Currency Rate',null,$spot."_exchange_rate")->validateNotNull(true)->addClass('exchange-rate');
				}
				$form->addField('line','amount_'.$row->id,'Amount',null,$spot."_amount");
			}
		}

		$form->addSubmit('DO');

		if($form->isSubmitted()){
			foreach ($transactions as $trans) {
				$transaction = $this->add('xepan\accounts\Model_Transaction');
				if($form_currency){
					$currency = $this->add('xepan\accounts\Model_Currency')->load($form['bank_currency_'.$row->id]);
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