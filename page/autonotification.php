<?php

namespace xepan\accounts;

class page_autonotification extends \xepan\base\Page{
	public $title="Auto Notification";
	function init(){
		parent::init();

		$tabs = $this->add('Tabs');
		$daily_tab = $tabs->addTab('Daily');
		$weekly_tab = $tabs->addTab('Weekly');
		$monthly_tab = $tabs->addTab('Monthly');
		$quartly_tab = $tabs->addTab('Quartly');
		$yearly_tab = $tabs->addTab('Yearly');
		
		$notify_report_array = array('balance_sheet' =>'Balance Sheet',
								'profit_and_loss' =>'Profit & Loss',
								'transaction_summary' =>'Transaction Summary');

		/**
		Daily Notification
		*/
		$daily_autonotification_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'auto_notification_duration'=>'Line',
							'notify_to'=>'DropDown',
							'report'=>'DropDown',
							'notify'=>'DropDown',
							],
					'config_key'=>'ACCOUNTS_DAILY_REPORT_AUTO_NOTIFICATION',
					'application'=>'accounts'
			]);
		$daily_autonotification_mdl->add('xepan\hr\Controller_ACL');
		$daily_autonotification_mdl->tryLoadAny();
		$daily_autonotification_mdl['auto_notification_duration'] = 'Daily';

		$form = $daily_tab->add('Form');
		$form->setModel($daily_autonotification_mdl);

		$notify_to = $form->getElement('notify_to')->set($daily_autonotification_mdl['notify_to']);
		$notify_to->setModel('xepan\base\Model_Contact');
		
		$report = $form->getElement('report')->setValueList($notify_report_array)->set($daily_autonotification_mdl['report']);
		$report->setAttr(['multiple'=>'multiple']);
		
		$form->getElement('notify')->setValueList(['yes'=>'Yes','no'=>'No'])->set($daily_autonotification_mdl['notify']);
		
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Daily Notification Info Successfully Updated')->execute();
		}


		/**
		Weekly Notification
		*/
		$weekly_autonotification_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'auto_notification_duration'=>'Line',
							'notify_to'=>'DropDown',
							'report'=>'DropDown',
							'notify'=>'DropDown',
							],
					'config_key'=>'ACCOUNTS_WEEKLY_REPORT_AUTO_NOTIFICATION',
					'application'=>'accounts'
			]);
		$weekly_autonotification_mdl->add('xepan\hr\Controller_ACL');
		$weekly_autonotification_mdl->tryLoadAny();
		$weekly_autonotification_mdl['auto_notification_duration'] = 'Weekly';

		$form = $weekly_tab->add('Form');
		$form->setModel($weekly_autonotification_mdl);

		$notify_to = $form->getElement('notify_to')->set($weekly_autonotification_mdl['notify_to']);
		$notify_to->setModel('xepan\base\Model_Contact');
		
		$report = $form->getElement('report')->setValueList($notify_report_array)->set($weekly_autonotification_mdl['report']);
		$report->setAttr(['multiple'=>'multiple']);
		
		$form->getElement('notify')->setValueList(['yes'=>'Yes','no'=>'No'])->set($weekly_autonotification_mdl['notify']);
		
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Weekly Notification Info Successfully Updated')->execute();
		}


		/**
		Monthly Notification
		*/
		$monthly_autonotification_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'auto_notification_duration'=>'Line',
							'notify_to'=>'DropDown',
							'report'=>'DropDown',
							'notify'=>'DropDown',
							],
					'config_key'=>'ACCOUNTS_MONTHLY_REPORT_AUTO_NOTIFICATION',
					'application'=>'accounts'
			]);
		$monthly_autonotification_mdl->add('xepan\hr\Controller_ACL');
		$monthly_autonotification_mdl->tryLoadAny();
		$monthly_autonotification_mdl['auto_notification_duration'] = 'Monthly';

		$form = $monthly_tab->add('Form');
		$form->setModel($monthly_autonotification_mdl);

		$notify_to = $form->getElement('notify_to')->set($monthly_autonotification_mdl['notify_to']);
		$notify_to->setModel('xepan\base\Model_Contact');
		
		$report = $form->getElement('report')->setValueList($notify_report_array)->set($monthly_autonotification_mdl['report']);
		$report->setAttr(['multiple'=>'multiple']);
		
		$form->getElement('notify')->setValueList(['yes'=>'Yes','no'=>'No'])->set($monthly_autonotification_mdl['notify']);
		
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Monthly Notification Info Successfully Updated')->execute();
		}


		/**
		Quartly Notification
		*/
		$quartly_autonotification_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'auto_notification_duration'=>'Line',
							'notify_to'=>'DropDown',
							'report'=>'DropDown',
							'notify'=>'DropDown',
							],
					'config_key'=>'ACCOUNTS_QUARTLY_REPORT_AUTO_NOTIFICATION',
					'application'=>'accounts'
			]);
		$quartly_autonotification_mdl->add('xepan\hr\Controller_ACL');
		$quartly_autonotification_mdl->tryLoadAny();
		$quartly_autonotification_mdl['auto_notification_duration'] = 'Quartly';

		$form = $quartly_tab->add('Form');
		$form->setModel($quartly_autonotification_mdl);

		$notify_to = $form->getElement('notify_to')->set($quartly_autonotification_mdl['notify_to']);
		$notify_to->setModel('xepan\base\Model_Contact');
		
		$report = $form->getElement('report')->setValueList($notify_report_array)->set($quartly_autonotification_mdl['report']);
		$report->setAttr(['multiple'=>'multiple']);
		
		$form->getElement('notify')->setValueList(['yes'=>'Yes','no'=>'No'])->set($quartly_autonotification_mdl['notify']);
		
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Quartly Notification Info Successfully Updated')->execute();
		}



		/**
		Yearly Notification
		*/
		$yearly_autonotification_mdl = $this->add('xepan\base\Model_ConfigJsonModel',
			[
				'fields'=>[
							'auto_notification_duration'=>'Line',
							'notify_to'=>'DropDown',
							'report'=>'DropDown',
							'notify'=>'DropDown',
							],
					'config_key'=>'ACCOUNTS_YEARLY_REPORT_AUTO_NOTIFICATION',
					'application'=>'accounts'
			]);
		$yearly_autonotification_mdl->add('xepan\hr\Controller_ACL');
		$yearly_autonotification_mdl->tryLoadAny();
		$yearly_autonotification_mdl['auto_notification_duration'] = 'Yearly';

		$form = $yearly_tab->add('Form');
		$form->setModel($yearly_autonotification_mdl);

		$notify_to = $form->getElement('notify_to')->set($yearly_autonotification_mdl['notify_to']);
		$notify_to->setModel('xepan\base\Model_Contact');
		
		$report = $form->getElement('report')->setValueList($notify_report_array)->set($yearly_autonotification_mdl['report']);
		$report->setAttr(['multiple'=>'multiple']);
		
		$form->getElement('notify')->setValueList(['yes'=>'Yes','no'=>'No'])->set($yearly_autonotification_mdl['notify']);
		
		$form->addSubmit('Update')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$form->save();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Yearly Notification Info Successfully Updated')->execute();
		}



	}
}