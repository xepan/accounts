<?php


namespace xepan\accounts;

class View_EasySetupWizard extends \View{
	function init(){
		parent::init();

		/**
		............. Currency Configuration ...............
		*/
		if($_GET[$this->name.'_account_config']){
			$this->js(true)->univ()->frameURL("Currency Configuration",$this->app->url('xepan_accounts_config'));
		}

		$isDone = false;
		
		$action = $this->js()->reload([$this->name.'_account_config'=>1]);

		$default_currency = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'currency_id'=>'DropDown'
							],
					'config_key'=>'FIRM_DEFAULT_CURRENCY_ID',
					'application'=>'accounts'
			]);
		$default_currency->tryLoadAny();		

		if($default_currency['currency_id']){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already updated currency, visit page ? <a href="'. $this->app->url('xepan_accounts_config')->getURL().'"> click here to go </a>');
		}

		$default_currency_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Accounts')
			->setTitle('Set Currency')
			->setMessage('Please set currency configuration according to your country.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**
		............. Accounts Financial Year ...............
		*/
		if($_GET[$this->name.'_financial_year']){
			$this->js(true)->univ()->frameURL("Financial Year Setting",$this->app->url('xepan_accounts_financialyear'));
		}

		$isDone = false;
		
		$action = $this->js()->reload([$this->name.'_financial_year'=>1]);

		$financial_year_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'default_financial_year_start_month'=>'DropDown',
							'default_financial_year_end_month'=>'DropDown'
							],
					'config_key'=>'DEFAULT_FINANCIAL_YEAR_AND_MONTH',
					'application'=>'accounts'
			]);
		$financial_year_mdl->tryLoadAny();		

		if($financial_year_mdl['default_financial_year_start_month'] && $financial_year_mdl['default_financial_year_end_month']){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already updated financial year, visit page ? <a href="'. $this->app->url('xepan_accounts_financialyear')->getURL().'"> click here to go </a>');
		}

		$financial_year_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Accounts')
			->setTitle('Set Financial Year')
			->setMessage('Please configure financial year for your accounts reports.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		/**
		............. Salary Ledger Associaton Configuration ...............
		*/
		if($_GET[$this->name.'_sal_ledger_association']){
			$this->js(true)->univ()->frameURL("Salary Ledger Associaton",$this->app->url('xepan_accounts_salaryledgerassociation'));
		}
		$isDone = false;

		$action = $this->js()->reload([$this->name.'_sal_ledger_association'=>1]);

			if($this->add('xepan\accounts\Model_SalaryLedgerAssociation')->count()->getOne() > 0){
				$isDone = true;
				$action = $this->js()->univ()->dialogOK("Already have Data",' You have already assoicate salary with ledgers, visit page ? <a href="'. $this->app->url('xepan_accounts_salaryledgerassociation')->getURL().'"> click here to go </a>');
			}

		$sal_assoc_view = $this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Accounts')
			->setTitle('Assoicate Ledgers With Salaries')
			->setMessage('Assoicate ledgers to keep accounting entries')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);
		
		// currency management
		if($_GET[$this->name.'_currency']){
			$this->js(true)->univ()->frameURL("Currency Management",$this->app->url('xepan_accounts_currency'));
		}
		$isDone = false;
		$action = $this->js()->reload([$this->name.'_currency'=>1]);
		if($this->add('xepan\accounts\Model_Currency')->count()->getOne() > 0){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added your organization currency, visit page ? <a href="'. $this->app->url('xepan_accounts_currency')->getURL().'"> click here to go </a>');
		}
		$this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Accounts')
			->setTitle('Currency Management')
			->setMessage('add currency, your organization deals with')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

		// default currency
		if($_GET[$this->name.'_defaultcurrency']){
			$this->js(true)->univ()->frameURL("Default Currency",$this->app->url('xepan_accounts_config'));
		}

		$isDone = false;
		$action = $this->js()->reload([$this->name.'_defaultcurrency'=>1]);
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

		if($default_currency['currency_id']){
			$isDone = true;
			$action = $this->js()->univ()->dialogOK("Already have Data",' You have already added your organization currency, visit page ? <a href="'. $this->app->url('xepan_accounts_config')->getURL().'"> click here to go </a>');
		}

		$this->add('xepan\base\View_Wizard_Step')
			->setAddOn('Application - Accounts')
			->setTitle('Default Currency')
			->setMessage('add firm default currency, go to currency tab.')
			->setHelpMessage('Need help ! click on the help icon')
			->setHelpURL('#')
			->setAction('Click Here',$action,$isDone);

	}
}