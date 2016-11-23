<?php
namespace xepan\accounts;
class page_accountupdate extends \xepan\base\Page {
	
	public $title="Update Account";
	
	function init(){
		parent::init();

		// $ledger_id= $this->api->stickyGET('ledger_id')?:0;
		$ledger = $this->add('xepan\accounts\Model_Ledger');
		
		$f = $this->add('Form');
		$f->addField('Dropdown','Ledger')->setModel($ledger);
		$f->addSubmit('UpdateLedgerName')->addClass('btn btn-primary');
		if($f->isSubmitted()){
			if($f['Ledger']){
				$ledger->load($f['Ledger']);
				$contact_mdl = $this->add('xepan\base\Model_Contact');
				$contact_mdl->addCondition('name',$ledger['name']);
				if($ledger['contact_id'])
					$contact_mdl->load($ledger['contact_id']);
				else
					$contact_mdl->tryLoadAny();

				$ledger['name'] = $contact_mdl['unique_name'];
				$ledger->save();
			}
			$f->js()->univ()->successMessage('Ledger Updaed Successfully')->execute();
		}
	}
}