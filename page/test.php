<?php

namespace xepan\accounts;

class page_test extends \xepan\base\Page
{
	public $title = "Test page";
	
	function init()
	{
		parent::init();

		$tra =$this->add('xepan\accounts\Model_Transaction')->load(17);
		$this->form  = $form = $this->add('xepan\accounts\Form_EntryRunner');
		$form->setModel($tra);
	}
}