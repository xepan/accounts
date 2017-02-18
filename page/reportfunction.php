<?php

namespace xepan\accounts;

class page_reportfunction extends \xepan\base\Page
{
	public $title = "Report Function";
	
	function init(){

		parent::init();

		$group_model = $this->add('xepan\accounts\Model_Group');
		$ledger_model = $this->add('xepan\accounts\Model_Ledger');
		$head_model = $this->add('xepan\accounts\Model_Group');
		$head_model->addCondition('parent_group_id',null);

		$form = $this->add('Form');

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
								'PANDL'=>'PANDL'
							]);
		
		$group_value_field = $form->addField('dropdown','group_value')
						->addClass('multiselect-full-width')
						->setAttr(['multiple'=>'multiple'])
						->setModel($group_model);
		
		$head_value_field = $form->addField('dropdown','head_value')
						->addClass('multiselect-full-width')
						->setAttr(['multiple'=>'multiple'])
						->setModel($head_model);

		$ledger_value_field = $form->addField('dropdown','ledger_value')
						->addClass('multiselect-full-width')
						->setAttr(['multiple'=>'multiple'])
						->setModel($ledger_model);

		$form->addField('DatePicker','start_date');
		$form->addField('DatePicker','end_date');
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


		$rf_model = $this->add('xepan\accounts\Model_ReportFunction');
		$rf_model->setOrder('name','asc');
		$crud = $this->add('CRUD',['allow_add'=>false,'allow_edit'=>false]);
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
			$rf_model['group_value'] = $form['group_value'];
			$rf_model['head_value'] = $form['head_value'];
			$rf_model['ledger_value'] = $form['ledger_value'];
			$rf_model['start_date'] = $form['start_date'];
			$rf_model['end_date'] = $form['end_date'];
			$rf_model->save();

			$js=[	
					$crud->js()->reload(),
					$form->js()->reload()
				];
			$form->js(null,$js)->univ()->successMessage('function added successfully')->execute();
		}

	}
}