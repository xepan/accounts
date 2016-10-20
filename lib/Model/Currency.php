<?php
namespace xepan\accounts;
class Model_Currency extends \xepan\hr\Model_Document{
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
		$currency_j->addField('integer_part');
		$currency_j->addField('fractional_part');

		$this->addCondition('type','Currency');
		$this->addHook('beforeSave',[$this,'updateSearchString']);
	}

	function activate(){
		$this['status']='Active';
		$this->app->employee
            ->addActivity("Currency '".$this['name']."' is available for use", null/* Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_accounts_currency")
            ->notifyWhoCan('deactivate','Active',$this);
		$this->saveAndUnload();
	}

	function deactivate(){
		$this['status']='InActive';
		$this->app->employee
            ->addActivity("Currency '". $this['name'] ."' not available for use", null /*Related Document ID*/, $this->id /*Related Contact ID*/,null,null,"xepan_accounts_currency")
            ->notifyWhoCan('activate','InActive',$this);
		$this->saveAndUnload();
	}

	function updateSearchString($m){		
		$search_string = ' ';
		$search_string .=" ". $this['name'];
		$search_string .=" ". $this['type'];
		$search_string .=" ". $this['status'];
		$this['search_string'] = $search_string;
	} 
}