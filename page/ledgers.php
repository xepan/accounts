<?php
namespace xepan\accounts;
class page_ledgers extends \Page{
	public $title="Account Ledgers";
	function init(){
		parent::init();

		$ledger = $this->add('xepan\accounts\Model_Ledger');
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/ledger-grid']);
		$crud->setModel($ledger/*,['name','group_id','OpeningBalanceCr','OpeningBalanceDr'],['name','group','parent_group','root_group','CurrentBalanceCr','CurrentBalanceDr','OpeningBalanceCr','OpeningBalanceDr','balance']*/);

		if(!$crud->isEditing()){
			$ledger->setOrder('name','asc');
			// $crud->grid->add_sno();
			$crud->grid->addQuickSearch('name');
			// $crud->grid->removeColumn('CurrentBalanceDr');
			// $crud->grid->removeColumn('CurrentBalanceCr');
			// $crud->grid->removeColumn('OpeningBalanceCr');
			// $crud->grid->removeColumn('OpeningBalanceDr');
		}

	}
}