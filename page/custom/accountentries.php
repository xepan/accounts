<?php


namespace xepan\accounts;


class page_custom_accountentries extends \xepan\base\Page {
	public $title='xEpan Accounts Entries';
	public $dir='defaultAccount';
    public $namespace = __NAMESPACE__;
	
	function page_index(){
	
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/grid/account-transaction-template']);
		$crud->setModel($entry_template_m,
									[
										'name','detail','unique_trnasaction_template_code',
										'is_favourite_menu_lister','is_merge_transaction'
									],
									[
										'name','detail','unique_trnasaction_template_code',
										'is_system_default','is_favourite_menu_lister',
										'is_merge_transaction'
									]);

		$crud->grid->addColumn('expander','transactions');
		$crud->grid->addPaginator(10);
		$crud->grid->addQuickSearch(['name']);

		if(!$crud->isEditing()){
			$import_btn=$crud->grid->addButton('import')->addClass('btn btn-primary');

			$p=$this->add('VirtualPage');
			$p->set(function($p){
				$f=$p->add('Form');
				$f->addField('text','json');
				$f->addSubmit('Go');
				
				if($f->isSubmitted()){
					$import_m=$this->add('xepan\accounts\Model_EntryTemplate');

					$import_m->importJson($f['json']);	
					
					$f->js()->reload()->univ()->successMessage('Done')->execute();
				}
			});
			if($import_btn->isClicked()){
				$this->js()->univ()->frameURL('Import',$p->getUrl())->execute();
			}
			
			$p=$this->add('VirtualPage');
			$p->set(function($p){
				$export_m=$this->add('xepan\accounts\Model_EntryTemplate')->load($p->id);
					$json=$export_m->exportJson();
					$p->add('View')->set($json);
			});

			$p->addColumn("export", "export", "export", $crud->grid);

			$crud->grid->addHook('formatRow',function($g){
				if($g->model['is_system_default']){
					$g->current_row_html['edit'] = " ";
					$g->current_row_html['delete'] = " ";
				}
			});
		}
		
	}

	function page_transactions(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$entry_template_m->load($this->app->stickyGET('custom_account_entries_templates_id'));

		$temp_tansaction=$entry_template_m->ref('xepan\accounts\EntryTemplateTransaction');

		$crud=$this->add('xepan\hr\CRUD',['grid_class'=>'xepan\accounts\Grid_AccountsBase'],null,['view/grid/account-transaction-lister']);
		$crud->setModel($temp_tansaction);

		$crud->grid->addColumn('expander','rows');

		if(!$crud->isEditing()){
			$crud->grid->addHook('formatRow',function($g){
				if($g->model['is_system_default']){
					$g->current_row_html['edit'] = " ";
					$g->current_row_html['delete'] = " ";
				}
			});
		}
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

			$prnt_grp = $form->getElement('parent_group');
			$prnt_grp->select_menu_options=['tags'=>true];

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
		if(!$crud->isEditing()){
			$crud->grid->addHook('formatRow',function($g){
				if($g->model['is_system_default']){
					$g->current_row_html['edit'] = " ";
					$g->current_row_html['delete'] = " ";
				}
			});
		}
	}


}