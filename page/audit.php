<?php


namespace xepan\accounts;

class page_audit extends \xepan\base\Page {
	public $title= "Audit Accounts DB";

	function init(){
		parent::init();
		ini_set('memory_limit', '3G');
		set_time_limit(0);
		
	}

	function page_index(){
		parent::init();

		$tabs = $this->add('Tabs');
		$tabs->addTabURL($this->app->url('./DrCrMisMatch'),'Dr<>CD');
		$tabs->addTabURL($this->app->url('./groupsBalance'),'groupsBalance');
		$tabs->addTabURL($this->app->url('./openningCrDrDiff'),'openningCrDrDiff');
		$tabs->addTabURL($this->app->url('./allTRansactions'),'allTRansactions');
		$tabs->addTabURL($this->app->url('./allTRansactionsRows'),'allTRansactionsRows');
		$tabs->addTabURL($this->app->url('./allInvoiceData'),'allInvoiceData');
		$tabs->addTabURL($this->app->url('./ledgers'),'ledgers');
		$tabs->addTabURL($this->app->url('./groups'),'groups');
		$tabs->addTabURL($this->app->url('./balancesheet'),'balancesheet');

	}

	function page_DrCrMisMatch(){
		
			
		$m = $this->add('xepan\accounts\Model_Transaction');
		$q= $m->dsql();

		$m->addCondition($q->expr('[0]<>[1]',[$m->getElement('cr_sum'),$m->getElement('cr_sum')]));

		$grid = $this->add('Grid');
		$grid->setModel($m);

		$grid->add('H2',null,'grid_buttons')->set('Dr/Cr Mismatch');
	}

	function page_groupsBalance(){
		$m = $this->add('xepan\accounts\Model_Group');

		$m->add('misc/Field_Callback','balance')->set(function($m){
 			$t = $m->getBalance();
 			if(!$t) return '';
 			return abs($t) . ' '. ($t<0?'Dr':'Cr');
		});


		$grid = $this->add('xepan\base\Grid');
		$grid->setModel($m);
		$grid->addPaginator(50);
		$grid->add('H2',null,'grid_buttons')->set('Groups with Balances');
	}

	function page_openningCrDrDiff(){
		$this->add('h3')->set('Positive Balance signed is DR and negative is CR');

		$g= $this->add('xepan\base\Grid');
		$m = $this->add('xepan\accounts\Model_Ledger');
		$m->addCondition([['OpeningBalanceDr','>',0],['OpeningBalanceCr','>',0]]);
		$g->setModel($m,['name','group','report_name','OpeningBalanceDr','OpeningBalanceCr','balance_sheet','root_group','balance','balance_signed']);
		$g->add('H2',null,'grid_buttons')->set('Openning Balances of Ledgers');
		$g->addTotals(['OpeningBalanceDr','OpeningBalanceCr','balance_signed']);

		$g->addFormatter('group','wrap');
		$g->addFormatter('name','wrap');
	}

	function page_allTRansactions(){
		$crud = $this->add('xepan\base\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No account statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false,
					'allow_edit'=>false,
					'allow_del'=>false
				]);

		$transactions = $this->add('xepan\accounts\Model_Transaction');
		// $transactions->getElement('exchange_rate')->destroy();
		// $trow_j = $transactions->join('account_transaction_row.transaction_id');
		// $trow_j->addField('row_exchange_rate','exchange_rate');
		// $trow_j->addField('original_amount_dr','_amountDr');
		// $trow_j->addField('original_amount_cr','_amountCr');
		// $trow_j->hasOne('xepan\accounts\Model_Ledger','ledger_id');

		// $transactions->addExpression('amountDr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_dr'),$transactions->getElement('exchange_rate')]));
		// $transactions->addExpression('amountCr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_cr'),$transactions->getElement('exchange_rate')]));

