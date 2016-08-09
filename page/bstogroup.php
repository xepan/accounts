<?php

namespace xepan\accounts;

class page_bstogroup extends \xepan\base\Page{
	public $title = "Balance Sheet Groups";
	function init(){
		parent::init();
		
		$bs_id = $this->api->stickyGET('bs_id');
		$bs_group = $this->add('xepan\accounts\Model_BSGroup');
		$bs_group->addCondition('balance_sheet_id',$bs_id);

		$grid = $this->add('xepan\hr\Grid',null,null,['view\grid\bstogroup']);
		$grid->setModel($bs_group);

		$this->on('click','.xepan-accounts-bs-subgroup',function($js,$data){
            return $js->univ()->redirect($this->app->url('xepan_accounts_bstogroup',['bs_id'=>$data['id']]));
        });
	}
}