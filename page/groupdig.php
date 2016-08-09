<?php

namespace xepan\accounts;

class page_groupdig extends \xepan\base\Page{
	public $title = "Group Digging";

	function init(){
		parent::init();

		$group_id = $this->api->stickyGET('group_id');
		$bs_group = $this->add('xepan\accounts\Model_BSGroup');
		$bs_group->addCondition('parent_group_id',$group_id);

		$subgroupandledger = [];
		foreach ($bs_group as $group){
			$subgroupandledger[] = ['name'=>$group['name'],'type'=>'group','id'=>$group['id'],'balancecr'=>$group['ClosingBalanceCr'],'balancedr'=>$group['ClosingBalanceDr']]; 
		}
		
		$bs_ledger = $this->add('xepan\accounts\Model_BSLedger');
		$bs_ledger->addCondition('group_id',$group_id);

		foreach ($bs_ledger as $ledger){
			$subgroupandledger[] = ['name'=>$ledger['name'],'type'=>'ledger','id'=>$ledger['id'],'balancecr'=>$ledger['ClosingBalanceCr'],'balancedr'=>$ledger['ClosingBalanceDr']]; 
		}

		$grid = $this->add('xepan\hr\Grid');
		$grid->setSource($subgroupandledger);
		
		// $this->on('click','.xepan-accounts-bs-subgroup',function($js,$data){
  //           return $js->univ()->redirect($this->app->url('xepan_accounts_groupdig',['group_id'=>$data['id']]));
  //       });
	}
}