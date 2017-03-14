<?php


namespace xepan\accounts;


class Model_EntryTemplateTransaction extends \xepan\base\Model_Table{
	public $table = "custom_account_entries_templates_transactions";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\EntryTemplate','template_id');
		$this->addField('name');
		$this->addField('type');
		$this->hasMany('xepan\accounts\EntryTemplateTransactionRow','template_transaction_id');
		
		$this->addExpression('is_system_default')->set($this->refSQL('template_id')->fieldQuery('is_system_default'));

		$this->addHook('beforeDelete',function($m){
			$m->ref('xepan\accounts\EntryTemplateTransactionRow')->each(function($m1){
				$m1->delete();
			});
		});

		$this->is([
			'type|required'
			]);
		$this->setOrder('id');

		$this->addHook('beforeDelete',function($m){
			$m->ref('xepan\accounts\EntryTemplateTransactionRow')->each(function($m1){
				$m1->delete();
			});
		});

		
	}

	function getTransactionAndRowData(&$prefilled_data = []){
		if(!$this->loaded()) throw new \Exception("model entry templte transaction must loaded");
		
		if(!is_array($prefilled_data)) throw new \Exception("must pass emtry array or array of prefilled data");

		$trans_id = $this->id;
		$entry_tran_data = [];
		$entry_tran_data['entry_template_transaction_id'] = $this['id'];
		$entry_tran_data['name'] = $this['name'];
		$entry_tran_data['type'] = $this['type'];
		$entry_tran_data['is_system_default'] = $this['is_system_default'];
			
		$entry_tran_data['rows'] = [];
		foreach ($this->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
			$entry_tran_data['rows'][$row->id] = [];
			$entry_tran_data['rows'][$row->id]['title'] = $row['title'];
			$entry_tran_data['rows'][$row->id]['side'] = $row['side'];
			$entry_tran_data['rows'][$row->id]['group'] = $row['group'];
			$entry_tran_data['rows'][$row->id]['balance_sheet'] = $row['balance_sheet'];
			$entry_tran_data['rows'][$row->id]['parent_group'] = $row['parent_group'];
			$entry_tran_data['rows'][$row->id]['ledger'] = $row['ledger'];
			$entry_tran_data['rows'][$row->id]['ledger_type'] = $row['ledger_type'];
			$entry_tran_data['rows'][$row->id]['is_ledger_changable'] = $row['is_ledger_changable'];
			$entry_tran_data['rows'][$row->id]['is_allow_add_ledger'] = $row['is_allow_add_ledger'];
			$entry_tran_data['rows'][$row->id]['is_include_currency'] = $row['is_include_currency'];
			$entry_tran_data['rows'][$row->id]['code'] = $row['code'];
			$entry_tran_data['rows'][$row->id]['entry_template_id'] = $row['entry_template_id'];
			
			// check for pre filled values
			if(count($prefilled_data) AND isset($prefilled_data[$this['type']]) ){
				// $trans_type_array = $prefilled_data[$this['type']];
				foreach ($prefilled_data[$this['type']] as $tr_row_id => $row_data) {
					if($row_data['code'] != $row['code']) continue;

					foreach ($row_data as $key => $value) {
						$entry_tran_data['rows'][$row->id][$key] = $value;
					}
					
					unset($prefilled_data[$this['type']][$tr_row_id]);
					break;				
				}
			}			
		}

		if(count($prefilled_data[$this['type']])){
			foreach ($prefilled_data[$this['type']] as $tran_row_id => $data_array) {
				$key = "extra_".$tran_row_id;

				foreach ($data_array as $field_name => $value) {
					$entry_tran_data['rows'][$key][$field_name] = $value;
				}
			}

		}else{
			unset($prefilled_data[$this['type']]);
		}
		
		// echo "<pre>";
		// print_r($prefilled_data);
		// echo "</pre>";
		return $entry_tran_data;

	}
}
