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
			$data = [];
			$rg= $this->add('xepan\accounts\Model_Group')->addCondition('balance_sheet_id',$bm->id);
			$head_sum = 0;
			foreach ($rg->addCondition('parent_group_id',null) as $root_groups) {
				$gb = $root_groups->getBalance();
				$head_sum += $gb;
				$gb = ABS($gb) . ($gb>0 ? ' Dr':' Cr');
				$group_data=['name'=>'<b>'.$root_groups['name'].' => '.$gb.'</b>'];
				$group_data['groups'] = $this->getChildGroups($root_groups);
				$group_data['ledgers']=[];
				foreach ($root_groups->ref('xepan\accounts\Ledger') as $ledger) {
					$group_data['ledgers'][]= ['name'=>'<i>'.$ledger['name'].' => '.$ledger['balance'].'</i>'];
				}
				$data['groups'][] = $group_data;
			}

			$head_sum = ABS($head_sum) . ($head_sum>0 ? ' Dr':' Cr');
			$data['name']='<span class="label label-primary">'.$bm['name'].' => '. $head_sum .'</span> <small class="label label-primary">'.$bm['report_name'].'</small>';
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
			$gb = $sg->getBalance();
			$gb = ABS($gb) . ($gb>0 ? ' Dr':' Cr');
			$t = ['name'=>'<b>'.$sg['name'].' => '.$gb.'</b>'];
			$t['groups'] = $this->getChildGroups($sg);
			$t['ledgers']=[];
			foreach ($sg->ref('xepan\accounts\Ledger') as $ledger) {
				$t['ledgers'][]= ['name'=>'<i>'.$ledger['name'].' => '.$ledger['balance'].'</i>'];
			}
			$data[] = $t;
		}
		return $data;
	}

	function make_ulli($array){
	    if(!is_array($array)) return '';
	    // echo "working on ";
	    // var_dump($array);
	    $output = '<ul>';
	    foreach($array as $item){
	    	// if(!isset($item['name'])){
	    		// var_dump($item);
	    		// continue;
	    	// }

	        $output .= '<li>' . $item['name'];
	        // echo $item['name']. ' -- ';
	        if(isset($item['groups']) && count($item['groups'])){
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