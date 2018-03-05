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
		// ?(ABC)  OR ?(ABC:Date)
		$ask_variables=[];
		preg_match_all('^\?\((.*?)\)^', $layout, $ask_variables);

		// final report layout view
		$report_layout = $this->add('View');

		// OR !count($ask_variables[1])
		if($_GET['show_report']){
			
			$this->reportfunctionValue = $this->app->recall('reportfunctionValue');
			$this->app->forget('reportfunctionValue');

			// solving loop values
			$layout = $this->implementLoopFunction($layout);
			
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

				// checking assignment operator
				$assignment = explode("=", $sub_expression);
				
				if(count($assignment) >= 2 ){
					$assignment_variable = $assignment[0];
					$sub_expression = $assignment[1];
				}

				$function_objects = explode(" ", $sub_expression);
				foreach ($function_objects as $key => $function) {

						$fun_normlize_name = $this->app->normalizeName($function);

						if(!isset($this->reportfunctionValue[$fun_normlize_name])){
							// check for if function exist or not
							$function_model = $this->add('xepan\accounts\Model_ReportFunction');
							$function_model->addCondition('name',$function);
							$function_model->tryLoadAny();

							if(!$function_model->loaded()) continue;
							
							$this->reportfunctionValue[$fun_normlize_name] = $function_model->getResult();
						}
					$layout  = str_replace($function,$this->reportfunctionValue[$fun_normlize_name], $layout);
				}
			}

			// eval or math eval for executing  expression like [[ 200 * 90 - 10]];
			preg_match_all('^\[\[(.*?)\]\]^', $layout, $matches);
			$eval_math = new \Webit\Util\EvalMath\EvalMath;
			foreach ($matches[1] as $key => $eval_str) {
				// // checking assignment operator
				$assignment = explode("=", $eval_str);
				$assignment_variable = 0;
				if(count($assignment) >= 2 ){
					$assignment_variable = $this->app->normalizeName($assignment[0]);
					// $eval_str = $assignment[1];
				}

				if(isset($assignment_variable) AND isset($this->reportfunctionValue[$assignment_variable]) ){
					$result = $this->reportfunctionValue[$assignment_variable];
				}else{

					try{
						$result = $eval_math->evaluate($eval_str);
					}catch(\Exception $e){
						$result = $eval_str;
					}
				}

				$layout = str_replace("[[".$eval_str."]]",$result, $layout);

				// save assignment variable into array
				if($assignment_variable AND !isset($this->reportfunctionValue[$assignment_variable])){
					$this->reportfunctionValue[$assignment_variable] = $result;
				}
			}
			$report_layout->setHtml($layout);

		}else{

			$form = $this->add('Form');
			foreach ($ask_variables[1] as $key => $name) {
				$name_with_type = explode(":", $name);
				$nor_name = $this->app->normalizeName($name_with_type[0]);
				if(count($name_with_type) ==2 && strtolower($name_with_type[1]) == 'date')
					$field = $form->addField('DatePicker',$nor_name)->validate('required');
				else
					$field = $form->addField('line',$nor_name)->validate('required');

				if(isset($this->reportfunctionValue[$nor_name]))
					$field->set($this->reportfunctionValue[$nor_name]);
			}

			$config_date = ['FY_Start','FY_End','Current_Month_Start','Current_Month_End'];
			
			$config_model = $this->add('xepan\base\Model_ConfigJsonModel',
				        [
				            'fields'=>[
				                        'FY_Start'=>'DatePicker',
				                        'FY_End'=>'DatePicker',
				                        'Current_Month_Start'=>'DatePicker',
				                        'Current_Month_End'=>'DatePicker',
				                        ],
				                'config_key'=>'Accounts_Report_Config_Date',
				                'application'=>'accounts'
				        ]);
        	$config_model->tryLoadAny();
        	
			foreach ($config_date as $field_name) {
				$field = $form->addField('DatePicker',$field_name)->validate('required');
				if(isset($config_model[$field_name]))
					$field->set($config_model[$field_name]);
			}

			$form->addSubmit('Next');
			if($form->isSubmitted()){
				foreach ($form->getAllFields() as $key => $value) {
					$this->reportfunctionValue[$key] = $value;
				}

				// save config dates
				foreach ($config_date as $field_name) {
					$config_model[$field_name] = $form[$field_name];
				}
				$config_model->save();

				$this->app->memorize('reportfunctionValue',$this->reportfunctionValue);
				// $js = [
				// 		$report_layout->js()->reload(['show_report'=>1]),
				// 		$form->js()->hide()
				// 	];
				// $form->js(null,$js)->execute();
				$this->app->redirect($this->app->url(null,['show_report'=>1]));
			}
		}
	}

	function implementLoopFunction($layout){

		// preg match string
		// [[loop:Loop1:Name = {$name} Type = {$ledger_type}]]
		preg_match_all('^\[\[loop:(.*?)\]\]^', $layout, $matches);
		
		// echo "<pre>";
		// print_r($matches);
		// echo "</pre>";
		// die();


		foreach ($matches[1] as $key => $loop_str) {

			$temp_array = explode(":", $loop_str);
			// echo "<pre>";
			// print_r($temp_array);
			// echo "</pre>";
			// // die();			
			$loop_function = $temp_array[0];
			$loop_template = $temp_array[1];
			
			$loop_model = $this->add('xepan\accounts\Model_ReportLoop');
			$loop_model->addCondition('name',$loop_function);
			$loop_model->tryLoadany();
			if(!$loop_model->loaded())
				throw new \Exception("Report Funciton Loop named ".$loop_function." not defined");
			
			$list_model = $loop_model->getListModel();

			$controller = $this->add('AbstractController');

			$loop_template = '{rows}{row}'.$loop_template.'{/row}{/rows}';
			$temp = $controller->add('GiTemplate');
			$temp->loadTemplateFromString($loop_template);

			$lister = $controller->add('CompleteLister',null,null,$temp);
			$lister->setModel($list_model);

			$layout = str_replace($matches[0][$key], $lister->getHtml(), $layout);
			$lister->destroy();
		}

		return $layout;
	}
}