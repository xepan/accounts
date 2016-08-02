<?php


namespace xepan\accounts;


class page_custom_accountentries extends \xepan\base\Page {

	function page_index(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');

		$crud = $this->add('CRUD');
		$crud->setModel($entry_template_m,['name'],['name']);

		if(!$crud->isEditing()){
			$crud->grid->addColumn('expander','detail');
		}
	}

	function page_detail(){
		$entry_template_m = $this->add('xepan\accounts\Model_EntryTemplate');
		$entry_template_m->load($this->app->stickyGET('custom_account_entries_templates_id'));
		$m = $entry_template_m->ref('xepan\accounts\EntryTemplateRow');

		$m->addHook('beforeSave',function($m){
			
			
		});

		$crud = $this->add('CRUD');
		$crud->setModel($m);

		if(!$crud->isEditing()){
			$crud->grid->removeColumn('id');
		}
	}

}