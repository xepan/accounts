<?php
namespace xepan\accounts;
class Model_Currency extends \xepan\base\Model_Document{
	public $status=['Active','InActive'];
	
	public $actions = [
		'Active'=>['view','edit','delete','deactivate'],
		'InActive' => ['view','edit','delete','activate']
	];
		
	function init(){
		parent::init();
		
		$currency_j = $this->join('currency.document_id');
		
		$this->getElement('created_by_id')->defaultValue($this->app->employee->id);
		$this->getElement('status')->defaultValue('Active');

		$currency_j->addField('icon');
		$currency_j->addField('name')->sortable(true);
		$currency_j->addField('value')->sortable(true);
		
		$this->addCondition('type','Currency');
		// $this->hasMany('xepan\commerce\Customer','currency_id','Customers');
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