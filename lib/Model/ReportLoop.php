<?php
namespace xepan\accounts;

class Model_ReportLoop extends \xepan\accounts\Model_ReportFunction{
	
	function init(){
		parent::init();

		$this->addCondition('list_of','<>',null);
	}
}