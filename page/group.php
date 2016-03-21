<?php
namespace xepan\accounts;
class page_group extends \Page{
	public $title="Account Group";
	function init(){
		parent::init();

	$group = $this->add('xepan\accounts\Model_Group');
		$crud = $this->app->layout->add('xepan\base\CRUD');
		$crud->setModel($group);
	}
}