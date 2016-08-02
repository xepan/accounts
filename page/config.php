<?php
namespace xepan\accounts;
class page_config extends \xepan\base\Page{
	public $title="Accounts Configuration";
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$currency_tab = $tabs->addTab('Currency');
		
		$config=$this->app->epan->config;
		$default_currency=$config->getConfig('DEFAULT_CURRENCY_ID','accounts');
		$form=$currency_tab->add('Form');

		$currency_field=$form->addField('Dropdown','currency_id')->set($default_currency);
		$currency_field->setModel('xepan\accounts\Model_Currency');
		$form->addSubmit('Update')->addClass('btn btn-primary');
		if($form->isSubmitted()){
			$config->setConfig('DEFAULT_CURRENCY_ID',$form['currency_id'],'accounts');
			$form->js(null,$form->js()->reload())->univ()->successMessage('Update Information')->execute();
		}

		$tabs->addTabURL('xepan_accounts_custom_accountentries','Custom Accounts Entry');
	}

	function defaultTemplate(){
		return['page/account-configuration'];
	}
}