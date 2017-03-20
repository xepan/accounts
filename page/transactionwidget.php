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
	    	$group_m->tryLoadBy('name',$_GET['group']);
    		
    		if($group_m->loaded())	
    			$ledger_m->addCondition('group_id',$group_m['id']);
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

        $transactions=[];
        $total_amount=[];
        $related_transaction_id = null;

        foreach ($transaction_data as $transaction) {
        	// check if transaction is editing the remove all tr_row record
            if($transaction['editing_transaction_id']){
            	$transactions[] = $new_transaction = $this->add('xepan\accounts\Model_Transaction')->load($transaction['editing_transaction_id']);
        		
        		// delete rows
        		$transaction_row_m = $this->add('xepan\accounts\Model_TransactionRow');
        		$transaction_row_m->addCondition('transaction_id',$transaction['editing_transaction_id']);
        		$transaction_row_m->deleteAll();
        	}else{	
	            $transactions[] = $new_transaction = $this->add('xepan\accounts\Model_Transaction');
        	}

            $new_transaction->createNewTransaction($transaction['type'],null,date('Y-m-d',strtotime($transaction['transaction_date'])),$transaction['narration'],$transaction['currency'],$transaction['exchange_rate'],null,null,null,$transaction['entry_template_id']);
            $total_amount[$transaction['type']] = 0;          
        	
            foreach ($transaction['rows'] as $index => $row) {
            	$code = $row['data-code'];

            	if($row['currency'] === 'undefined' OR $row['currency'] == null OR $row['currency'] == 0)
            		$currency_id = $this->app->epan->default_currency;
            	
            	if($row['exchange_rate'] === 'undefined' OR $row['exchange_rate'] == null OR $row['exchange_rate'] == 0)
            		$exchange_rate = 1.00;

                if(strtolower($row['data-side'])=='dr'){
                    $new_transaction->addDebitLedger($row['data-ledger'],$row['data-amount'],$currency_id,$exchange_rate,$remark=null,$code);
                    $total_amount[$transaction['type']] += $row['data-amount']* $exchange_rate;
                }else{
                    $new_transaction->addCreditLedger($row['data-ledger'],$row['data-amount'],$currency_id,$exchange_rate,$remark=null,$code);
                }
            }

            if($total_amount[$transaction['type']] > 0){
                $new_transaction->execute();

                if(!$related_transaction_id){
                    $related_transaction_id = $new_transaction->id;
                }

                $new_transaction['updated_at'] = $this->app->now;
                $new_transaction['related_transaction_id'] = $related_transaction_id;
                $new_transaction->save();                
            }
        }

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