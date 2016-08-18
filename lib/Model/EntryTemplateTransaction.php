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
		
		$this->addExpression('is_system_default')->set($this->refSQL('template_id')->fieldQuery('is_system_default'));

		$this->addHook('beforeDelete',function($m){
			$m->ref('xepan\accounts\EntryTemplateTransactionRow')->each(function($m1){
				$m1->delete();
			});
		});

		$this->is([
			'type|required'
			]);
		$this->setOrder('id');
	}
}
