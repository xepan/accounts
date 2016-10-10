<?php

namespace xepan\accounts;

class page_financialyear extends \xepan\base\Page{
	public $title="Financial Year Configuration";
	function init(){
		parent::init();

		$financial_year_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'default_financial_year_start_month'=>'DropDown',
							'default_financial_year_end_month'=>'DropDown'
							],
					'config_key'=>'DEFAULT_FINANCIAL_YEAR_AND_MONTH',
					'application'=>'accounts'
			]);
		$financial_year_mdl->add('xepan\hr\Controller_ACL');
		$financial_year_mdl->tryLoadAny();

		$form=$this->add('Form');
		$form->setModel($financial_year_mdl,['default_financial_year_start_month']);

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

		$starting_month=$form->getElement('default_financial_year_start_month')->setValueList($financial_year_month_array)->set($financial_year_mdl['default_financial_year_start_month']);
		$form->addSubmit('Update')->addClass('btn btn-primary');
		if($form->isSubmitted()){
			$financial_year_mdl['default_financial_year_start_month'] = $form['default_financial_year_start_month'];
			if($form['default_financial_year_start_month'] == 1)
				$ending_month = '12';
			else
				$month = $form['default_financial_year_start_month'] - 1;
				$ending_month = "0" . $month;

			$financial_year_mdl['default_financial_year_end_month'] = $ending_month;
			$financial_year_mdl->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Successfully updated financial year information')->execute();
		}
	}

}