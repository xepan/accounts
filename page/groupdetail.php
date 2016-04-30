<?php
namespace xepan\accounts;
class page_groupdetail extends \Page{
	public $title="Group's Ledger";
	function init(){
		parent::init();

	}

	function defaultTemplate(){
		return ['page/groupdetail'];
	}
}