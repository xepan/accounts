<?php


namespace xepan\accounts;


class Model_EntrytemplateRow extends \xepan\base\Model_Table{
	public $table = "custom_account_entries_templates_row";

	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\EntryTemplate','template_id');
		$this->addField('side')->enum(['Dr','Cr']);
		$this->hasOne('xepan\accounts\Ledger','account','name');
		$this->hasOne('xepan\accounts\Group','root_group','name');
	}
}