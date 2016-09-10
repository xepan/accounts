<?php
namespace xepan\accounts;
class page_cashbook extends \xepan\base\Page{
	public $title="Account CashBook";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->setLayout('view/form/casbookstatement-grid-info-form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Open Cash Book')->addClass('btn btn-primary');

		$crud=$this->add('xepan\hr\CRUD',['grid_class'=>'xepan\accounts\Grid_AccountsBase'],null,['view/cashbookstatement-grid']);
		
		$transaction_row = $this->add('xepan\accounts\Model_Transaction');
		$transaction_row->getElement('exchange_rate')->destroy();
		$transaction_r_j = $transaction_row->join('account_transaction_row.transaction_id','id');
		$transaction_r_j->addField('ledger_id');		
		$transaction_r_j->addField('exchange_rate');
		$transaction_r_j->addField('original_amount_dr','_amountDr');
		$transaction_r_j->addField('original_amount_cr','_amountCr');
		$ledger_j = $transaction_r_j->join('ledger.id','ledger_id');
		$ledger_j->addField('group_id');

		$transaction_row->addExpression('amountDr')->set($transaction_row->dsql()->expr('round(([0]*[1]),2)',[$transaction_row->getElement('original_amount_dr'),$transaction_row->getElement('exchange_rate')]));
		$transaction_row->addExpression('amountCr')->set($transaction_row->dsql()->expr('round(([0]*[1]),2)',[$transaction_row->getElement('original_amount_cr'),$transaction_row->getElement('exchange_rate')]));

		$group=$this->add('xepan\accounts\Model_Group')->load("Cash In Hand");

		$transaction_row->addCondition('group_id',$group['id']);
		
		if($_GET['from_date']){
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			$transaction_row->addCondition('created_at','>=',$_GET['from_date']);
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			$cash_account = $this->add('xepan\accounts\Model_Ledger')->load("Cash Account");
			$opening_balance = $cash_account->getOpeningBalance($_GET['from_date']);
		}else{			
			$transaction_row->addCondition('created_at','>=',$this->api->today);
			$transaction_row->addCondition('created_at','<',$this->app->nextDate($this->api->today));
			$cash_account = $this->add('xepan\accounts\Model_Ledger')->load("Cash Account");
			$opening_balance = $cash_account->getOpeningBalance($this->api->today);
		}
				
		if(($opening_balance['DR'] - $opening_balance['CR']) > 0){
			$opening_column = 'amountDr';
			$opening_amount = $opening_balance['DR'] - $opening_balance['CR'];
			$opening_narration = "To Opening balance";
			$opening_side = 'DR';
		}else{
			$opening_column = 'amountCr';
			$opening_amount = $opening_balance['CR'] - $opening_balance['DR'];
			$opening_narration = "By Opening balance";
			$opening_side = 'CR';
		}

		$crud->grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
		$crud->grid->addCurrentBalanceInEachRow();

		$crud->grid->setModel($transaction_row,['voucher_no','transaction_type','created_at','Narration','account','amountDr','amountCr','root_group_name']);
		$crud->grid->addSno();
		$crud->grid->removeColumn('account');

		$crud->grid->addMethod('format_transaction_type',function($g,$f){
			if($g->model->customer()){
				$g->current_row_html[$f]=$g->model['transaction_type']." :: ".$g->model->customer()->get('organization_name');
			}else
			$g->current_row_html[$f]=$g->model['transaction_type'];
		});
		$crud->grid->addFormatter('transaction_type','transaction_type');

		if($form->isSubmitted()){
			$crud->grid->js()->reload(['from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0])->execute();
		}
	}
}