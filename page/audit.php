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
		
		$form->addField('CheckBox','update_in_account_transaction');
		$form->addField('CheckBox','update_in_account_entry_template');
		
		// $form->addField('Line','ldgr_for_remove_ledger_type');
		// $form->addField('Line','ldgr_for_remove_group_name');
		// $form->addField('Line','ldgr_for_remove_OpeningBalanceDr');
		// $form->addField('Line','ldgr_for_remove_OpeningBalanceCr');
		
		// $form->addField('Line','new_merged_ldgr_ledger_type');
		// $form->addField('Line','new_merged_ldgr_group_name');
		// $form->addField('Line','new_merged_ldgr_OpeningBalanceDr');
		// $form->addField('Line','new_merged_ldgr_OpeningBalanceCr');
	
		$ldgr_for_remove->setModel('xepan\accounts\Ledger');
		$new_merged_ldgr->setModel('xepan\accounts\Ledger');


		// $this->addExpression('CurrentBalanceDr')->set(function($m,$q){
		// $this->addExpression('CurrentBalanceCr')->set(function($m,$q){

		$form->addSubmit('Merge Ledger')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$new_merged_ldgr_mdl = $this->add('xepan\accounts\Model_Ledger');
			$new_merged_ldgr_mdl->loadBy('id',$form['new_merged_ldgr']);
			
			$removed_ledger_mdl = $this->add('xepan\accounts\Model_Ledger');
			$removed_ledger_mdl->loadBy('id',$form['ldgr_for_remove']);

			// $new_merged_ldgr_mdl['name'] = $removed_ledger_mdl['name'];
			// $new_merged_ldgr_mdl['group_id'] = $removed_ledger_mdl['group_id'];
			// $new_merged_ldgr_mdl['related_id'] = $removed_ledger_mdl['related_id'];
			// $new_merged_ldgr_mdl['ledger_type'] = $removed_ledger_mdl['ledger_type'];
			// $new_merged_ldgr_mdl['LedgerDisplayName'] = $removed_ledger_mdl['LedgerDisplayName'];
			$new_merged_ldgr_mdl['OpeningBalanceCr'] += $removed_ledger_mdl['OpeningBalanceCr'];
			$new_merged_ldgr_mdl['OpeningBalanceDr'] += $removed_ledger_mdl['OpeningBalanceDr'];
			$new_merged_ldgr_mdl->save();

			if($form['update_in_account_transaction']){
				$trans_row_mdl = $this->add('xepan\accounts\Model_TransactionRow');
				$trans_row_mdl->addCondition('ledger_id',$removed_ledger_mdl->id);
				foreach ($trans_row_mdl as $trans) {
					$trans_m = $this->add('xepan\accounts\Model_TransactionRow');
					$trans_m->loadBy('ledger_id',$trans['ledger_id']);
					$trans_m['ledger_id'] = $new_merged_ldgr_mdl->id;
					$trans_m->save();
				}
			}

			if($form['update_in_account_entry_template']){
				$acnt_entry_tmplt_row = $this->add('xepan\accounts\Model_EntryTemplateTransactionRow');
				$acnt_entry_tmplt_row->addCondition('ledger',$removed_ledger_mdl['name']);
				foreach ($acnt_entry_tmplt_row as $trns_row) {
					$trns_row_m = $this->add('xepan\accounts\Model_EntryTemplateTransactionRow');
					$trns_row_m->loadBy('ledger',$trns_row['ledger']);
					$trns_row_m['ledger'] = $new_merged_ldgr_mdl['name'];
					$trns_row_m->save();
				}
			}
			$removed_ledger_mdl->delete();
			$form->js(null,$form->js()->reload())->univ()->successMessage('Two ledger has been merged successfully')->execute();
		}
	}
}