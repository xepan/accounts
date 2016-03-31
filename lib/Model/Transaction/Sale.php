<?php

namespace xepan\accounts;

class Model_Transaction_Sale extends \xepan\accounts\Model_Transaction{
	function init(){
		parent::init();

		$this->addCondition('related_type','SaleInvoice');
	}
}