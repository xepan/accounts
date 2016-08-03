<?php

namespace xepan\accounts;

class page_chartofaccount extends \xepan\base\Page{
	public $title = "Chart of Accounts";
	function init()
	{
		parent::init();

		$balance_model = $this->add('xepan\accounts\Model_BalanceSheet');

		$obj = [];

		foreach ($balance_model as $bm) {
			$obj['name'] = $bm['name'];
			foreach ($bm->ref('xepan\accounts\Group')->addCondition('parent_group_id',null) as $root_groups) {
				if(!isset($obj['groups'])) $obj['groups']=[];
				$group_data=['name'=>$root_groups['name']];
				$group_data['groups'] = $this->getChildGroups($root_groups);
				$group_data['ledgers']=[];
				foreach ($root_groups->ref('xepan\accounts\Ledger') as $ledger) {
					$group_data['ledgers'][]= $ledger['name'];
				}
				$obj['groups'][] = $group_data;
			}
		}

		echo "<pre>";

		var_dump($obj);
		exit;

		// $this->add('View')->set($this->make_ulli([$obj]));
	}

	function getChildGroups($group){
		$t= [];
		foreach ($this->add('xepan\accounts\Model_Group')->addCondition('parent_group_id',$group->id) as $sg) {
			$t=['name'=>$sg['name']];
			$t['groups'] = $this->getChildGroups($sg);
			$t['ledgers']=[];
			foreach ($sg->ref('xepan\accounts\Ledger') as $ledger) {
				$t['ledgers'][]= $ledger['name'];
			}
		}
		return $t;
	}

	function make_ulli($array){
	    if(!is_array($array)) return '';

	    $output = '<ul>';
	    foreach($array as $item){  

	        $output .= '<li>' . $item->name;      

	        if(property_exists($item, 'childs'))
	            $output .= $this->make_ulli($item->childs);

	        $output .= '</li>';

	    }   
	    $output .= '</ul>';

	    return $output;
	}


}	