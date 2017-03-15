<?php

namespace xepan\accounts;

class page_transactionwidget extends \Page{

	function page_ledger(){

		$data = ['name1','name2'];
		echo json_encode($data);
		exit;
	}

	function page_save(){

		$transaction_data = $_POST['transaction_data'];
		$transaction_data = json_decode($transaction_data);
		
		exit;
	}

}