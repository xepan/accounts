<?php
namespace xepan\accounts;
class page_report_balancesheet extends page_report{
	public $title="BalanceSheet Formatted";

	function init(){
		parent::init();
		$f=$this->add('Form',null,null,['form/empty']);
		$f->addField('DatePicker','from_date');
		$f->addField('DatePicker','to_date');
		$f->addSubmit('Go');

		$this->app->stickyGET('from_date');
		$this->app->stickyGET('to_date');

		$view = $this->add('xepan\accounts\View_ReportRunner',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']]);

		if($f->isSubmitted()){
			$view->js()->reload([
									'from_date'=>$f['from_date']?:0,
									'to_date'=>$f['to_date']?:0,
								])->execute();
		}

		

	}
}