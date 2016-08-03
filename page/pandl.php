<?php
namespace xepan\accounts;
class page_pandl extends \xepan\base\Page{
	public $title="Account Profit & Loss";
	function init(){
		parent::init();
		
		
		$f=$this->add('Form',null,'form');
		$c=$f->add('Columns')->addClass('row xepan-push');
		$l=$c->addColumn(6)->addClass('col-md-6');
		$r=$c->addColumn(6)->addClass('col-md-6');
		$l->addField('DatePicker','from_date');
		$r->addField('DatePicker','to_date');
		$f->addSubmit('Go')->addClass('btn btn-primary xepan-push');


		$pandl = $this->add('xepan\accounts\Model_TransactionRow');
		$pandl->addExpression('DR')->set($pandl->dsql()->expr('sum(IFNULL([0],0))',[$pandl->getElement('amountDr')]));
		$pandl->addExpression('CR')->set($pandl->dsql()->expr('sum(IFNULL([0],0))',[$pandl->getElement('amountCr')]));
		$pandl->_dsql()->group(['balance_sheet_id','group','ledger']);
		$pandl->addCondition('is_pandl',true);

		// $pandl->addCondition('created_at','>=',$fy['start']);
		// $pandl->addCondition('created_at','<',$naxtday_of_selected_day);

		// $grid = $this->add('xepan\hr\Grid');
		// $grid->setModel($pandl,['balance_sheet_id','balance_sheet','is_pandl','group','ledger','CR','DR']);
		// $grid->js(true)->find('table')->css('width','100%')->attr('border','1px')->attr('cell-padding','0.5em');
		// return;
		
		$expenses_sum = 0;
		$income_sum = 0;
		foreach ($pandl as $tr) {
			$subtract_from = $tr['subtract_from'];			
			$subtract = $subtract_from=='DR'?'CR':'DR';
			if(($amount = $tr[$subtract_from] - $tr[$subtract])>=0){
				$side='assets'; // expenses for pandl
				$expenses_sum += abs($amount);
			}else{
				$side='liabilities'; // income for pandl
				$income_sum += abs($amount);
			}

			$this->add('View',null,$side.'_name')->set($tr['balance_sheet']);
			$this->add('View',null,$side.'_amount')->set(abs($amount));
		}

		if($income_sum > $expenses_sum){
			$profit = abs($income_sum - $expenses_sum);
			$loss=0;
			$this->add('View',null,'assets_name')->set('PROFIT');
			$this->add('View',null,'assets_amount')->set($profit);
		}else{
			$loss=abs($income_sum - $expenses_sum);
			$profit=0;
			$this->add('View',null,'liabilities_name')->set('LOSS');
			$this->add('View',null,'liabilities_amount')->set($loss);
		}

		// Show Sum

		$this->add('HR',null,'assets_name');
		$this->add('HR',null,'assets_amount');

		$this->add('HR',null,'liabilities_name');
		$this->add('HR',null,'liabilities_amount');

		$this->add('View',null,'assets_name')->set('TOTAL');
		$this->add('View',null,'assets_amount')->set($expenses_sum+$profit);

		$this->add('View',null,'liabilities_name')->set('TOTAL');
		$this->add('View',null,'liabilities_amount')->set($income_sum+$loss);



	}

	function defaultTemplate(){
		return ['page/pandl'];
	}
}