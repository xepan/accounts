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

		$group_path_variable = "";
		switch ($this['list_of']) {
			case 'Ledger':
				$list_model = $this->add('xepan\accounts\Model_BSLedger');
				$group_path_variable = "group_path";
				break;
	// 		case 'Group':
	// 			$list_model = $this->add('xepan\accounts\Model_BSGroup');
	// 			$group_path_variable = "path"
	// 			break;
	// 		case 'Transaction':
	// 			$list_model = $this->add('xepan\accounts\Model_Transaction');
	// 			break;
		}

		switch ($this['under']) {
			case 'Group':
				$group_model = $this->add('xepan\accounts\Model_Group')->load($this['group_id']);
				$list_model->addCondition('group_id',$group_model->id);
				if(isset($group_path_variable) AND $group_path_variable)
					$list_model->addCondition($group_path_variable,'like',$group_model['path']."%");
				break;
	// 		case 'GroupOnly':
	// 			$group_model = $this->add('xepan\accounts\Model_Group')->load($this['group_id']);
	// 			$list_model->addCondition('group_id',$group_model->id);
	// 			break;
	// 		case 'Head':
	// 			$list_model->addCondition('head_id',$); 

	// 			break;
	// 		case 'Ledger':

	// 			break;
		}		
		return $list_model;
	}
	
}