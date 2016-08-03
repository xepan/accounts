<?php


namespace xepan\accounts;


class page_custom_accountentries extends \xepan\base\Page {

	function page_index(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/grid/account-transaction-template']);
		$crud->setModel($entry_template_m,['name','detail','unique_trnasaction_template_code',
											'is_favourite_menu_lister','is_merge_transaction'],
										  ['name','detail','unique_trnasaction_template_code',
										  	'is_system_default','is_favourite_menu_lister','is_merge_transaction']);
		// $crud->setModel($entry_template_m);
		$crud->grid->addColumn('expander','transactions');
		$import_template = $crud->grid->addColumn('button','import');
		$crud->grid->addColumn('button','export');

		$crud->grid->addHook('formatRow',function($g){
			if($g->model['is_system_default']){
				$g->current_row_html['edit'] = " ";
				$g->current_row_html['delete'] = " ";
			}
		});
	}

	function page_transactions(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$entry_template_m->load($this->app->stickyGET('custom_account_entries_templates_id'));

		$temp_tansaction=$entry_template_m->ref('xepan\accounts\EntryTemplateTransaction');

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/grid/account-transaction-lister']);
		$crud->setModel($temp_tansaction);
		$crud->grid->addColumn('expander','rows');

	}

	function page_transactions_rows(){
		$transaction_id = $this->api->stickyGET('custom_account_entries_templates_transactions_id');
		$transaction = $this->add('xepan\accounts\Model_EntryTemplateTransaction');
		$transaction->load($transaction_id);

		$rows = $transaction->ref('xepan\accounts\EntryTemplateTransactionRow');

		$crud=$this->add('xepan\hr\CRUD',null,null,['view/grid/account-transaction-rows-lister']);

		if($crud->isEditing()){
			$form=$crud->form;
			$form->setLayout(['view/form/accountentriesrow']);
		}	
		$crud->setModel($rows);
		if($crud->isEditing()){
			$form=$crud->form;
			$grp_fld = $form->getElement('group');
			$grp_fld->select_menu_options=['tags'=>true];

			$group_m=$this->add('xepan\accounts\Model_Group');
			foreach ($group_m as $g) {
				$x[$g['name']]=[];
			}
			$x['*']=['parent_group','balance_sheet'];

			$grp_fld->js(true)->univ()->bindConditionalShow(
				$x,
			'div.atk-form-row');

			$ledger_fld = $form->getElement('ledger');
			$ledger_fld->select_menu_options=['tags'=>true];
			
			$ledger_m=$this->add('xepan\accounts\Model_Ledger');
			foreach ($ledger_m as $ledg) {
				$y[$ledg['name']]=[];
			}
			$y['*']=['ledger_type'];

			$ledger_fld->js(true)->univ()->bindConditionalShow(
				$y,
			'div.atk-form-row');


			$balancesheet_field=$form->getElement('balance_sheet');


		}
	}


}