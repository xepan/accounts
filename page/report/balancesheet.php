<?php
namespace xepan\accounts;
class page_report_balancesheet extends page_report{
	public $title="BalanceSheet Formatted";

	function init(){
		parent::init();

		$this->add('xepan\accounts\View_BalanceSheetFormatted');

	}
}