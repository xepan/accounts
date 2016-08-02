<?php


namespace xepan\accounts;


class Model_EntryTemplateTransaction extends \xepan\base\Model_Table{
	public $table = "custom_account_entries_templates_transactions";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\EntryTemplate','template_id');
		$this->addField('name');
		$this->addField('type');
		$this->hasMany('xepan\accounts\EntryTemplateTransactionRow','template_transaction_id');
		
		$this->is([
			'type|required'
			]);
	}
}
