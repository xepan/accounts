<?php
namespace xepan\accounts;
class page_audit extends \xepan\base\Page {
	
	public $title="Merge Ledger";
	
	function init(){
		parent::init();

		$form=$this->add('Form',null,null);
		$form->setLayout('view/form/act-merge-form');

		$ldgr_for_remove = $form->addField('autocomplete/Basic','ldgr_for_remove')->validateNotNull();
		$new_merged_ldgr = $form->addField('autocomplete/Basic','new_merged_ldgr')->validateNotNull();
		
		$group_name = $form->addField('Line','ldgr_for_remove_group_name');
		$ldgr_for_remove->other_field->js('change',$group_name->js()->reload(null,null,[$this->app->url(null,['cut_object'=>$group_name]),'ledger_name'=>$ldgr_for_remove->other_field->js()->val()]));

		// $ledger = $this->add('xepan\accounts\Model_Ledger');
		// $ledger->loadBy('id',$form['ldgr_for_remove']);

		

		$form->addField('Line','ldgr_for_remove_ledger_type');
		$form->addField('Line','ldgr_for_remove_OpeningBalanceDr');
		$form->addField('Line','ldgr_for_remove_OpeningBalanceCr');
		
		$form->addField('Line','new_merged_ldgr_ledger_type');
		$form->addField('Line','new_merged_ldgr_group_name');
		$form->addField('Line','new_merged_ldgr_OpeningBalanceDr');
		$form->addField('Line','new_merged_ldgr_OpeningBalanceCr');
	
		$ldgr_for_remove->setModel('xepan\accounts\Ledger');
		$new_merged_ldgr->setModel('xepan\accounts\Ledger');


		// $this->addExpression('CurrentBalanceDr')->set(function($m,$q){
		// $this->addExpression('CurrentBalanceCr')->set(function($m,$q){

		$form->addSubmit('Merge Ledger')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$new_merged_ldgr = $this->add('xepan\accounts\Model_Ledger');
			$new_merged_ldgr->loadBy('id',$form['new_merged_ldgr']);
			
			$removed_ledger = $this->add('xepan\accounts\Model_Ledger');
			$removed_ledger->loadBy('id',$form['ldgr_for_remove']);

			$new_merged_ldgr['name'] = $removed_ledger['name'];
			$new_merged_ldgr['group_id'] = $removed_ledger['group_id'];
			$new_merged_ldgr['related_id'] = $removed_ledger['related_id'];
			$new_merged_ldgr['ledger_type'] = $removed_ledger['ledger_type'];
			$new_merged_ldgr['LedgerDisplayName'] = $removed_ledger['LedgerDisplayName'];
			$new_merged_ldgr['OpeningBalanceCr'] = $removed_ledger['OpeningBalanceCr'];
			$new_merged_ldgr['OpeningBalanceDr'] = $removed_ledger['OpeningBalanceDr'];
			$new_merged_ldgr->save();
		}
	}
}