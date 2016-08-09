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
			$subgroupandledger[] = ['name'=>$group['name'],'data-type'=>'group','id'=>$group['id'],'balancecr'=>$group['ClosingBalanceCr'],'balancedr'=>$group['ClosingBalanceDr']]; 
		}
		
		$bs_ledger = $this->add('xepan\accounts\Model_BSLedger');
		$bs_ledger->addCondition('group_id',$group_id);

		foreach ($bs_ledger as $ledger){
			$subgroupandledger[] = ['name'=>$ledger['name'],'data-type'=>'ledger','id'=>$ledger['id'],'balancecr'=>$ledger['ClosingBalanceCr'],'balancedr'=>$ledger['ClosingBalanceDr']]; 
		}

		$grid = $this->add('xepan\hr\Grid',null,null,['view\grid\subgroupandledger']);
		$grid->setSource($subgroupandledger);
		
		$grid->addHook('formatRow',function($g){

		});	

		$this->on('click','.xepan-accounts-bs-group-ledger',function($js,$data){
            if($data['type'] == 'ledger')
            	return $js->univ()->redirect($this->app->url('xepan_accounts_ledger',['ledger_id'=>$data['id']]));
            
            if($data['type'] == 'group')
            	return $js->univ()->redirect($this->app->url('xepan_accounts_groupdig',['group_id'=>$data['id']]));
        });	
	}
}