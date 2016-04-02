<?php
namespace xepan\accounts;
class page_ledgers extends \Page{
	public $title="Account Ledgers";
	function init(){
		parent::init();

		$group = $this->add('xepan\accounts\Model_Ledger');
		$crud = $this->add('xepan\hr\CRUD',null,null,['view/ledger-grid']);
		$crud->setModel($group,['name','group_id','OpeningBalanceCr','OpeningBalanceDr'],['name','group','parent_group','root_group','CurrentBalanceCr','CurrentBalanceDr','OpeningBalanceCr','OpeningBalanceDr']);

		if(!$crud->isEditing()){
			$crud->grid->addMethod('format_balance',function($g,$f){
				$m=$g->model;

				$fig = ($m['OpeningBalanceDr'] + $m['CurrentBalanceDr'])-($m['OpeningBalanceCr'] + $m['CurrentBalanceCr']);
				
				if($fig < 0)
					$g->current_row_html[$f] = '<div class="pull-left">'.abs($fig) . '</div><div class="pull-right">Dr</div>';
				else
					$g->current_row_html[$f] = '<div class="pull-left">'.abs($fig) . '</div><div class="pull-right">Cr</div>';
			});
			$group->setOrder('name','asc');
			$crud->grid->addColumn('balance','balance');
			// $crud->grid->add_sno();
			$crud->grid->addQuickSearch('name');
			$crud->grid->removeColumn('CurrentBalanceDr');
			$crud->grid->removeColumn('CurrentBalanceCr');
			$crud->grid->removeColumn('OpeningBalanceCr');
			$crud->grid->removeColumn('OpeningBalanceDr');
		}

	}
}