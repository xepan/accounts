<?php
namespace xepan\accounts;
class page_balancesheet extends \xepan\base\Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();

		$f=$this->add('Form',null,null,['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date');
		$r->addField('DatePicker','to_date');
		$f->addSubmit('Filter')->addClass('btn btn-primary btn-block');

		$view = $this->add('View',null,null,['page/balancesheet']);
		$view_url = $this->api->url(null,['cut_object'=>$view->name]);

		if($f->isSubmitted()){
			return $view->js()->reload(['from_date'=>$f['from_date'],'to_date'=>$f['to_date']],null,$view_url);
		}

		$bsbalancesheet = $view->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']]);
		$bsbalancesheet->addCondition('report_name','BalanceSheet');

		$left=[];
		$right=[];

		$left_sum=0;
		$right_sum=0;

		foreach ($bsbalancesheet as $bs) {
			if($bs['subtract_from']=='CR'){
				$amount  = $bs['ClosingBalanceCr'] - $bs['ClosingBalanceDr'];
			}else{
				$amount  = $bs['ClosingBalanceDr'] - $bs['ClosingBalanceCr'];
			}
			if($amount >=0 && $bs['positive_side']=='LT'){
				$left[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
				$left_sum += abs($amount);
			}else{
				$right[] = ['name'=>$bs['name'],'amount'=>abs($amount),'id'=>$bs['id']];
				$right_sum += abs($amount);
			}
		}
		
		// get Trading

		// Add P&L
		$profit = 0;
		$loss=0;
		$pandl = $view->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']]);
		$pandl->addCondition('report_name','Profit & Loss');

		foreach ($pandl as $pl) {
			if($pl['subtract_from']=='CR'){
				$amount  = $pl['ClosingBalanceCr'] - $pl['ClosingBalanceDr'];
			}else{
				$amount  = $pl['ClosingBalanceDr'] - $pl['ClosingBalanceCr'];
			}
			if($amount >=0 && $pl['positive_side']=='LT'){
				$left_sum += abs($amount);
				$profit += abs($amount);
			}else{
				$right_sum += abs($amount);
				$loss += abs($amount);
			}
		}

		if($profit >= 0){
			$left[] = ['name'=>'Profit','amount'=>abs($profit)];	
		}

		if($loss > 0){
			$right[] = ['name'=>'Loss','amount'=>abs($loss)];
		}

		$grid_l = $view->add('xepan\hr\Grid',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setSource($left);

		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->setSource($right);
	
        $view->on('click','.xepan-accounts-bs-group',function($js,$data){
            return $js->univ()->redirect($this->app->url('xepan_accounts_bstogroup',['bs_id'=>$data['id']]));
        });
	}
}