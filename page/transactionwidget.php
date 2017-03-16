<?php

namespace xepan\accounts;

class page_transactionwidget extends \Page{

	function page_ledger(){
	    $ledger_m = $this->add('xepan\accounts\Model_Ledger');

	    if(!$_GET['is_ledger_changable'] && $_GET['ledger']){
	    	if(is_numeric ($_GET['ledger']))
	    		$ledger_m->load($_GET['ledger']);
	    	else
	    		$ledger_m->loadBy('name',$_GET['ledger']);
	    }
	    
	    if($_GET['group']){
	    	$group_m = $this->add('xepan\accounts\Model_Group');
	    	$group_m->addCondition('name',$_GET['group']);
    		
    		if($group_m->loaded())	
    			$ledger_m->addCondition('group_id',$group_m['id']);
	    }

		$data = [];
		foreach ($ledger_m as $ledger) {
			$data [$ledger['id']] = $ledger['name'];	
		}

		echo json_encode($data);
		exit;
	}

	function page_save(){
		$transaction_data = $_POST['transaction_data'];
		$transaction_data = json_decode($transaction_data);
		exit;
	}
}