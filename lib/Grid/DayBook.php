<?php

namespace xepan\accounts;

class Grid_DayBook extends \xepan\base\Grid{
	public $voucher_no=0;

	function setModel($model,$fields=array()){
		parent::setModel($model,$fields);
		$this->addFormatter('voucher_no','Wrap');
		$this->addFormatter('ledger','Wrap');
		// $this->addFormatter('forcedelete','password');
	}

	function format_voucherNo($field){
		if($this->voucher_no==$this->model->get('voucher_no')){
			$this->current_row[$field]=$this->model->get('Narration');
		}
		else{
			$this->voucher_no=$this->model->get('voucher_no');
			$this->current_row[$field] = $this->voucher_no . ' [ '. $this->model['transaction_type'] .' ]';
		}
		parent::format_voucherNo($field);
	}

	function formatRow(){		
		$this->current_row_html['forcedelete']="-";
		parent::formatRow();
	}


}