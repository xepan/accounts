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

		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No cash statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false
				],null,['view/cashbookstatement-grid']);

		$transaction = $this->add('xepan\accounts\Model_Transaction');
		$transaction->getElement('exchange_rate')->destroy();
		$transaction_r_j = $transaction->join('account_transaction_row.transaction_id','id');
		$transaction_r_j->addField('ledger_id');		
		$transaction_r_j->addField('exchange_rate');
		$transaction_r_j->addField('original_amount_dr','_amountDr');
		$transaction_r_j->addField('original_amount_cr','_amountCr');
		$ledger_j = $transaction_r_j->join('ledger.id','ledger_id');
		$ledger_j->addField('group_id');

		$transaction->addExpression('group_path')->set($this->add('xepan\accounts\Model_Group')->addCondition('id',$transaction->getElement('group_id'))->fieldQuery('path'));
		$transaction->addExpression('amountDr')->set($transaction->dsql()->expr('round(([0]*[1]),2)',[$transaction->getElement('original_amount_dr'),$transaction->getElement('exchange_rate')]));
		$transaction->addExpression('amountCr')->set($transaction->dsql()->expr('round(([0]*[1]),2)',[$transaction->getElement('original_amount_cr'),$transaction->getElement('exchange_rate')]));

		$group=$this->add('xepan\accounts\Model_Group')->load("Cash In Hand");

		$transaction->addCondition('group_path','like',$group['path'].'%');
			
		$from_date = $this->app->stickyGET('from_date')?:$this->app->today;
		$to_date = $this->app->stickyGET('to_date')?:$this->app->nextDate($this->app->today);

		$transaction->addCondition('created_at','>=',$from_date);
		$transaction->addCondition('created_at','<',$to_date);

		$cash_account = $this->add('xepan\accounts\Model_Ledger')->load("Cash Account");
		$opening_balance = $cash_account->getOpeningBalance($this->api->today);
				
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

		if(!$crud->isEditing()){
			$grid= $crud->grid;
			$grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
			$grid->addCurrentBalanceInEachRow();
			$grid->addSno();
			$grid->removeColumn('account');

			$grid->addHook('formatRow',function($g){
				if($g->model->customer()){
					$g->current_row_html['transaction_type']=$g->model['transaction_type']." :: ".$g->model->customer()->get('organization_name');
				}else
					$g->current_row_html['transaction_type']=$g->model['transaction_type'];

				if(!$g->model['original_amount_cr']){								
					$g->current_row_html['currency_cr'] = ' ';
				}else{
					$g->current_row_html['currency_cr'] = $g->model['currency'];
				}

				if(!$g->model['original_amount_dr']){								
					$g->current_row_html['currency_dr'] = ' ';
				}else{
					$g->current_row_html['currency_dr'] = $g->model['currency'];
				}

				if($g->model['currency_id'] == $this->app->epan->default_currency->id){
					$g->current_row_html['currency_dr'] = ' ';
					$g->current_row_html['original_amount_dr'] = ' ';
					$g->current_row_html['currency_cr'] = ' ';
					$g->current_row_html['original_amount_cr'] = ' ';
				}								
			});	
		}

		if($crud->isEditing()){
			$transaction->load($crud->id);
		}
		
		$crud->setModel($transaction);

		if($form->isSubmitted()){
			$crud->js()->reload(['from_date'=>$form['from_date']?:0,'to_date'=>$form['to_date']?:0])->execute();
		}
	}
}