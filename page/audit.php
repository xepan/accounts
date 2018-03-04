<?php


namespace xepan\accounts;

class page_audit extends \xepan\base\Page {
	public $title= "Audit Accounts DB";

	function init(){
		parent::init();

		ini_set('memory_limit', '3G');
		set_time_limit(0);

		$this->DrCrMisMatch();
		$this->groupsBalance();
		$this->allTRansactions();

	}

	function DrCrMisMatch(){
		
			
		$m = $this->add('xepan\accounts\Model_Transaction');
		$q= $m->dsql();

		$m->addCondition($q->expr('[0]<>[1]',[$m->getElement('cr_sum'),$m->getElement('cr_sum')]));

		$grid = $this->add('Grid');
		$grid->setModel($m);

		$grid->add('H2',null,'grid_buttons')->set('Dr/Cr Mismatch');
	}

	function groupsBalance(){
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

	function allTRansactions(){
		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No account statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false,
					'allow_edit'=>false,
					'allow_delete'=>false,
				],null,['view/accountstatement-grid']);


		$transactions = $this->add('xepan\accounts\Model_Transaction');
		$transactions->getElement('exchange_rate')->destroy();
		$trow_j = $transactions->join('account_transaction_row.transaction_id');
		$trow_j->addField('exchange_rate');
		$trow_j->addField('original_amount_dr','_amountDr');
		$trow_j->addField('original_amount_cr','_amountCr');
		$trow_j->addField('ledger_id');

		$transactions->addExpression('amountDr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_dr'),$transactions->getElement('exchange_rate')]));
		$transactions->addExpression('amountCr')->set($transactions->dsql()->expr('round(([0]*[1]),2)',[$transactions->getElement('original_amount_cr'),$transactions->getElement('exchange_rate')]));

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

		$crud->setModel($transactions,['voucher_no','transaction_type','created_at','Narration','amountDr','amountCr','original_amount_dr','original_amount_cr','related_id']);

		$crud->grid->addPaginator(100);

		$crud->grid->add('H2',null,'grid_buttons')->set('All Transactions');
	}
}