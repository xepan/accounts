<?php

namespace xepan\accounts;

class page_transactionwidget extends \Page{

	function page_ledger(){

		$data = ['name1','name2'];
		echo json_encode($data);
		exit;
	}
}