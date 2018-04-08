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
	    
        if($_GET['term']){
            $ledger_m->addCondition('name','like','%'.$_GET['term'].'%');
        }

	    if($_GET['group']){
	    	$group_m = $this->add('xepan\accounts\Model_Group');
	    	$group_m->addCondition('name',explode(",",$_GET['group']));
    		
    		// if($group_m->loaded())	
			$ledger_m->addCondition('group_id','in',$group_m->fieldQuery('id'));
	    }

		$data = [];
		foreach ($ledger_m as $ledger) {
			$data [$ledger['id']] = [
									'id'=>$ledger->id,
									'name'=>$ledger['name'],
									'value'=>$ledger['name']
								];	
		}

		echo json_encode($data);
		exit;
	}

	function page_save(){
        // $tra_date = new \DateTime('20-March-2017');
        // echo "Rakesh = ".$tra_date->format('Y-m-d')."<br/>";
        // echo "Rakesh ".strtotime('20-March-2017');
        // exit;

		$transaction_data = $_POST['transaction_data'];
		$transaction_data = json_decode($transaction_data,true);

        $this->add('xepan\accounts\Model_EntryTemplate')->executeSave($transaction_data);        

        echo "success";
        exit;
	}

    function page_addledger(){
        $ledger_name = $_POST['ledger_name']; 
        $group_name = $_POST['group']; 

        $group_model = $this->add('xepan\accounts\Model_Group')->addCondition('name',$group_name);
        $group_model->tryLoadAny();
        if(!$group_model->loaded()){
            echo json_encode(['status'=>'failed','message'=>'group model not found']);
            exit;
        }

        $ledger_model = $this->add('xepan\accounts\Model_Ledger');
        $ledger_model->addCondition('group_id',$group_model->id);
        $ledger_model->addCondition('name',$ledger_name);
        $ledger_model->tryLoadAny();
        if($ledger_model->loaded()){
            echo json_encode(['status'=>'failed','message'=>'ledger with this name is already exist']);
            exit;
        }
            
        $ledger_model->save();

        echo json_encode(['status'=>'success','message'=>"ledger created successfully",'id'=>$ledger_model->id,'name'=>$ledger_model['name']]);
        exit;
    }

}