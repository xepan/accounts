<?php

namespace xepan\accounts;

class Model_Transaction_Purchase extends \xepan\accounts\Model_Transaction{
	function init(){
		parent::init();

		$this->addCondition('related_type','Purchase');
	}
}