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

			$this->app->redirect($this->app->url('xepan_accounts_report_executer',['layout'=>$_GET['Run']]));

			// $rl_model = $this->add('xepan\accounts\Model_Report_Layout');
			// $rl_model->load($_GET['Run']);
			// $layout = $rl_model['layout'];
			// $matches = [];

			// // nested [[ ]] square bracket not required, work only for single square bracket with arithmathic operator
			// preg_match_all('^\[\[(.*?)\]\]^', $layout, $matches);

			// //  replacing all values with function result values like Function1 replace by 100 getting it's value from getResult Function
			// foreach ($matches[1] as $key => $sub_expression) {
			// 	$function_objects = explode(" ", $sub_expression);
			// 	foreach ($function_objects as $key => $function) {

			// 			if(!isset($this->reportfunctionValue[$function])){
			// 				// check for if function exist or not
			// 				$function_model = $this->add('xepan\accounts\Model_ReportFunction');
			// 				$function_model->addCondition('name',$function);
			// 				$function_model->tryLoadAny();

			// 				if(!$function_model->loaded()) continue;
							
			// 				$this->reportfunctionValue[$function] = $function_model->getResult();
			// 			}
			// 		$layout  = str_replace($function,$this->reportfunctionValue[$function], $layout);
			// 	}
			// }

			// // eval or math eval for executing  expression like [[ 200 * 90 - 10]];
			// preg_match_all('^\[\[(.*?)\]\]^', $layout, $matches);
			// $eval_math = new \Webit\Util\EvalMath\EvalMath;
			// foreach ($matches[1] as $key => $eval_str) {
			// 	$result = $eval_math->evaluate($eval_str);
			// 	$layout = str_replace("[[".$eval_str."]]",$result, $layout);
			// }
		}

		$report_function_btn = $crud->addButton('Report Function');
		$report_function_btn->js('click')->univ()
            ->frameURL(
                'Adding new function',
                'xepan\accounts\reportfunction'
            );


	}

}