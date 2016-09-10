<?php
namespace xepan\accounts;
class page_statement extends \xepan\base\Page {
	
	public $title="Account Statement";
	
	function init(){
		parent::init();

		$ledger_id= $this->api->stickyGET('ledger_id')?:0;
		$to_date = $this->api->stickyGET('to_date');
		$from_date = $this->api->stickyGET('from_date');

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
		
		$form->addSubmit('Get Statement')->addClass('btn btn-primary');

		$crud = $this->add('xepan\hr\CRUD',
				[
					'grid_class'=>'xepan\accounts\Grid_AccountsBase',
					'grid_options'=>['no_records_message'=>'No account statement found'],
					'form_class' => 'xepan\accounts\Form_EntryRunner',
					'allow_add'=> false
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

		if($ledger_id){
			
			$ledger_id = $this->api->stickyGET('ledger_id');
			$this->api->stickyGET('from_date');
			$this->api->stickyGET('to_date');
		
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

				$send_email_btn = $grid->addButton('Send E-mail')->addClass('btn btn-primary');

		/*Send Account Statement In mail to Customer*/
				$mail_vp = $this->add('VirtualPage');
				$mail_vp->set(function($p)use($transactions,$ledger_id){
					
					$ledger_model=$p->add('xepan\accounts\Model_Ledger');
					$ledger=$ledger_model->load($ledger_id);
					$contact=$ledger->contact();
					$email=$contact->ref('Emails')->get('value');
					
					$vp_form=$p->add('Form');
					$vp_form->addField('line','email_to')->set($email);
					$vp_form->addField('line','subject');
					$ledger_lister_view=$p->add('xepan\accounts\View_Lister_LedgerStatement',['ledger_id'=>$ledger_id,'from_date'=>$_GET['from_date']]);
					$ledger_lister_view->setModel($transactions);
									
					$vp_form->addSubmit('send')->addClass('btn btn-primary');
					if($vp_form->isSubmitted()){
						$ledger_model->sendEmail($vp_form['email_to'],$vp_form['subject'],$ledger_lister_view->getHtml(),$vp_form['message'],$ccs=[],$bccs=[]);
						$vp_form->js(null,$vp_form->js()->univ()->closeDialog())->univ()->successMessage('Mail Send Successfully')->execute();
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

		$transactions->addExpression('no')->set(function($m,$q){
			$related_no = $m->add('xepan\commerce\Model_QSP_Master')
									->addCondition('id',$m->getElement('related_id'));
			return $q->expr("[0]",[$related_no->fieldQuery('document_no')]);
		});

		if($crud->isEditing()){
			$transactions->load($crud->id);
		}

		$crud->setModel($transactions);

			// ,['voucher_no','transaction_type','created_at','Narration','amountDr','amountCr','original_amount_dr','original_amount_cr','related_id']);
		// $grid->addPaginator(10);

		if($form->isSubmitted()){
			
			$crud->js()->reload(
					[
						'ledger_id'=>$ledger_id?:$form['ledger'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						]
					)->execute();
		}
	}
}