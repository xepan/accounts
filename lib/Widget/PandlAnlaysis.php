<?php 

namespace xepan\accounts;

class Widget_PandlAnlaysis extends \xepan\base\Widget {
	
	function init(){
		parent::init();

		$this->report->enableFilterEntity('date_range');
		// $this->chart = $this->add('xepan\base\View_Chart');
		// $this->grid = $this->add('xepan\base\Grid');
		$this->chart = $this->add('xepan\base\View_Chart');
	}

	function recursiveRender(){
		$bsbalancesheet = $this->add('xepan\accounts\Model_BSBalanceSheet');
		$bsbalancesheet->addCondition('report_name','Profit & Loss');
		
		if(isset($this->report->start_date))
			$from_date = $this->report->start_date;
		if(isset($this->report->end_date))
			$to_date = $this->report->end_date;
		
		// $report = $bsbalancesheet->getPandL($from_date,$to_date);

		// $left=$report['left'];
		// var_dump($left);
		// $right=$report['right'];

		// $left_sum = $report['left_sum'];
		// $right_sum = $report['right_sum'];

		// $this->grid->setModel($report,['left','right','left_sum','right_sum']);
		$this->chart->setType('bar')
     		        ->setModel($bsbalancesheet,'name',['left','right'])
     		        ->setGroup(['left_sum','right_sum'])
     		        ->setTitle('Pandl Report')
     		        ->rotateAxis();
		return parent::recursiveRender();
	}
}