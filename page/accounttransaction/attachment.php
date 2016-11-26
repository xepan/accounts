<?php

namespace xepan\accounts;

class page_accounttransaction_attachment extends \xepan\base\Page
{
	public $title = "Accounts Transaction Attachments";
	
	function init()
	{
		parent::init();

			$transaction_id= $this->api->stickyGET('account_transaction_id')?:0;
			$transactions = $this->add('xepan\accounts\Model_Transaction')->load($transaction_id);
			$model_attachment=$this->add('xepan\accounts\Model_Transaction_Attachment');
			$model_attachment->addCondition('account_transaction_id',$transactions->id);	
			$model_attachment->acl = 'xepan\accounts\Model_Transaction';
			
			$attachment_acl_add = true;

			$attachment_crud = $this->add('xepan\hr\CRUD',['allow_add'=>$attachment_acl_add],null,['view\accounts-attachment-grid']);
			$attachment_crud->setModel($model_attachment,['file_id','thumb_url'])
			->addCondition('account_transaction_id',$transactions->id);

			$qsp_m = $this->add('xepan\commerce\Model_QSP_Master');
			if($transactions['related_id'])
				$qsp_m->load($transactions['related_id']);
			
			if($qsp_m->loaded()){
				$doc_attachment_m = $this->add('xepan\base\Model_Document_Attachment')
								->addCondition('document_id',$qsp_m->id);		
				// $qsp_m->addExpression('doc_attachments_count')->set($qsp_m->refSQL('Attachments')->count());
				$doc_attachment_crud = $this->add('xepan\hr\Grid',null,null,['view\accounts-subdocuments-attachment-grid']);
				$doc_attachment_crud->setModel($doc_attachment_m,['file_id','thumb_url']);
			}
	}
}