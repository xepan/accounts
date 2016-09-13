<?php

namespace xepan\accounts;

class page_financialyear extends \xepan\base\Page{
	public $title="Financial Year Configuration";
	function init(){
		parent::init();

		$config=$this->app->epan->config;
		$default_financial_year_start_month=$config->getConfig('DEFAULT_FINANCIAL_YEAR_START_MONTH','accounts');
		$form=$this->add('Form');

		$financial_year_month_array = array('01' =>'January',
									 '02' =>'February',
									 '03' =>'March',
									 '04' =>'April',
									 '05' =>'May',
									 '06' =>'June',
									 '07' =>'July',
									 '08' =>'August',
									 '09' =>'September',
									 '10' =>'October',
									 '11' =>'November',
									 '12' =>'December');

		$starting_month=$form->addField('Dropdown','starting_month')->setValueList($financial_year_month_array);
		$form->addSubmit('Update')->addClass('btn btn-primary');
		if($form->isSubmitted()){
			$config->setConfig('DEFAULT_FINANCIAL_YEAR_START_MONTH',$form['starting_month'],'accounts');
			if($form['starting_month'] == 1)
				$ending_month = '12';
			else
				$month = $form['starting_month'] - 1;
				$ending_month = "0" . $month;

			$config->setConfig('DEFAULT_FINANCIAL_YEAR_END_MONTH',$ending_month,'accounts');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}
	}

}