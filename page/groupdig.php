<?php

namespace xepan\accounts;

class page_groupdig extends \xepan\base\Page{
	public $title = "Group Digging";

	function init(){
		parent::init();

		$group_id = $this->api->stickyGET('group_id');
		$to_date = $this->api->stickyGET('to_date');
		$from_date = $this->api->stickyGET('from_date');

		$bs_group = $this->add('xepan\accounts\Model_BSGroup');
		$bs_group->addCondition('parent_group_id',$group_id);

		$subgroupandledger = [];
		foreach ($bs_group as $group){
			$subgroupandledger[] = ['name'=>$group['name'],'type'=>'group','id'=>$group['id'],'class'=>'-sub-group','balancecr'=>$group['ClosingBalanceCr'],'balancedr'=>$group['ClosingBalanceDr']]; 
		}
		
		$bs_ledger = $this->add('xepan\accounts\Model_BSLedger');
		$bs_ledger->addCondition('group_id',$group_id);

		foreach ($bs_ledger as $ledger){
			$subgroupandledger[] = ['name'=>$ledger['name'],'type'=>'ledger','id'=>$ledger['id'],'class'=>'-ledger','balancecr'=>$ledger['ClosingBalanceCr'],'balancedr'=>$ledger['ClosingBalanceDr']]; 
		}

		$grid = $this->add('xepan\hr\Grid',null,null,['view\grid\subgroupandledger']);
		$grid->setSource($subgroupandledger);

		$g = $this->add('xepan\accounts\Model_BSGroup')->load($group_id);

		$grid->template->trySet('parent',$g['name']);
		$grid->template->trySet('from_date',$from_date);
		$grid->template->trySet('to_date',$to_date);

        $this->js('click')->_selector('.xepan-accounts-bs-ledger')->univ()->frameURL('Account Statement',[$this->api->url('xepan_accounts_statement'),'ledger_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);	
        $this->js('click')->_selector('.xepan-accounts-bs-sub-group')->univ()->frameURL('Groups And Ledger',[$this->api->url('xepan_accounts_groupdig'),'group_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);	
	}
}