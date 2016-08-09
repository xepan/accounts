<?php
namespace xepan\accounts;
class page_balancesheet extends \xepan\base\Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();

		$f=$this->add('Form',null,'form',['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date');
		$r->addField('DatePicker','to_date');
		$f->addSubmit('Filter')->addClass('btn btn-primary btn-block');
		
		$bsbalancesheet_l = $this->add('xepan\accounts\Model_BSBalanceSheet');
		$bsbalancesheet_l->addCondition('report_name','BalanceSheet');
		$bsbalancesheet_l->addCondition('is_left',true);
		$grid_l = $this->add('xepan\hr\Grid',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setModel($bsbalancesheet_l);

		$bsbalancesheet_a = $this->add('xepan\accounts\Model_BSBalanceSheet');
		$bsbalancesheet_a->addCondition('report_name','BalanceSheet');
		$bsbalancesheet_l->addCondition('is_left',false);
		$grid_a = $this->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->setModel($bsbalancesheet_a);
		
		
		// $grid->addHook('formatRow',function($g){		
		// 	if($g->model['positive_side'] == 'LT'){
		// 		$g->current_row['left_side'] = $g->model['name'];
		// 		$g->current_row['liablity_amount'] = $g->model['name'];
		// 	}	
		// });
	}

	function defaultTemplate(){
		return ['page/balancesheet'];
	}
}