<?php

namespace xepan\accounts;

class Model_Report_Layout extends \xepan\base\Model_Table {
	
	public $table="account_report_layout";
	public $acl=false;

	function init(){
		parent::init();

		$this->addField('name');
		$this->addField('layout')->caption('Report Content')->display(['form'=>'xepan\base\RichText']);
	}
}