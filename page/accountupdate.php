<?php
namespace xepan\accounts;
class page_accountupdate extends \xepan\base\Page {
	
	public $title="Update Account";
	
	function init(){
		parent::init();

		$f = $this->add('Form');
		$f->add('View')->setElement('b')->set('Click On Button To Update All Contact Type Ledger');

		$f->addSubmit('UpdateLedgerName')->addClass('btn btn-primary');
		if($f->isSubmitted()){
				$ledger = $this->add('xepan\accounts\Model_Ledger');
				foreach ($ledger as $contact_ledger) {
					$contact_mdl = $this->add('xepan\base\Model_Contact');
					if($contact_ledger['contact_id']){
						$contact_mdl->load($contact_ledger['contact_id']);
						if($contact_ledger['name'] != $contact_mdl['unique_name']){
							$contact_ledger['name'] = $contact_mdl['unique_name'];
							$contact_ledger->save();
						}
					}
				}
			$f->js()->univ()->successMessage('All Ledger Updaed Successfully')->execute();
		}
	}
}