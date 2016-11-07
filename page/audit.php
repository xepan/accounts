<?php
namespace xepan\accounts;
class page_audit extends \xepan\base\Page {
	
	public $title="Merge Ledger";
	
	function init(){
		parent::init();

		$rem_ldgr_id = $this->app->stickyGET('rem_ldgr_id');
		$new_ldgr_id = $this->app->stickyGET('new_ldgr_id');

		$ldgr_view = $this->add('View');

		$form = $ldgr_view->add('Form',null,null);
		$form->setLayout('view/form/act-merge-form');

		$ldgr_for_remove = $form->addField('Dropdown','ldgr_for_remove')->setEmptyText('Select a ledger')->validateNotNull();
		$new_merged_ldgr = $form->addField('Dropdown','new_merged_ldgr')->setEmptyText('Select a ledger')->validateNotNull();
		
		$ldgr_for_remove->setModel('xepan\accounts\Ledger');
		$new_merged_ldgr->setModel('xepan\accounts\Ledger');
		
		$ldgr_for_remove->js('change',$ldgr_view->js()->reload(['rem_ldgr_id'=>$ldgr_for_remove->js()->val()]));
		$new_merged_ldgr->js('change',$ldgr_view->js()->reload(['new_ldgr_id'=>$new_merged_ldgr->js()->val()]));
		
		$form->addField('CheckBox','update_in_account_transaction');
		$form->addField('CheckBox','update_in_account_entry_template');
		
		$rem_ldgr_name = $form->addField('Readonly','ldgr_for_remove_ledger_name');
		$rem_ldgr_type = $form->addField('Readonly','ldgr_for_remove_ledger_type');
		$rem_ldgr_group = $form->addField('Readonly','ldgr_for_remove_group_name');
		$rem_ldgr_op_bal_dr = $form->addField('Readonly','ldgr_for_remove_OpeningBalanceDr');
		$rem_ldgr_op_bal_cr = $form->addField('Readonly','ldgr_for_remove_OpeningBalanceCr');
		$rem_ldgr_contact = $form->addField('Readonly','ldgr_for_remove_contact');

		if($rem_ldgr_id){
			$rem_ldgr_mdl = $this->add('xepan\accounts\Model_Ledger');
			$rem_ldgr_mdl->load($rem_ldgr_id);

			$rem_ldgr_name->set($rem_ldgr_mdl['name']);
			$rem_ldgr_type->set($rem_ldgr_mdl['ledger_type']);
			$rem_ldgr_group->set($rem_ldgr_mdl['group']);
			$rem_ldgr_op_bal_dr->set($rem_ldgr_mdl['OpeningBalanceDr']);
			$rem_ldgr_op_bal_cr->set($rem_ldgr_mdl['OpeningBalanceCr']);
			$rem_ldgr_contact->set($rem_ldgr_mdl['contact_id']);
		}

		$new_ldgr_name = $form->addField('Readonly','new_merged_ldgr_ledger_name');
		$rew_ldgr_type = $form->addField('Readonly','new_merged_ldgr_ledger_type');
		$new_ldgr_group = $form->addField('Readonly','new_merged_ldgr_group_name');
		$new_ldgr_op_bal_dr = $form->addField('Readonly','new_merged_ldgr_OpeningBalanceDr');
		$new_ldgr_op_bal_cr = $form->addField('Readonly','new_merged_ldgr_OpeningBalanceCr');
		$new_ldgr_contact = $form->addField('Readonly','new_merged_ldgr_contact');

		if($new_ldgr_id){
			$new_ldgr_mdl = $this->add('xepan\accounts\Model_Ledger');
			$new_ldgr_mdl->load($new_ldgr_id);

			$new_ldgr_name->set($new_ldgr_mdl['name']);
			$rew_ldgr_type->set($new_ldgr_mdl['ledger_type']);
			$new_ldgr_group->set($new_ldgr_mdl['group']);
			$new_ldgr_op_bal_dr->set($new_ldgr_mdl['OpeningBalanceDr']);
			$new_ldgr_op_bal_cr->set($new_ldgr_mdl['OpeningBalanceCr']);
			$new_ldgr_contact->set($new_ldgr_mdl['contact_id']);
		}

		$form->addSubmit('Merge Ledger')->addClass('btn btn-primary');

		if($form->isSubmitted()){
			$new_merged_ldgr_mdl = $this->add('xepan\accounts\Model_Ledger');
			$new_merged_ldgr_mdl->loadBy('id',$new_ldgr_id);
			// $new_merged_ldgr_mdl->loadBy('id',$form['new_merged_ldgr']);
			
			$removed_ledger_mdl = $this->add('xepan\accounts\Model_Ledger');
			$removed_ledger_mdl->loadBy('id',$rem_ldgr_id);
			// $removed_ledger_mdl->loadBy('id',$form['ldgr_for_remove']);

			if($removed_ledger_mdl['contact_id'])
				$new_merged_ldgr_mdl['contact_id'] = $removed_ledger_mdl['contact_id'];

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