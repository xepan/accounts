<?php
namespace xepan\accounts;
class page_config extends \Page{
	public $title="Accounts Configuration";
	function init(){
		parent::init();
		$config=$this->app->epan->config;
		$default_currency=$config->getConfig('DEFAULT_CURRENCY_ID','accounts');
		$form=$this->add('Form');

		$currency_field=$form->addField('Dropdown','currency_id')->set($default_currency);
		$currency_field->setModel('xepan\commerce\Model_Currency');
		$form->addSubmit('Update');
		if($form->isSubmitted()){
			$config->setConfig('DEFAULT_CURRENCY_ID',$form['currency_id'],'accounts');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();	
		}
	}
}