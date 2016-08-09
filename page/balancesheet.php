<?php
namespace xepan\accounts;
class page_balancesheet extends \xepan\base\Page{
	public $title="Account Balance Sheet";
	function init(){
		parent::init();

		$fy=$this->app->getFinancialYear();
		
		$from_date = $this->api->stickyGET('from_date')?:$fy['start_date'];
		$to_date = $this->api->stickyGET('to_date')?:$fy['end_date'];

		$f=$this->add('Form',null,null,['form/stacked']);
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date')->set($from_date);
		$r->addField('DatePicker','to_date')->set($to_date);
		$f->addSubmit('Filter')->addClass('btn btn-primary btn-block');

		$view = $this->add('View',null,null,['page/balancesheet']);

		if($f->isSubmitted()){
			return $view->js()->reload(['from_date'=>$f['from_date']?:0,'to_date'=>$f['to_date']?:0])->execute();
		}

		$bsbalancesheet = $view->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$from_date,'to_date'=>$to_date]);
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

		$gross_profit =0;
		$gross_loss = 0;

		$trade = $view->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$_GET['from_date'],'to_date'=>$_GET['to_date']]);
		$trade->addCondition('report_name','Trading');

		foreach ($trade as $tr) {
			if($tr['subtract_from']=='CR'){
				$amount  = $tr['ClosingBalanceCr'] - $tr['ClosingBalanceDr'];
			}else{
				$amount  = $tr['ClosingBalanceDr'] - $tr['ClosingBalanceCr'];
			}
			if($amount >=0 && $tr['positive_side']=='LT'){
				$left_sum += abs($amount);
				$gross_profit += abs($amount);
			}else{
				$right_sum += abs($amount);
				$gross_loss += abs($amount);
			}
		}


		// Add P&L
		$profit = $gross_profit;
		$loss=$gross_loss;
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
		$grid_l->template->trySet('lheading','Liablities');
		
		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Assets');
		$grid_a->setSource($right);
		
		$ltotal = 0;
		foreach ($left as $lib) {
			$ltotal += $bs['amount'];
		}

		$atotal = 0;
		foreach ($right as $ass) {
			$atotal += $bs['amount'];
		}

		$view->template->trySet('ltotal',$ltotal);
		$view->template->trySet('atotal',$atotal);

        $view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}