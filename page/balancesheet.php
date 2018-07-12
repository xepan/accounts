<?php
namespace xepan\accounts;
class page_balancesheet extends \xepan\base\Page{
	public $title="Balance Sheet";
	function init(){
		parent::init();

		// $this->add('xepan\accounts\Model_BalanceSheet')->loadDefaults();
		// $this->add('xepan\accounts\Model_Group')->loadDefaults();
		// $this->add('xepan\accounts\Model_Ledger')->loadDefaults();

		// return;


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
		$acl = $bsbalancesheet->add('xepan\hr\Controller_ACL',['skip_allow_add'=>true]);
		if(!$acl->canView()) {
			$this->add('View_Error')->set('You are not authorised for this view');
			return ;
		}

		$report = $bsbalancesheet->getBalanceSheet($from_date,$to_date);

		$left=$report['left'];
		$right=$report['right'];

		$left_sum = $report['left_sum'];
		$right_sum = $report['right_sum'];

		if($left_sum < $right_sum){
			if(($right_sum-$left_sum)>0.01){
				$left[] = ['name'=>'ERROR','amount'=>($right_sum-$left_sum),'id'=>'','type'=>'-'];
				$left_sum += ($right_sum-$left_sum);
			}
		}

		if($left_sum > $right_sum){
			if(($left_sum-$right_sum)>0.01){
				$right[] = ['name'=>'ERROR','amount'=>($left_sum-$right_sum),'id'=>'','type'=>'-'];
				$right_sum += ($left_sum-$right_sum);
			}
		}


		$grid_l = $view->add('xepan\hr\Grid',null,'balancesheet_liablity',['view\grid\balancesheet']);
		$grid_l->setSource($left);
		$grid_l->template->trySet('lheading','Liablities');
		
		$grid_a = $view->add('xepan\hr\Grid',null,'balancesheet_assets',['view\grid\balancesheet']);
		$grid_a->template->trySet('rheading','Assets');
		$grid_a->setSource($right);

		$view->template->trySet('ltotal',$left_sum);
		$view->template->trySet('atotal',$right_sum);

        $view->js('click')->_selector('.xepan-accounts-bs-group.bsrow')->univ()->frameURL('BalanceSheet Head Groups',[$this->api->url('xepan_accounts_bstogroup'),'bs_id'=>$this->js()->_selectorThis()->closest('[data-id]')->data('id'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
        $view->js('click')->_selector('.xepan-accounts-bs-group.pandl')->univ()->frameURL('PANDL',[$this->api->url('xepan_accounts_pandl'), 'from_date'=>$from_date, 'to_date'=>$to_date]);
        $view->js('click')->_selector('.xepan-accounts-bs-group.op_pandl')->univ()->frameURL('Openning PANDL',[$this->api->url('xepan_accounts_pandl'), 'from_date'=>'1970-01-01', 'to_date'=>$this->app->previousDate($from_date)]);
        $view->js(true)->_selector('td:contains(ERROR), tr:contains(ERROR)')->css(['color'=>'red','font-weight'=>'bold']);
	}
}