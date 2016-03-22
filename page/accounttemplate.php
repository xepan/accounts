<?php

namespace xepan\accounts;

class page_accounttemplate extends \Page
{
	public $title = "Account Type";
	public $data =[
					['name'=>'Customer', 'description'=>'Entries related to customer', 'acctype'=>'customer'],
					['name'=>'Supplier', 'description'=>'Entries related to Supplier', 'acctype'=>'supplier']
	]; 

	function init()
	{
		parent::init();

		$completelister = $this->add('CompleteLister',null,null,['view\accounttemplate']);
		$completelister->setSource($this->data);
		
	}
}