<?php
namespace xepan\accounts;
class page_currency extends \Page{
	public $title="Currency Management";
	function init(){
		parent::init();

	$currency = $this->add('xepan\accounts\Model_Currency');
	if($status = $this->app->stickyGET('status'))
			$currency->addCondition('status',$status);
	$currency->add('xepan\hr\Controller_SideBarStatusFilter');
	$crud = $this->add('xepan\hr\CRUD',null,null,['view/grid/currency']);
	$crud->grid->addQuickSearch(['name']);
	$crud->grid->addPaginator(10);
	$crud->setModel($currency,['name','value','icon']);
	}
}