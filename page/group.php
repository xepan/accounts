<?php
namespace xepan\accounts;
class page_group extends \xepan\base\Page{
	public $title="Account Group";
	function init(){
		parent::init();

		$group = $this->add('xepan\accounts\Model_Group');
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/group-grid']);
		$crud->setModel($group,['name','parent_group_id','balance_sheet_id','root_group_id'],['name','parent_group','balance_sheet','root_group']);
	}
}