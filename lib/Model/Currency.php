<?php
namespace xepan\accounts;
class Model_Currency extends \xepan\base\Model_Table{
	public $table="currency";
	public $status=['Active','InActive'];
	
	public $actions = [
		'Active'=>['view','edit','delete','deactivate'],
		'InActive' => ['view','edit','delete','activate']
	];
		
	function init(){
		parent::init();
		$this->addField('icon');
		$this->addField('name');
		$this->addField('value');
		$this->hasMany('xepan\commerce\Customer','currency_id','Customers');
	
		$this->addField('status')->enum($this->status)->defaultValue('Active');
		// $this->addCondition('type','Currency');
	
	}

	function activate(){
		$this['status']='Active';
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->saveAndUnload();
	}
}