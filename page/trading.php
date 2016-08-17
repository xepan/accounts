<?php
namespace xepan\accounts;
class page_trading extends \xepan\base\Page{
	public $title="Trading Account";
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

		$bsbalancesheet = $view->add('xepan\accounts\Model_BSBalanceSheet');
		$report = $bsbalancesheet->getTradingBalance($from_date,$to_date);

		$left=$report['left'];
		$right=$report['right'];

		$left_sum = $report['left_sum'];
		$right_sum = $report['right_sum'];


		$grid_l = $view->add('xepan\hr\Grid',null,'balancesheet_liablity',['view\grid\balancesheet-liablity']);
		$grid_l->setSource($left);
		$grid_l->template->trySet('lheading','Expenditure');
		
		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet-assets']);
		$grid_a->template->trySet('rheading','Income');
		$grid_a->setSource($right);

		$view->template->trySet('ltotal',$left_sum);
		$view->template->trySet('atotal',$right_sum);

        $view->js('click')->_selector('.xepan-accounts-bs-group')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
	}
}