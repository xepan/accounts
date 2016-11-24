<?php

namespace xepan\accounts;

class page_bstogroup extends \xepan\base\Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
				
		$bs_id = $this->api->stickyGET('bs_id');
		$from_date = $this->api->stickyGET('from_date');
		$to_date = $this->api->stickyGET('to_date');
		
		$bs_group = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$from_date,'to_date'=>$to_date]);
		$bs_group->addCondition('balance_sheet_id',$bs_id);
		$bs_group->addCondition('parent_group_id',null);

		$grid = $this->add('xepan\hr\Grid',null,null,['view\grid\bstogroup']);
		$grid->setModel($bs_group);
		$grid->addColumn('balance');

		$grid->addMethod('format_balance',function($g,$f){
			$side = ($g->model['ClosingBalanceDr'] > $this->model['ClosingBalanceCr']) ? "Dr":"Cr";
			$amount = abs($g->model['ClosingBalanceDr'] - $g->model['ClosingBalanceCr']);
			if($amount)
				$g->current_row_html[$f]= '<span style="float:right">'.$side . '</span> ' . $amount;
			else
				$g->current_row_html[$f]= '';
		});

		$grid->addFormatter('balance','balance');

		$bs = $this->add('xepan\accounts\Model_BSBalanceSheet')->load($bs_id);
		$grid->template->trySet('head',$bs['name']);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);

		$grid->addTotals(['ClosingBalanceDr','ClosingBalanceCr','balance']);
        $this->js('click')->_selector('.xepan-accounts-bs-subgroup')->univ()->frameURL('Groups And Ledger',[$this->api->url('xepan_accounts_groupdig'),'group_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}