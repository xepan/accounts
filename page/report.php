<?php
namespace xepan\accounts;
class page_report extends \xepan\base\Page{
	public $title="Reports";
	function init(){
		parent::init();

		$this->app->side_menu->addItem([' Tax Report','icon'=>' fa fa-tax'],'xepan_accounts_report_subtax')->setAttr(['title'=>'Tax Report ']);		
		$this->app->side_menu->addItem([' BalanceSheet Formatted','icon'=>' fa fa-tax'],'xepan_accounts_report_balancesheet')->setAttr(['title'=>'Tax Report ']);		
	}
}