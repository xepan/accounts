<?php
namespace xepan\accounts;
class page_report_executer extends page_report{
	public $title="Report Viewer";
	public $reportfunctionValue = [];

	function init(){
		parent::init();

		$this->app->stickyGET('layout');

		$rl_model = $this->add('xepan\accounts\Model_Report_Layout');
		$rl_model->addCondition('id',$_GET['layout']);
		$rl_model->tryLoadAny();

		if(!$rl_model->loaded()){
			$this->add('View_Error')->set('report not found');
			return;
		}

		$layout = $rl_model['layout']; 

		// ask variable from customer
		$ask_variables=[];
		preg_match_all('^\?\((.*?)\)^', $layout, $ask_variables);

		$report_layout = $this->add('View');

		if($_GET['show_report']){

			$this->reportfunctionValue = $this->app->recall('reportfunctionValue');
			$this->app->forget('reportfunctionValue');

			// replacing ask values with input values
			foreach($ask_variables[1] as $key => $name) {
				$nor_name = $this->app->normalizeName($name);
				// replacing values with 
				$actual_name = $ask_variables[0][$key];
				$layout = str_replace($actual_name, $this->reportfunctionValue[$nor_name], $layout);
			}

			// nested [[ ]] square bracket not required, work only for single square bracket with arithmathic operator
			$matches = [];
			preg_match_all('^\[\[(.*?)\]\]^', $layout, $matches);
			//replacing all values with function result values like Function1 replace by 100 getting it's value from getResult Function
			foreach ($matches[1] as $key => $sub_expression) {
				$function_objects = explode(" ", $sub_expression);
				foreach ($function_objects as $key => $function) {

						if(!isset($this->reportfunctionValue[$function])){
							// check for if function exist or not
							$function_model = $this->add('xepan\accounts\Model_ReportFunction');
							$function_model->addCondition('name',$function);
							$function_model->tryLoadAny();

							if(!$function_model->loaded()) continue;
							
							$this->reportfunctionValue[$function] = $function_model->getResult();
						}
					$layout  = str_replace($function,$this->reportfunctionValue[$function], $layout);
				}
			}

			// eval or math eval for executing  expression like [[ 200 * 90 - 10]];
			preg_match_all('^\[\[(.*?)\]\]^', $layout, $matches);
			$eval_math = new \Webit\Util\EvalMath\EvalMath;
			foreach ($matches[1] as $key => $eval_str) {
				$result = $eval_math->evaluate($eval_str);
				$layout = str_replace("[[".$eval_str."]]",$result, $layout);
			}
			$report_layout->setHtml($layout);

		}elseif(count($ask_variables[1])){

			$form = $this->add('Form');
			foreach ($ask_variables[1] as $key => $name) {
				$nor_name = $this->app->normalizeName($name);
				$field = $form->addField('line',$nor_name)->validate('required');
				if( isset($this->reportfunctionValue[$nor_name]))
					$field->set($this->reportfunctionValue[$nor_name]);
			}

			$form->addSubmit('Next');
			if($form->isSubmitted()){
				foreach ($form->getAllFields() as $key => $value) {
					$this->reportfunctionValue[$key] = $value;
				}

				$this->app->memorize('reportfunctionValue',$this->reportfunctionValue);
				$js = [
						$report_layout->js()->reload(['show_report'=>1]),
						$form->js()->hide()						
					];
				$form->js(null,$js)->execute();
			}
		}

		

	}
}