		$transactions->addExpression('no')->set(function($m,$q){
			$related_no = $m->add('xepan\commerce\Model_QSP_Master')
									->addCondition('id',$m->getElement('related_id'));
			return $q->expr("[0]",[$related_no->fieldQuery('document_no')]);
		});

		$transactions->setOrder('created_at');

		

		$transactions->addExpression('doc_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\base\Model_Document_Attachment')
								->addCondition('document_id',$m->getElement('related_id'));		
			return $doc_attachment_m->count();
		});

		$transactions->addExpression('trans_attachment_count')->set(function($m,$q){
			$doc_attachment_m = $m->add('xepan\accounts\Model_Transaction_Attachment')
								->addCondition('account_transaction_id',$m->getElement('id'));		
			return $doc_attachment_m->count();
		});

		$transactions->getElement('attachments_count')->destroy();
		$transactions->addExpression('attachments_count')->set(function($m,$q){
			return $q->expr('([0]+[1])',[$m->getElement('doc_attachment_count'), $m->getElement('trans_attachment_count')]);
		});

		$crud->grid->addHook('formatRow',function($g){
			$g->current_row_html['created_at'] = date('F jS Y', strtotime($g->model['created_at']));
			if(!$g->model['transaction_template_id'] && !$this->app->auth->model->isSuperUser()){
				$g->current_row_html['edit'] = '<span class="fa-stack table-link"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-pencil fa-stack-1x fa-inverse"></i></span>';				
				$g->current_row_html['delete'] = '<span class="table-link fa-stack"><i class="fa fa-square fa-stack-2x"></i><i class="fa fa-trash-o fa-stack-1x fa-inverse"></i></span>';				
			}

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

		$crud->setModel($transactions,['created_at','voucher_no','transaction_type','Narration','cr_sum','dr_sum','cr_sum_exchanged','dr_sum_exchanged']);

		$crud->grid->addPaginator(100);
		$crud->grid->addSno();
		$crud->grid->addTotals(['amountDr','amountCr']);

		$crud->addRef('TransactionRows',['view_class'=>'xepan\base\Grid','fields'=>['ledger','currency','_amountDr','_amountCr','exchange_rate','amountCr','amountDr']]);

		$crud->grid->add('H2',null,'grid_buttons')->set('All Transactions');
	}

	function page_allTRansactionsRows(){
		$grid = $this->add('xepan\base\Grid');

		$grid->addHook('formatRow',function($g){
			$g->current_row_html['created_at'] = date('F jS Y', strtotime($g->model['created_at']));
		});
		
		$m= $this->add('xepan\accounts\Model_TransactionRow');
		$m->setOrder('created_at');

		$m->addCondition('ledger',null);

		$grid->setModel($m,['created_at','transaction','ledger','ledger_id','report_name','subtract_from','positive_side','_amountDr','_amountCr','amountDr','amountCr','original_amount_dr','original_amount_cr','currency']);
		$grid->removeColumn('ledger_id');
		$grid->addFormatter('ledger','wrap');
		$grid->addTotals(['_amountDr','_amountCr','amountDr','amountCr']);
	}

	function page_allInvoiceData(){
		$grid = $this->add('Grid');
		$m = $this->add('xepan\commerce\Model_SalesInvoice');
		$grid->setModel($m);
		$grid->addColumn('Expander','details');
	}

	function page_allInvoiceData_details(){
		$grid = $this->add('Grid');
		$m1 = $this->add('xepan\commerce\Model_QSP_Detail')->addCondition('qsp_master_id',$_GET['document_id']);
		$grid->setModel($m1);

	}

	function page_ledgers(){
		$btn_ste = $this->add('ButtonSet');
		$btn_1 = $btn_ste->addButton('Remove Zero');
		$btn_2 = $btn_ste->addButton('Show All');

		$f = $this->add('Form');
		$f->addField('DropDown','balance_sheet_type')
			->setValueList(['BalanceSheet'=>'BalanceSheet','Profit & Loss'=>'Profit & Loss','Trading'=>'Trading'])
			->setAttr('multiple');
		$f->addSubmit('Filter');

		$this->add('h3')->set('Positive Balance signed is DR and negative is CR');

		$grid = $this->add('xepan\base\Grid');
		$m = $this->add('xepan\accounts\Model_Ledger');
		
		if($_GET['remove_zero'])
			$m->addCondition('balance_signed','<>',0);

		$m->setOrder('report_name');

		if($_GET['balance_sheet_type'])
			$m->addCondition('report_name',explode(",", $_GET['balance_sheet_type']));
		
		$grid->setModel($m,['name','group','root_group','balance_sheet','report_name','subtract_from','positive_side','balance','balance_signed']);
		$grid->addFormatter('name','wrap');
		$grid->addFormatter('group','wrap');
		// $grid->removeColumn('balance_signed');

		$grid->addTotals(['balance_signed']);

		$btn_1->js('click',$grid->js()->reload(['remove_zero'=> 1]));
		$btn_2->js('click',$grid->js()->reload(['remove_zero'=> 0]));

		if($f->isSubmitted()){			
			$grid->js()->reload(['balance_sheet_type'=>$f['balance_sheet_type']])->execute();
		}

	}

	function page_groups(){
		$grid = $this->add('xepan\base\Grid');
		$m = $this->add('xepan\accounts\Model_BSGroup');

		$grid->setModel($m,['name','balance_sheet','parent_group','root_group_name','report_name','subtract_from','positive_side','ClosingBalanceDr','ClosingBalanceCr']);

		$grid->addFormatter('name','wrap');
		$grid->addFormatter('parent_group','wrap');
		$grid->addFormatter('root_group_name','wrap');

		$grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
	}

	function page_balancesheet(){
		$f = $this->add('Form');
		$f->addField('DropDown','balance_sheet_type')
			->setValueList(['BalanceSheet'=>'BalanceSheet','Profit & Loss'=>'Profit & Loss','Trading'=>'Trading'])
			->setAttr('multiple');
		$f->addSubmit('Filter');

		$grid = $this->add('xepan\base\Grid');
		$m = $this->add('xepan\accounts\Model_BSBalanceSheet');
		
		$m->getElement('OpeningBalanceDr')->caption('ACC-OP-DR');
		$m->getElement('OpeningBalanceCr')->caption('ACC-OP-CR');
		$m->getElement('PreviousTransactionsDr')->caption('B4-Date-Dr');
		$m->getElement('PreviousTransactionsCr')->caption('B4-Date-Cr');
		$m->getElement('TransactionsDr')->caption('from-to-Dr');
		$m->getElement('TransactionsCr')->caption('from-to-Cr');
		$m->getElement('ClosingBalanceDr')->caption('Bal-DR');
		$m->getElement('ClosingBalanceCr')->caption('Bal-CR');

		$m->addExpression('side')->set(function($m,$q){
			return $q->expr('IF([0]="LT","Liabilities","Assets")',[$m->getElement('positive_side')]);
		});

		$grid->addMethod('format_balance',function($g,$f){
			$amt = $g->model['ClosingBalanceDr']-$g->model['ClosingBalanceCr'];
			$g->current_row[$f] = abs($amt). ' '. ($amt>0?'Dr':'Cr');
		});

		if($_GET['balance_sheet_type'])
			$m->addCondition('report_name',explode(",", $_GET['balance_sheet_type']));


		$grid->setModel($m,['name','report_name','subtract_from','side','OpeningBalanceDr','OpeningBalanceCr','PreviousTransactionsDr','PreviousTransactionsCr','TransactionsDr','TransactionsCr','ClosingBalanceDr','ClosingBalanceCr','balance']);
		$grid->addColumn('balance','balance');
		$grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr']);
		$grid->addFormatter('name','wrap');


		if($f->isSubmitted()){			
			$grid->js()->reload(['balance_sheet_type'=>$f['balance_sheet_type']])->execute();
		}
	}

}