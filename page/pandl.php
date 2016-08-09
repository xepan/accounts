<?php
namespace xepan\accounts;
class page_pandl extends \xepan\base\Page{
	public $title="Profit And Loss";
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
		$bsbalancesheet->addCondition('report_name','Profit & Loss');

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

		if($gross_profit >= 0){
			$left[] = ['name'=>'Gross Profit','amount'=>abs($gross_profit)];	
		}

		if($gross_loss > 0){
			$right[] = ['name'=>'Gross Loss','amount'=>abs($gross_loss)];
		}

		$grid_l = $view->add('xepan\hr\Grid',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->template->trySet('lheading','Expenses\Loss');
		$grid_l->setSource($left);

		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Income\Profit');
		$grid_a->setSource($right);
	
        $view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->location([$this->app->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->data('id'),'from_date'=>$from_date,'to_date'=>$to_date]);

	}
}