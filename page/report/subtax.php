<?php
namespace xepan\accounts;
class page_report_subtax extends page_report{
	public $title="Sub Tax Reports";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Submit');

		if($form->isSubmitted()){
			
		}

	}
}