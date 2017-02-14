<?php

namespace xepan\accounts;

class page_test extends \xepan\base\Page
{
	public $title = "Test page";
	
	function init()
	{
		parent::init();

		$this->add('xepan\accounts\View_ReportRunner');
	}
}