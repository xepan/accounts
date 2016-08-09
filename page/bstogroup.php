<?php

namespace xepan\accounts;

class page_bstogroup extends \xepan\base\Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
		
		$bs_id = $this->api->stickyGET('bs_id');
		$bs_group = $this->add('xepan\accounts\Model_BSGroup');
		$bs_group->addCondititon('balance_sheet_id',$bs_id);

		$this->add('grid')->setModel($bs_group);
	}
}