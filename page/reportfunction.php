<?php

namespace xepan\accounts;

class page_reportfunction extends \xepan\base\Page
{
	public $title = "Report Function";
	
	function init(){

		parent::init();

		$tab = $this->add('Tabs');
		$fun_tab = $tab->addTab('Report Function');
		$loop_tab = $tab->addTab('Report Loop');

		$group_model = $fun_tab->add('xepan\accounts\Model_Group');
		$ledger_model = $fun_tab->add('xepan\accounts\Model_Ledger');
		$head_model = $fun_tab->add('xepan\accounts\Model_BalanceSheet');

		$form = $fun_tab->add('Form');
		$name_field = $form->addField('line','name')->validate('required');
		$type_field = $form->addField('dropdown','type')->setValueList([
								'HeadBalance'=>'HeadBalance',
								'GroupBalance'=>'GroupBalance',
								'GroupOnlyBalance'=>'GroupOnlyBalance',
								'GroupCR'=>'GroupCR',
								'GroupDR'=>'GroupDR',
								'GroupOnlyDR'=>'GroupOnlyDR',
								'GroupOnlyCR'=>'GroupOnlyCR',
								'LedgerBalance'=>'LedgerBalance',
								'HeadDR'=>'HeadDR',
								'HeadCR'=>'HeadCR',
								'HeadTransactionSUMDR'=>'HeadTransactionSUMDR',
								'HeadTransactionSUMCR'=>'HeadTransactionSUMCR',
								'GroupTransactionSUMDR'=>'GroupTransactionSUMDR',
								'GroupTransactionSUMCR'=>'GroupTransactionSUMCR',
								'GroupOnlyTransactionSUMDR'=>'GroupOnlyTransactionSUMDR',
								'GroupOnlyTransactionSUMCR'=>'GroupOnlyTransactionSUMCR',
								'PANDL'=>'PANDL',
								'Trading'=>'Trading'
							]);
		
		$group_value_field = $form->addField('dropdown','group_value');
						// ->addClass('multiselect-full-width')
						// ->setAttr(['multiple'=>'multiple'])
		$group_value_field->setModel($group_model);
		$group_value_field->setEmptyText("Please Select Group");
		
		$head_value_field = $form->addField('dropdown','head_value');
						// ->addClass('multiselect-full-width')
						// ->setAttr(['multiple'=>'multiple'])
		$head_value_field->setModel($head_model);
		$head_value_field->setEmptyText("Please Select Head");

		$ledger_value_field = $form->addField('dropdown','ledger_value');
						// ->addClass('multiselect-full-width')
						// ->setAttr(['multiple'=>'multiple'])
		$ledger_value_field->setModel($ledger_model);
		$ledger_value_field->setEmptyText("Please Select Ledger");

		$start_date_field = $form->addField('DropDown','start_date');
		$start_date_field->setValueList([
									'FYStart'=>'FYStart',
									'PreviousFYStart'=>"PreviousFYStart",
									'CurrentMonthStart'=>"CurrentMonthStart",
									'PreviousMonthStart'=>"PreviousMonthStart",
									'CustomDate'=>'CustomDate'
								]);
		$custom_start_date_field = $form->addField('DatePicker','custom_start_date');

		$end_date_field = $form->addField('DropDown','end_date');
		$end_date_field->setValueList([
									'FYEnd'=>'FYEnd',
									'PreviousFYEnd'=>"PreviousFYEnd",
									'CurrentMonthEnd'=>"CurrentMonthEnd",
									'PreviousMonthEnd'=>"PreviousMonthEnd",
									'CustomDate'=>'CustomDate'
								]);
		$custom_end_date_field = $form->addField('DatePicker','custom_end_date');

		$form->addSubmit('Save');

		$type_field->js(true)->univ()->bindConditionalShow([
				'HeadBalance'=>['head_value'],
				'HeadDR'=>['head_value'],
				'HeadCR'=>['head_value'],
				'HeadTransactionSUMDR'=>['head_value'],
				'HeadTransactionSUMCR'=>['head_value'],
				'GroupBalance'=>['group_value'],
				'GroupOnlyBalance'=>['group_value'],
				'GroupCR'=>['group_value'],
				'GroupDR'=>['group_value'],
				'GroupOnlyDR'=>['group_value'],
				'GroupOnlyCR'=>['group_value'],
				'GroupTransactionSUMDR'=>['group_value'],
				'GroupTransactionSUMCR'=>['group_value'],
				'GroupOnlyTransactionSUMDR'=>['group_value'],
				'GroupOnlyTransactionSUMCR'=>['group_value'],
				'LedgerBalance'=>['ledger_value']

			],'div.atk-form-row');

		$start_date_field->js(true)->univ()->bindConditionalShow([
				'CustomDate'=>['custom_start_date']
			],'div.atk-form-row');
		
		$end_date_field->js(true)->univ()->bindConditionalShow([
				'CustomDate'=>['custom_end_date']
			],'div.atk-form-row');

		$rf_model = $fun_tab->add('xepan\accounts\Model_ReportFunction');
		$rf_model->setOrder('name','asc');
		$crud = $fun_tab->add('CRUD',['allow_add'=>false,'allow_edit'=>false]);
		$crud->setModel($rf_model);

		if($form->isSubmitted()){

			if(preg_match('/\s/',$form['name']))
				$form->error('name','whitespace are not allowed');

			if( in_array($form['type'], ['HeadBalance','HeadDR','HeadCR','HeadTransactionSUMDR','HeadTransactionSUMCR']) AND !$form['head_value']){
				$form->error('head_value','please select head Value');
			}		
			elseif( in_array($form['type'], ['LedgerBalance']) AND !$form['ledger_value']){
				$form->error('ledger_value','please select ledger Value');
			}
			elseif (in_array($form['type'], ['GroupBalance','GroupOnlyBalance','GroupCR','GroupDR','GroupOnlyDR','GroupOnlyCR','GroupTransactionSUMDR','GroupTransactionSUMCR','GroupOnlyTransactionSUMDR','GroupOnlyTransactionSUMCR']) AND !$form['group_value']) {
				$form->error('group_value','please select group Value');	
			}

			$rf_model = $this->add('xepan\accounts\Model_ReportFunction');
			$rf_model['name'] = $form['name'];
			$rf_model['type'] = $form['type'];
			$rf_model['group_id'] = $form['group_value'];
			$rf_model['head_id'] = $form['head_value'];
			$rf_model['ledger_id'] = $form['ledger_value'];

			$rf_model['start_date'] = $form['start_date'];
			if($form['start_date'] == "customDate")
				$rf_model['start_date'] = $form['custom_start_date'];

			$rf_model['end_date'] = $form['end_date'];
			if($form['end_date'] == "customDate")
				$rf_model['end_date'] = $form['custom_end_date'];

			$rf_model->save();

			$js=[	
					$crud->js()->reload(),
					$form->js()->reload()
				];
			$form->js(null,$js)->univ()->successMessage('function added successfully')->execute();
		}


		// LOOP CRUD
		$loop_model = $loop_tab->add('xepan\accounts\Model_ReportLoop');
		$crud = $loop_tab->add('xepan\hr\CRUD');
		$crud->setModel($loop_model,
							['name','list_of','under','group_id','group','head_id','head','ledger_id','ledger'],
							['name','list_of','under','group','head','ledger']
						);

		if($crud->isEditing()){
			$form = $crud->form;
			$list_of_field = $form->getElement('list_of');
			$under_field = $form->getElement('under');

			$list_of_field->js('change',$under_field->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$under_field->name]),'list_of'=>$list_of_field->js()->val()]));

			if($_GET['list_of']){
				switch ($_GET['list_of']) {
					case 'Ledger':
						$under_field->setValueList(
								[
									'Group'=>'Group',
									'GroupOnly'=>'GroupOnly',
									'Head'=>'Head'
								]);
						break;

					case 'Group':
						$under_field->setValueList(
								[
									'Group'=>'Group',
									'GroupOnly'=>'GroupOnly',
									'Head'=>'Head'
								]);
						break;

					case 'Transaction':
						$under_field->setValueList(
								[
									'Group'=>'Group',
									'GroupOnly'=>'GroupOnly',
									'Head'=>'Head',
									'Ledger'=>'Ledger'
								]);
						break;
				}
			}

			$under_field->js(true)->univ()->bindConditionalShow([
					'Group'=>['group_id'],
					'GroupOnly'=>['group_id'],
					'Head'=>['head_id'],
					'Ledger'=>['ledger_id'],
					
				],'div.atk-form-row');

		}
	}
}