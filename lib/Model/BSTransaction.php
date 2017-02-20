<?php



namespace xepan\accounts;


class Model_BSTransaction extends Model_TransactionRow {
	
	public $from_date = '1970-01-01';
	public $to_date = '2017-01-01';
	public $p_and_l=false;

	function init(){
		parent::init();

	}
}