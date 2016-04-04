<?php

namespace xepan\accounts;

class page_accounttemplate extends \Page
{
	public $title = "Account Type";
	
	function init()
	{
		parent::init();

		$completelister = $this->add('CompleteLister',null,null,['view\accounttemplate']);
		$completelister->setSource($this->getConfig('account_template_data'));
		
	}
}