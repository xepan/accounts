<?php
namespace xepan\accounts;
class page_currency extends \Page{
	public $title="Currency Management";
	function init(){
		parent::init();

	$currency = $this->add('xepan\accounts\Model_Currency');
	$crud = $this->add('xepan\hr\CRUD',null,null,['view/grid/currency']);
	$crud->setModel($currency,['name','value','icon']);
	
	}
}