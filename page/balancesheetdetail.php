<?php
namespace xepan\accounts;
class page_balancesheetdetail extends \Page{
	public $title="Balance Sheet's Groups ";
	function init(){
		parent::init();

		// $group = 54;
		// $this->add('xepan\accounts\Model_Group')->load($group);
		$this->add('xepan\accounts\Model_Group')->tryLoadAny();
		
	}

	function detail_balance_sheet(){

		$this->api->redirect($this->api->url('xepan_commerce_balance',['document_id'=>$this->id]));
	}

	function defaultTemplate(){
		return ['page/balancesheetdetail'];
	}
}