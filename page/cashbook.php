<?php
namespace xepan\accounts;
class page_cashbook extends \xepan\base\Page{
	public $title="Account CashBook";
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date')->validateNotNull();
		$form->addField('DatePicker','to_date')->validateNotNull();
		$form->addSubmit('Open Cash Book');

		$grid = $this->add('xepan\accounts\Grid_AccountsBase');

		$transaction_row = $this->add('xepan\accounts\Model_TransactionRow');
		$group=$this->add('xepan\accounts\Model_Group')->loadRootCashGroup();

		$transaction_row->addCondition('root_group_id',$group['id']);
		
		if($_GET['from_date']){
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
			$transaction_row->addCondition('created_at','>=',$_GET['from_date']);
			$transaction_row->addCondition('created_at','<',$this->api->nextDate($_GET['to_date']));
			$cash_account = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
			$opening_balance = $cash_account->getOpeningBalance($_GET['from_date']);
		}else{			
			$transaction_row->addCondition('created_at','>=',$this->api->today);
			$transaction_row->addCondition('created_at','<',$this->app->nextDate($this->api->today));
			// throw new \Exception($transaction_row->count()->getOne());
			// throw new \Exception($this->api->nextDate($this->api->today));
			$cash_account = $this->add('xepan\accounts\Model_Ledger')->loadDefaultCashLedger();
			$opening_balance = $cash_account->getOpeningBalance($this->api->today);
		}
		
		
		// throw new \Exception("Error Processing Request", 1);
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

		$grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
		$grid->addCurrentBalanceInEachRow();

		$grid->setModel($transaction_row,['voucher_no','transaction_type','created_at','Narration','account','amountDr','amountCr','root_group_name']);
		// $grid->addSno();
		$grid->removeColumn('account');

		$grid->addMethod('format_transaction_type',function($g,$f){
			if($g->model->transaction()->customer()){
				$g->current_row_html[$f]=$g->model['transaction_type']."::".$g->model->transaction()->customer()->get('organization_name');
			}else
			$g->current_row_html[$f]=$g->model['transaction_type'];
		});
		$grid->addFormatter('transaction_type','transaction_type');

		$js=[
		$this->js()->_selector('.atk-cells-gutter-large')->parent()->parent()->toggle(),
		$this->js()->_selector('.atk-box')->toggle(),
		$this->js()->_selector('.navbar1')->toggle(),
			// $this->js()->_selector('.atk-text-nowrap')->toggle(),
		$this->js()->_selector('.atk-form')->toggle(),
		];

		$grid->js('click',$js);

		// $grid->addTotals(array('amountCr','amountDr'));
		if($form->isSubmitted()){
			$grid->js()->reload(['from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0])->execute();
		}


	}
}