<?php


namespace xepan\accounts;

class Model_EntryTemplate extends \xepan\base\Model_Table {
	
	public $table= "custom_account_entries_templates";

	function init(){
		parent::init();

		$this->addField('name');
		$this->hasMany('xepan\accounts\EntryTemplateRow','template_id');
	}

	function manageForm($parent){

	}

	// As per given rules of this template, ie groups accounts etc.
	function verifyData($data){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}

	function execute($data=[]){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

	}
}