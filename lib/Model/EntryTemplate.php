<?php

namespace xepan\accounts;

class Model_EntryTemplate extends \xepan\base\Model_Table{
	public $table= "custom_account_entries_templates";
	public $acl=true;
	public $acl_type = 'EntryTemplate';

	public $form = null;

	function init(){
		parent::init();

		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue(@$this->app->employee->id);
		

		$this->addField('name');
		$this->addField('detail')->type('text');
		$this->addField('unique_trnasaction_template_code')->PlaceHolder('If it is default for system, Insert Unique Default Template Transaction Code')->caption('Code')->hint('Place your unique template transaction code ');
		$this->addField('is_system_default')->type('boolean')->defaultValue(false);
		$this->addField('is_favourite_menu_lister')->type('boolean')->defaultValue(false);
		// $this->addField('is_merge_transaction')->type('boolean');
		$this->hasMany('xepan\accounts\EntryTemplateTransaction','template_id');
		$this->hasMany('xepan\accounts\Transaction','transaction_template_id');

		$this->addHook('beforeDelete',function($m){
			$m->ref('xepan\accounts\EntryTemplateTransaction')->each(function($m1){
				$m1->delete();
			});
		});

		$this->is([
			'unique_trnasaction_template_code|required|unique',
			]);
	}

	
	function manageForm($page, $related_id=null, $related_type=null, $pre_filled_values=[],$default_narration=null){
		// Pre filled values array format
		// $pre_filled_values=[
				// 'transaction_number'=>['tranasction_row_code'=>['ledger'=>$ledger,'amount'=>$amount,'currency'=>$currency,'exchange_rate'=>$exchange_rate]],
		// ]

		$this->form  = $form = $page->add('xepan\accounts\Form_EntryRunner');
		$form->addHook('afterExecute',function($et,$transaction,$total_amount,$row_data){
			if($this->app->db->inTransaction()) $this->app->db->commit();
			$this->hook('afterExecute',[$transaction,$total_amount,$row_data]);
		});

		$form->setModel($this,$related_id, $related_type, $pre_filled_values,$default_narration);
		
	}


/*
$entry_template->executeSave([
					$transaction->id => [
						// 'entry_template_transaction_id'=>$transaction->id,
						'entry_template_id'=>$entry_template->id,
						// 'name'=>$transaction['name'],
						'type'=>$transaction['type'],
						'transaction_date'=>$this->app->now,
						'narration'=> $this['narration'],
						'currency'=>$this->app->epan->default_currency->id,
						'related_id'=>$this->id,
						'related_type'=>'xavoc\ispmanager\Model_PaymentTransaction',
						'exchange_rate'=>1,
						'rows'=>[
									[
										'data-code'=>'cash',
										'currency'=>$this->app->epan->default_currency->id,
										'exchange_rate'=>1,
										'data-side'=>'DR',
										'data-ledger'=> $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('name','Cash Account')->get('id'),
										'data-amount'=> $this['amount']
									],
									[
										'data-code'=>'party',
										'currency'=>$this->app->epan->default_currency->id,
										'exchange_rate'=>1,
										'data-side'=>'CR',
										'data-ledger'=> $this->ref('contact_id')->ledger()->get('id'),
										'data-amount'=>$this['amount']
									]
								]

					]
				]
			);

*/
	function executeSave($transaction_data){
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

            $new_transaction->createNewTransaction($transaction['type'],null,date('Y-m-d',strtotime($transaction['transaction_date'])),$transaction['narration'],$transaction['currency'],$transaction['exchange_rate'],isset($transaction['related_id'])?$transaction['related_id']:null,isset($transaction['related_type'])?$transaction['related_type']:null,null,$transaction['entry_template_id']);
            $total_amount[$transaction['type']] = 0;          
        	
            foreach ($transaction['rows'] as $index => $row) {
            	$code = $row['data-code'];

            	$currency_id = $row['currency'];
            	if($row['currency'] === 'undefined' OR $row['currency'] == null OR $row['currency'] == 0)
            		$currency_id = $this->app->epan->default_currency;
            	
            	$exchange_rate = $row['exchange_rate'];
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
	}

	function exportJson(){
		$data = $this->get();
		unset($data['id']);

		$data['transactions']=[];
		foreach ($this->ref('xepan\accounts\EntryTemplateTransaction') as $transaction) {
			$transaction_data = $transaction->get();
			unset($transaction_data['id']);
			unset($transaction_data['template_id']);
			unset($transaction_data['template']);

			$transaction_data['rows']=[];
			foreach ($transaction->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
				$row_data = $row->get();
				unset($row_data['id']);
				unset($row_data['template_transaction_id']);
				unset($row_data['template_transaction_id']);
				$transaction_data['rows'][] = $row_data;
			}

			$data['transactions'][] = $transaction_data;
		}
		return json_encode($data);
	}

	function importJson($json,$as_system=false){
		$data=json_decode($json,true);
		// echo "<pre>";
		// // print_r($data['transactions'][0]['rows']);
		// print_r($data['transactions'][0]);
		// echo "</pre>";
		$temp=$this->add('xepan\accounts\Model_EntryTemplate');
		$temp['name']=$data['name'];
		$temp['detail']=$data['detail'];
		$temp['unique_trnasaction_template_code']=$data['unique_trnasaction_template_code'];
		$temp['is_system_default']=$as_system?:$data['is_system_default'];
		$temp['is_favourite_menu_lister']=$data['is_favourite_menu_lister'];
		$temp['is_merge_transaction']=$data['is_merge_transaction'];
		$temp->save();

		foreach ($data['transactions'] as $tr) {
			$transaction=$this->add('xepan\accounts\Model_EntryTemplateTransaction');
			$transaction['template_id']=$temp->id;
			$transaction['name']=$tr['name'];
			$transaction['type']=$tr['type'];
			$transaction->save();

			foreach ($tr['rows'] as  $tr_row) {
				$row=$this->add('xepan\accounts\Model_EntryTemplateTransactionRow');
				$row['template_transaction_id']=$transaction->id;
				$row['code']=$tr_row['code'];
				$row['title']=$tr_row['title'];
				$row['side']=$tr_row['side'];
				$row['group']=$tr_row['group'];
				$row['balance_sheet']=$tr_row['balance_sheet'];
				$row['parent_group']=$tr_row['parent_group'];
				$row['ledger']=$tr_row['ledger'];
				$row['ledger_type']=$tr_row['ledger_type'];
				$row['is_ledger_changable']=$tr_row['is_ledger_changable'];
				$row['is_allow_add_ledger']=$tr_row['is_allow_add_ledger'];
				$row['is_include_currency']=$tr_row['is_include_currency'];
				$row->save();
			}
		}


	}
}