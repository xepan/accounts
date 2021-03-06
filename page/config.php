<?php

namespace xepan\accounts;

class page_config extends \xepan\base\Page{
	public $title="Accounts Configuration";
	
	function page_index(){		

		// $tabs->addTabURL('xepan_accounts_custom_accountentries','Custom Accounts Entry');
		// $tabs->addTabURL('xepan_accounts_financialyear','Financial Year Start Month');
		// $tabs->addTabURL('xepan_accounts_salaryledgerassociation','Salary Ledger Association');
		// $tabs->addTabURL('xepan_accounts_autonotification','Auto Notification');
	}

	function page_currency(){
		$default_currency = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'currency_id'=>'DropDown'
							],
					'config_key'=>'FIRM_DEFAULT_CURRENCY_ID',
					'application'=>'accounts'
			]);
		$default_currency->add('xepan\hr\Controller_ACL');
		$default_currency->tryLoadAny();		

		$form = $this->add('Form');
		$form->setModel($default_currency);

		$default_currency_id=$form->getElement('currency_id')->set($default_currency['currency_id']);
		$default_currency_id->setModel('xepan\accounts\Model_Currency');
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Currency Information Successfully Updated')->execute();
		}

	}

	// function defaultTemplate(){
	// 	return['page/account-configuration'];
	// }
}