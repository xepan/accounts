<?php
namespace xepan\accounts;
class page_voucherprint extends \Page{

	function init(){
		parent::init();

			$this->api->stickyGET('transaction_id');

			$this->vp=$this->add('VirtualPage')->set(function($p){
				$related_root_document_name = $p->api->stickyGET('root_document_name');

				$array = [
						'xepan\commerce\SalesInvoice'=>'invoice',
						'xepan\commerce\Order'=>'order'
					];

				$m_class = explode("\\", $related_root_document_name);
				$m_class=$m_class[0].'/Model_'.$m_class[1];
				$m=$p->add($m_class);
				$m->tryLoad($p->api->stickyGET('document_id'));

				$v_class = explode("\\", $related_root_document_name);
				$v_class=$v_class[0].'/View_'.$v_class[1];

				$model_field = $array[$related_root_document_name];

				$p->add($v_class,[$model_field=>$m]);
			});

			$transaction = $this->add('xepan\accounts\Model_Transaction');
			$transaction->load($_GET['transaction_id']);

			$cols= $this->add('Columns');
			$left=$cols->addColumn(4);
			$mid=$cols->addColumn(4);
			$right=$cols->addColumn(4);

			$left->add('View')->set(['Transaction Date : ' . $transaction['created_at'],'icon'=>'calendar']);
			$mid->add('View')->set([$transaction['transaction_type'],'icon'=>'check'])->addClass('text-center');
			
			if(!$transaction->relatedDocument() instanceof \Dummy){
				$right->add('View')
					->setElement('a')
					->set([$transaction->relatedDocument()->get('name'),'icon'=>'export'])
					->setAttr('href','#xepan')
					->js('click',$this->js()->univ()->frameURL($transaction->relatedDocument()->get('name'), $this->api->url($this->vp->getURL(),['root_document_name'=>$transaction['related_root_document_name'],'document_id'=>$transaction['related_document_id']]) ));				
			}

			$grid=$this->add('Grid');
			$grid->setModel($transaction->ref('xepan\accounts\TransactionRow')->setOrder('amountDr desc, amountCr desc'),['account','amountDr','amountCr']);

			$this->add('View')->set([$transaction['Narration'],'icon'=>'pencil']);
	}

	function relatedDocumentLink(){

	}
}