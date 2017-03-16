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
		$transaction_data = json_decode($transaction_data,true);
		
		// echo"<pre>";
		// print_r($transaction_data);
		// echo "</pre>";
		// exit;

        $transactions=[];
        $total_amount=[];
        $related_transaction_id = null;

        foreach ($transaction_data as $transaction) {
            $transactions[] = $new_transaction = $this->add('xepan\accounts\Model_Transaction');
            $new_transaction->createNewTransaction($transaction['type'],null,$transaction['date'],$transaction['narration'],$transaction['currency'],$transaction['exchange_rate'],null,null,$transaction['entry_template_transaction_id'], $transaction['entry_template_transaction_id']);
            $total_amount[$transaction['type']] = 0;
            
            if($transaction['editing_transaction_id']){
        		$transaction_row_m = $this->add('xepan\accounts\Model_TransactionRow');
        		$transaction_row_m->addCondition('transaction_id',$transaction['editing_transaction_id']);
        		$transaction_row_m->deleteAll();
        	}
        	
            foreach ($transaction['rows'] as $code => $row) {
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

            if($total_amount[$transaction['type']] > 0)
                $new_transaction->execute();
        }               
	}
}