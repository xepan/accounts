<?php


namespace xepan\accounts;


class page_custom_accountentries extends \xepan\base\Page {

	function page_index(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');

		$crud = $this->add('CRUD');
		$crud->setModel($entry_template_m,['name','detail'],['name','detail']);

		if(!$crud->isEditing()){
			$crud->grid->addColumn('expander','transactions');
		}
	}

	function page_transactions(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$entry_template_m->load($this->app->stickyGET('custom_account_entries_templates_id'));

		$temp_tansaction=$entry_template_m->ref('xepan\accounts\EntryTemplateTransaction');

		$crud=$this->add('CRUD');
		$crud->setModel($temp_tansaction);

		if(!$crud->isEditing()){
			$crud->grid->addColumn('expander','rows');
		}	

	}

	function page_transactions_rows(){
		$transaction_id = $this->api->stickyGET('custom_account_entries_templates_transactions_id');
		$transaction = $this->add('xepan\accounts\Model_EntryTemplateTransaction');
		$transaction->load($transaction_id);

		$rows = $transaction->ref('xepan\accounts\EntryTemplateTransactionRow');

		$crud = $this->add('CRUD');

		$crud->setModel($rows);
		if($crud->isEditing()){
			$form=$crud->form;
			$grp_fld = $form->getElement('group');
			$grp_fld->select_menu_options=['tags'=>true];

			$led_fld=$form->getElement('ledger');
			$led_fld->select_menu_options=['tags'=>true];
			$balancesheet_field=$form->getElement('balance_sheet');


		}
	}

}