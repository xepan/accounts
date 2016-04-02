<?php
namespace xepan\accounts;
class View_Lister_LedgerStatement extends \CompleteLister{
	public $sno=1;
	public $ledger_id = 0;
	public $from_date = 0;
	function setModel($model){
		parent::setModel($model);

		if($this->ledger_id and $this->from_date){
			$opening_balance = $this->add('xepan\accounts\Model_Ledger')->load($this->ledger_id)->getOpeningBalance($this->from_date);
			$this->template->set('opening_balance_narration','Opening Balance');			
			$this->template->set('opening_balance',$opening_balance['cr']);			
		}else
			$this->template->tryDel('opening_balance_section');

	}

	function formatRow(){
		$amount  = $this->model['amountDr'] - $this->model['amountCr'];
		if($amount > 0)
			$balance = $amount." DR";
		else
			$balance = abs($amount)." CR";

		$this->current_row['s_no'] = $this->sno++;
		$this->current_row['created_date'] = date('Y-m-d', strtotime($this->model->get('created_at')));
		$this->current_row['balance'] = $balance;
		parent::formatRow();
	}

	function defaultTemplate(){
		return ['view/acstatement'];
	}
}