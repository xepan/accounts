<?php
namespace xepan\accounts;
class page_voucherprint extends \Page{

	function init(){
		parent::init();

			$this->api->stickyGET('transaction_id');

			$this->vp=$this->add('VirtualPage')->set(function($p){
				// $related_root_document_name = $p->api->stickyGET('root_document_name');

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

			$cols= $this->add('Columns')->addClass('row xepan-push');
			$left=$cols->addColumn(4)->addClass('col-md-4');
			$mid=$cols->addColumn(4)->addClass('col-md-4');
			$right=$cols->addColumn(4)->addClass('col-md-4');

			$left->add('View')->set(['Transaction Date : ' . $transaction['created_at']])->addClass('fa fa-calendar');
			$mid->add('View')->set([$transaction['transaction_type']])->addClass('text-center fa fa-check');
			
			if(!$transaction instanceof \Dummy){
				$right->add('View_Info')
					->setElement('a')
					->set([$transaction->get('name')."tst",'icon'=>'fa fa-export'])
					->setAttr('href','#xepan')
					->addClass('fa fa-export')
					->js('click',$this->js()->univ()->frameURL($transaction->get('name'), $this->api->url($this->vp->getURL()) ));				
			}

			$grid=$this->add('xepan\hr\Grid',null,null,['view/voucher-grid']);
			$grid->template->tryDel('Pannel');
			$grid->setModel($transaction->ref('TransactionRows')->setOrder('amountDr desc, amountCr desc'),['ledger','amountDr','amountCr']);
			$d_c=$this->add('Columns');
			$ld=$d_c->addColumn(8)->addClass('col-md-8');
			$ld->add('View')->set([$transaction['Narration']])->addClass('fa fa-pencil');
			$rd=$d_c->addColumn(4)->addClass('col-md-4');
			$rd->add('Button')->setHTML('<i class="fa fa-trash-o"></i>')->addClass('pull-right');
	}

	function relatedDocumentLink(){

	}
}