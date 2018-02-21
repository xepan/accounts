<?php


namespace xepan\accounts;

class page_audit extends \xepan\base\Page {
	public $title= "Audit Accounts DB";

	function init(){
		parent::init();

		$this->DrCrMisMatch();
		$this->groupsBalance();

	}

	function DrCrMisMatch(){
		
			
		$m = $this->add('xepan\accounts\Model_Transaction');
		$q= $m->dsql();

		$m->addCondition($q->expr('[0]<>[1]',[$m->getElement('cr_sum'),$m->getElement('cr_sum')]));

		$grid = $this->add('Grid');
		$grid->setModel($m);

		$grid->add('H2',null,'grid_buttons')->set('Group Balances');
	}

	function groupsBalance(){
		$m = $this->add('xepan\accounts\Model_Group');

		$m->add('misc/Field_Callback','balance')->set(function($m){
 			$t = $m->getBalance();
 			if(!$t) return '';
 			return abs($t) . ' '. ($t<0?'Dr':'Cr');
		});


		$grid = $this->add('Grid');
		$grid->setModel($m);

		$grid->add('H2',null,'grid_buttons')->set('Groups with Balances');
	}
}