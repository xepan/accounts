<?php
namespace xepan\accounts;
class page_report_all extends page_report{
	public $title="Account Reports";

	function init(){
		parent::init();

		$crud = $this->add('xepan\hr\CRUD',
						null,
						null,
						['view\report\all-report-grid']
					);
		$model = $this->add('xepan\accounts\Model_Report_Layout');
		$crud->setModel($model);
		$run_executer = $crud->grid->addColumn('button','Run');

		if($_GET['Run']){
			throw new \Exception("Error Processing Request", 1);
			
		}
	}
}