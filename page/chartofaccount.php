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
			$data = ['name' => $bm['name']];
			foreach ($bm->ref('xepan\accounts\Group')->addCondition('parent_group_id',null) as $root_groups) {
				$group_data=['name'=>$root_groups['name']];
				$group_data['groups'] = $this->getChildGroups($root_groups);
				$group_data['ledgers']=[];
				foreach ($root_groups->ref('xepan\accounts\Ledger') as $ledger) {
					$group_data['ledgers'][]= ['name'=>$ledger['name']];
				}
				$data['groups'][] = $group_data;
			}
			$obj[] = $data;
		}

		// echo "<pre>";
		// var_dump($obj);
		// exit;

		$this->add('View')->setHTML($this->make_ulli($obj));
	}

	function getChildGroups($group){
		$data= [];
		foreach ($this->add('xepan\accounts\Model_Group')->addCondition('parent_group_id',$group->id) as $sg) {
			$t=['name'=>$sg['name']];
			$t['groups'] = $this->getChildGroups($sg);
			$t['ledgers']=[];
			foreach ($sg->ref('xepan\accounts\Ledger') as $ledger) {
				$t['ledgers'][]= ['name'=>$ledger['name']];
			}
			$data = $t;
		}
		return $data;
	}

	function make_ulli($array){
	    if(!is_array($array)) return '';
	    // echo "working on ";
	    // var_dump($array);

	    $output = '<ul>';
	    foreach($array as $item){  

	        $output .= '<li>' . $item['name'];
	        // echo $item['name']. ' -- ';
	        if(isset($item['groups']) && count($item['groups'])){
	        	// var_dump($item['groups']);
	            $output .= $this->make_ulli($item['groups']);
	        }

	        if(isset($item['ledgers']) && count($item['ledgers']))
	            $output .= $this->make_ulli($item['ledgers']);

	        $output .= '</li>';

	    }   
	    $output .= '</ul>';

	    return $output;
	}


}	