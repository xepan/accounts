<?php
namespace xepan\accounts;
class page_statement extends \xepan\base\Page {
	public $title="Account Statement";
	public $acl=false;
	function init(){
		parent::init();


		$form=$this->add('Form');
		$account_field = $form->addField('autocomplete/Basic','ledger')->validateNotNull();
		$account_field->setModel('xepan\accounts\Ledger');

		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');
		$form->addSubmit('Get Statement');

		$grid = $this->add('xepan\accounts\Grid_AccountsBase',null,null,['view/accountstatement-grid']);

		$transactions = $this->add('xepan\accounts\Model_TransactionRow');

		if($ledger_id = $this->api->stickyGET('ledger_id')){
			
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
			$grid->addOpeningBalance($opening_amount,$opening_column,['Narration'=>$opening_narration],$opening_side);
			$grid->addCurrentBalanceInEachRow();

			$send_email_btn = $grid->addButton('Send E-mail');

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
								
				$vp_form->addSubmit('send');
				if($vp_form->isSubmitted()){
					$ledger_model->sendEmail($vp_form['email_to'],$vp_form['subject'],$ledger_lister_view->getHtml(),$vp_form['message'],$ccs=[],$bccs=[]);
					$vp_form->js(null,$vp_form->js()->univ()->closeDialog())->univ()->successMessage('Mail Send Successfully')->execute();
				}
			});

			if($send_email_btn->isClicked()){
				$this->js()->univ()->frameURL('Send Mail',$mail_vp->getURL(),['ledger_id'=>$_GET['ledger_id']])->execute();
				}

		}else{
			$transactions->addCondition('id',-1);
		}

		$transactions->setOrder('created_at');
		$grid->setModel($transactions,['voucher_no','transaction_type','created_at','Narration','amountDr','amountCr','original_amount_dr','original_amount_cr']);
		// $grid->addPaginator(10);
		$grid->addSno();
		


		if($form->isSubmitted()){
			
			$grid->js()->reload(
					[
						'ledger_id'=>$form['ledger'],
						'from_date'=>($form['from_date'])?:0,
						'to_date'=>($form['to_date'])?:0,
						]
					)->execute();
		}
	}
}