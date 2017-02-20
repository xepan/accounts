<?php
namespace xepan\accounts;

class Model_ReportLoop extends \xepan\accounts\Model_ReportFunction{
	
	function init(){
		parent::init();

		$this->addCondition('list_of','<>',null);
	}

	function getListModel(){
		if(!$this->loaded())
			throw new \Exception("Error Processing Request");

		$group_path_field = "";
		switch ($this['list_of']) {
			case 'Ledger':
				$list_model = $this->add('xepan\accounts\Model_BSLedger');
				$group_path_field = "group_path";
				$head_field = "balance_sheet_id";
				$group_field = "group_id";
				break;
			case 'Group':
				$list_model = $this->add('xepan\accounts\Model_BSGroup');
				$group_path_field = "path";
				$head_field = "balance_sheet_id";
				$group_field = "id";
				break;
			case 'Transaction':
				$list_model = $this->add('xepan\accounts\Model_BSTransaction');
				$group_path_field = "group_path";
				$head_field = "balance_sheet_id";
				$group_field = "group_id";
				break;
		}

		switch ($this['under']) {
			case 'Group':
				$group_model = $this->add('xepan\accounts\Model_Group')->load($this['group_id']);
				$list_model->addCondition($group_field,$group_model->id);
				if(isset($group_path_field) AND $group_path_field)
					$list_model->addCondition($group_path_field,'like',$group_model['path']."%");
				break;
			case 'GroupOnly':
				$group_model = $this->add('xepan\accounts\Model_Group')->load($this['group_id']);
				$list_model->addCondition($group_field,$group_model->id);
				break;
			case 'Head':
				$list_model->addCondition($head_field,$this['head_id']);
				break;

			case 'Ledger':
				// only used if list of == Transaction
				if($this['list_of'] == "Transaction")
					$list_model->addCondition('ledger_id',$this['ledger_id']);
				break;
		}		
		return $list_model;
	}

}