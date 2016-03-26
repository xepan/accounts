<?php
namespace xepan\accounts;
class page_transactionrow extends \Page{
	public $title="Account Transaction Row";
	function init(){
		parent::init();

	$transaction_row = $this->add('xepan\accounts\Model_TransactionRow');
		$crud = $this->add('xepan\base\CRUD');
		$crud->setModel($transaction_row);
	}
}