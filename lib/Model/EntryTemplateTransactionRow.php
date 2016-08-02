<?php


namespace xepan\accounts;


class Model_EntryTemplateTransactionRow extends \xepan\base\Model_Table{
	public $table = "custom_account_entries_templates_transaction_row";

	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\EntryTemplateTransaction','template_transaction_id');
		$this->addField('title');
		$this->addField('side')->enum(['Dr','Cr']);
		
		$group_field = $this->addField('group')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$group_field->setModel($this->add('xepan\accounts\Model_Group',['id_field'=>'name']),'name');
		
		$balancesheet_field = $this->addField('balance_sheet')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$balancesheet_field->setModel($this->add('xepan\accounts\Model_BalanceSheet',['id_field'=>'name']),'name');

		// $this->addField('is_include_subgroup_ledger_account')->type('boolean');
	
		$parent_group_field = $this->addField('parent_group')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$parent_group_field->setModel($this->add('xepan\accounts\Model_ParentGroup',['id_field'=>'name']),'name');
		
		$ledger_field = $this->addField('ledger')->display(['form'=>'xepan\base\NoValidateDropDown']);
		$ledger_field->setModel($this->add('xepan\accounts\Model_Ledger',['id_field'=>'name']),'name');

		$this->addField('ledger_type');
		$this->addField('is_ledger_changable')->type('boolean');
		$this->addField('is_allow_add_ledger')->type('boolean');
		$this->addField('is_include_currency')->type('boolean');

		$this->addHook('beforeSave',$this);
	}

	function beforeSave(){
		/*Check  Group*/

		$group_m=$this->add('xepan\accounts\Model_Group');
		$group_m->addCondition('name',$this['group']);
		$group_m->tryLoadAny();

		if(!$group_m->loaded()){
			$balancesheet_m=$this->add('xepan\accounts\Model_BalanceSheet');
			$balancesheet_m->tryLoadBy('name',$this['balance_sheet']);
			
			if(!$balancesheet_m->loaded()) throw $this->exception("must be select Balance Sheet",'ValidityCheck')->setField('balance_sheet');
			
			$pg_m=$this->add('xepan\accounts\Model_ParentGroup');
			$pg_m->tryLoadBy('name',$this['parent_group']);

			if(!$pg_m->loaded()) throw $this->exception("must be select Parent Group",'ValidityCheck')->setField('parent_group');
			
			$group_m['parent_group_id']=$pg_m->id;
			$group_m['balance_sheet_id']=$balancesheet_m->id;
			$group_m->save();
		}
		
		/*Check Ledger*/

		$ledger_m = $this->add('xepan\accounts\Model_Ledger');
		$ledger_m->addCondition('name',$this['ledger']);
		$ledger_m->tryLoadAny();
		
		if(!$ledger_m->loaded()){
			$ledger_m['group_id'] = $group_m->id;
			$ledger_m['ledger_type'] = $this['ledger_type'];
			$ledger_m->save();
		}
	}
}