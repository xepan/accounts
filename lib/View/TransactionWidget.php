<?php

namespace xepan\accounts;

class View_TransactionWidget extends \View {

	public $entry_tran_data = [];
	public $currency_list = [];
	function init(){
		parent::init();
		
		$currency_list = $this->add('xepan\accounts\Model_Currency')->addCondition('status','Active')->getRows();
		
		foreach ($currency_list as $key => $value) {
			$this->currency_list[$value['id']] = $value;	
		}
	}

	function setModel($model){
		$prefilled_data = [];
		if($model instanceof \xepan\accounts\Model_Transaction){

            if($model['related_transaction_id']){
                $model  = $model->newInstance()->load($model['related_transaction_id']);
            }
            $transaction_to_edit = $model;

            // get prefilled data
			if($transaction_to_edit->loaded()){
				$prefilled_data = $transaction_to_edit->populatePreFilledValues();
			}

            if(!$model['transaction_template_id']){
                $this->owner->add('View')->set('It is related document');
                throw $this->exception('','StopInit');
            }

            $transaction_m = $transaction_to_edit->ref('transaction_template_id');
            $transactions = $transaction_m->ref('xepan\accounts\EntryTemplateTransaction');
            $related_id = $transaction_to_edit['related_id'];
            $related_type = $transaction_to_edit['related_type'];
            $date = $transaction_to_edit['created_at'];
            $narration= $transaction_to_edit['Narration'];

        }elseif($model instanceof \xepan\accounts\Model_EntryTemplate){
            $transaction_m = $model;
            $transactions = $model->ref('xepan\accounts\EntryTemplateTransaction');
            $transaction_to_edit=null;
        }else{
            throw $this->exception('Only Loaded, Transaction and Entry Template Models permitted')
                        ->addMoreInfo('transaction_provided',get_class($transaction_m));
        }

		$entry_tran_data = [];
		foreach ($transactions as $trans) {
			$t_and_r_data = $trans->getTransactionAndRowData($prefilled_data);
			if($transaction_to_edit instanceof \xepan\accounts\Model_Transaction && $transaction_to_edit->loaded()){
				$t_and_r_data['editing_transaction_id'] = $transaction_to_edit->id;
				$t_and_r_data['narration'] = $transaction_to_edit['Narration'];
				$t_and_r_data['transaction_date'] = $transaction_to_edit['created_at'];
			}
			$entry_tran_data[$trans->id] = $t_and_r_data;
		}

		$this->entry_tran_data = $entry_tran_data;

		parent::setModel($model);
	}

	function render(){

		// echo "<pre>";
		// print_r($this->entry_tran_data);
		// echo "</pre>";
		// exit;
		$this->js(true)->_load('jquery.livequery');

		$json_data = json_encode($this->entry_tran_data);
		$currency_list = json_encode($this->currency_list);

		// echo "<pre>";
		// print_r($this->currency_list);
		// echo "</pre>";
		// exit;
		$this->js(true)
					->_load('xepan_accounts_transaction_executer')
					->transaction_executer([
										'entry_template'=>$json_data,'transaction_name',
										'currency_list'=>$currency_list
										]);
		parent::render();
	}

	// function defaultTemplate(){
	// 	return ['view/transactionwidget'];
	// }


}