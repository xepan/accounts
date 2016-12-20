<?php

namespace xepan\accounts;

class page_salaryledgerassociation extends \xepan\base\Page{
	public $title="Salary Ledger Association";
	function init(){
		parent::init();

		$sal_ledg_assoc_m=$this->add('xepan\accounts\Model_SalaryLedgerAssociation');

		$crud=$this->add('xepan\hr\CRUD',
						null,
						null,
						['view\grid\salary-association']
					);

		$crud->setModel($sal_ledg_assoc_m);
		$crud->grid->addPaginator(10);
		
		$frm=$crud->grid->addQuickSearch(['salary']);
	}

}