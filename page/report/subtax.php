<?php
namespace xepan\accounts;
class page_report_subtax extends page_report{
	public $title="Sub Tax Reports";
	public $sub_tax_amount = [];
	function init(){
		parent::init();

		$form = $this->add('Form');
		$form->addField('DatePicker','from_date');
		$form->addField('DatePicker','to_date');

		$form->addSubmit('Submit');

		$view = $this->add('View');
		if($_GET['reload']){
			$taxation_model = $this->add('xepan\commerce\Model_Taxation');
			$taxation = [];
			foreach ($taxation_model as $model) {
				$taxation[$model->id] = $model['percentage'];
			}

			$root_group = $this->add('xepan\accounts\Model_Group')->loadBy('name','Tax Payable');
			
			$transaction_row = $this->add('xepan/accounts/Model_TransactionRow');
			$transaction_row->addCondition('group_path','like',$root_group['path'].'%');
			
			$grid = $view->add('xepan/hr/Grid');
			if($_GET['from_date'])
				$transaction_row->addCondition('created_at','>=',$this->app->stickyGET('from_date'));
			if($_GET['to_date'])
				$transaction_row->addCondition('created_at','<',$this->app->nextDate($this->app->stickyGET('to_date')));
			
			$transaction_row->addExpression('cr_sum')->set(function($m,$q){
				return $q->expr('sum([0])',[$m->getElement('amountCr')]);
			});

			$transaction_row->addExpression('dr_sum')->set(function($m,$q){
				return $q->expr('sum([0])',[$m->getElement('amountDr')]);
			});

			$transaction_row->addExpression('taxation_id')->set(function($m,$q){
				return  $m->refSQL('ledger_id')->fieldQuery('related_id');
			});

			$transaction_row->_dsql()->group('ledger_id,remark');
			
			$sub_tax_view = $view->add('View_Info');
			$grid->sub_tax_amount = [];
			$grid->addHook('formatRow',function($g)use($taxation,$sub_tax_view){
				$remark_array = explode(",", $g->model['remark']);

				foreach ($remark_array as $index => $remark) {

					if(!$taxation[$g->model['taxation_id']])
						continue;

					$sub_tax = explode("-", $remark);
					
					$amount = ($g->model['cr_sum'] * trim($sub_tax[1])) / $taxation[$g->model['taxation_id']];
					$g->sub_tax_amount[$remark] += $amount;
				}
				$sub_tax_view->set(print_r($g->sub_tax_amount,true));
			});

			$grid->setModel($transaction_row,['ledger','remark','cr_sum','taxation_id']);

		}


		if($form->isSubmitted()){

			$view->js(null,$form->js()->reload())->reload([
					'from_date'=>$form['from_date']?:'0',
					'to_date'=>$form['to_date']?:'0',
					'reload'=>1
				])->execute();
		}

	}
}