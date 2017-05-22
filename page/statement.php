<?php
namespace xepan\accounts;
class page_statement extends \xepan\base\Page {
	
	public $title="Account Statement";
	
	function init(){
		parent::init();

		$ledger_id= $this->api->stickyGET('ledger_id')?:0;
		$to_date = $this->api->stickyGET('to_date');
		$from_date = $this->api->stickyGET('from_date');
		$ledger_amount = $this->api->stickyGET('amount');

		$form=$this->add('Form',null,null);
		$form->setLayout('view/form/actstatement-grid-info-form');

		if($ledger_id){
			$ledger_m = $this->add('xepan\accounts\Model_Ledger')->load($ledger_id);
			$form->layout->add('View',null,'ledger')->set($ledger_m['name']);
			// $account_field = $form->addField('Readonly','ledger')->set($ledger_m['name']);

		}else{			
			$account_field = $form->addField('autocomplete/Basic','ledger')->validateNotNull();
			$account_field->setModel('xepan\accounts\Ledger');
		}

		$form->addField('DatePicker','from_date')->set($from_date);
		$form->addField('DatePicker','to_date')->set($to_date);
		$form->addField('Line','amount')->set($to_date);
		
		$form->addSubmit('Get Statement')->addClass('btn btn-primary');

		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No account statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false
				],null,['view/accountstatement-grid']);


		 $crud->grid->add('VirtualPage')
	 		->addColumn('edit_transaction')
			->set(function($page){
				$id = $_GET[$page->short_name.'_id'];
				$model = $page->add('xepan\accounts\Model_Transaction')->load($id);
				$widget = $page->add('xepan\accounts\View_TransactionWidget');
				$widget->setModel($model);
			});

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


		if($ledger_id){
			$ledger_id = $this->api->stickyGET('ledger_id');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');

			if($ledger_amount){
				$transactions->addCondition($transactions->dsql()->orExpr()
								->where('_amountDr',$ledger_amount)
								->where('_amountCr',$ledger_amount)
					);
			}
		
			if($ledger_id)
				$transactions->addCondition('ledger_id',$_GET['ledger_id']);
			
			if($_GET['from_date'])
				$transactions->addCondition('created_at','>=',$_GET['from_date']);
			
			if($_GET['to_date'])
				$transactions->addCondition('created_at','<',$this->app->nextDate($_GET['to_date']));

			if($_GET['ledger_id'])
				$opening_balance = $this->add('xepan\accounts\Model_Ledger')->load($_GET['ledger_id'])->getOpeningBalance($_GET['from_date']);
			

			if(($opening_balance['DR'] - $opening_balance['CR']) > 0){
				$opening_column = 'amountDr';
				$opening_amount = $opening_balance['DR'] - $opening_balance['CR'];
				$opening_narration = "To Opening balace";
				$opening_side = 'DR';
			}else{
				$opening_column = 'amountCr';
				$opening_amount = $opening_balance['CR'] - $opening_balance['DR'];
				$opening_narration = "By Opening balace";
				$opening_side = 'CR';
			}
			
			if(!$crud->isEditing()){
				$grid = $crud->grid;
				$grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
				$grid->addCurrentBalanceInEachRow();
				$grid->addSno();
				$grid->addHook('formatRow',function($g){
					if($g->model['no']){
						$g->current_row['sales_no'] = " :: " .$g->model['no'];
					}
				});

				$grid->js('click')->_selector('.do-view-attachment')->univ()
					->frameURL('Attachments',[$this->api->url
					('xepan_accounts_accounttransaction_attachment'),'account_transaction_id'=>$this->js()
					->_selectorThis()->closest('[data-id]')->data('id')]);
				
				/*Send Account Statement In mail to Customer*/
				$send_email_btn = $grid->addButton('Send E-mail')->addClass('btn btn-primary');

				$mail_vp = $this->add('VirtualPage');
				$mail_vp->set(function($p)use($transactions,$ledger_id){
					
					$ledger_model=$p->add('xepan\accounts\Model_Ledger');
					$ledger=$ledger_model->load($ledger_id);
					$contact=$ledger->contact();
					if($contact){
						$email=str_replace("<br/>", ",",$contact['emails_str']);
					}else{
						$email = " ";
					}
					$vp_form=$p->add('Form');
					$vp_form->setLayout('view/form/send-statment');
					$vp_form->addField('line','email_to')->set($email);
					$vp_form->addField('line','cc');
					$vp_form->addField('line','bcc');
					$vp_form->addField('line','subject');
					$vp_form->addField('xepan\base\RichText','message');
					
					$from_email = $vp_form->addField('dropdown','from_email')->validate('required')->setEmptyText('Please Select from Email');
					$from_email->setModel('xepan\hr\Post_Email_MyEmails');
					
					$email_setting=$this->add('xepan\communication\Model_Communication_EmailSetting');
					
					if($_GET['from_email'])
						$email_setting->tryLoad($_GET['from_email']);
					$view = $vp_form->layout->add('View',null,'signature')->setHTML($email_setting['signature']);
					$from_email->js('change',$view->js()->reload(['from_email'=>$from_email->js()->val()]));
					
					$statement_list =$p->add('xepan\hr\CRUD',
											[
												'grid_class'=>'xepan\accounts\Grid_AccountsBase',
												'grid_options'=>['no_records_message'=>'No account statement found'],
												'form_class' => null,
												'allow_add'=> false
											],null,['view/acstatement']);;
					
					if($_GET['ledger_id'])
						$opening_bal = $this->add('xepan\accounts\Model_Ledger')->load($_GET['ledger_id'])->getOpeningBalance($_GET['from_date']);

					if(($opening_bal['DR'] - $opening_bal['CR']) > 0){
						$opening_column = 'amountDr';
						$opening_amount = $opening_bal['DR'] - $opening_bal['CR'];
						$opening_narration = "To Opening balace";
						$opening_side = 'DR';
					}else{
						$opening_column = 'amountCr';
						$opening_amount = $opening_bal['CR'] - $opening_bal['DR'];
						$opening_narration = "By Opening balace";
						$opening_side = 'CR';
					}
					if(!$statement_list->isEditing()){
						$grid = $statement_list->grid;
						$grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
						$grid->addCurrentBalanceInEachRow();
						$grid->addSno();
						$grid->addHook('formatRow',function($g){
							if($g->model['no']){
								$g->current_row['sales_no'] = " :: " .$g->model['no'];
							}
						});
					}
					$transactions->setOrder('created_at');
					$statement_list->setModel($transactions);
					
					$vp_form->addSubmit('Send')->addClass('btn btn-primary');
					

					if($vp_form->isSubmitted()){
						$list = $statement_list->getHtml();
						$message = $vp_form['message']."  ". " <br> ". $list;
						$ledger_model->sendEmail($vp_form['from_email'],$vp_form['email_to'],$vp_form['cc'],$vp_form['bcc'],$vp_form['subject'],$message);
						$vp_form->js(null,$vp_form->js()->closest('.dialog')->dialog('close'))->univ()->successMessage('Email Send SuccessFully')->execute();
					}

				});	

				if($send_email_btn->isClicked()){
					$this->js()->univ()->frameURL('Send Mail',$mail_vp->getURL(),['ledger_id'=>$_GET['ledger_id']])->execute();
				}
			}

		}else{
			$transactions->addCondition('id',-1);
		}

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




		if($crud->isEditing()){
			$transactions->load($crud->id);
		}

		$crud->setModel($transactions,['voucher_no','transaction_type','created_at','Narration','amountDr','amountCr','original_amount_dr','original_amount_cr','related_id']);
		$crud->grid->addQuickSearch(['name','Narration','transaction_type','related_type']);

			// ,['voucher_no','transaction_type','created_at','Narration','amountDr','amountCr','original_amount_dr','original_amount_cr','related_id']);
		// $grid->addPaginator(10);

		if($form->isSubmitted()){
			
			$crud->js()->reload(
					[
						'ledger_id'=>$ledger_id?:$form['ledger'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						'amount'=>($form['amount'])?:0,
						]
					)->execute();
		}
	}
}