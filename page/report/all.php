<?php
namespace xepan\accounts;
class page_report_all extends page_report{
	public $title="Account Reports";


	function init(){
		parent::init();

		$tag_helper = $this->add('VirtualPage')
				->set(function($page){
					if($_GET['delimiter']=='!')
						echo json_encode([['name'=>'{$a !}'],['name'=>'{$b !}'],['name'=>'{$c !}']]);
					if($_GET['delimiter']=='$')
						echo json_encode([['name'=>'{$a $}'],['name'=>'{$b $}'],['name'=>'{$c $}']]);
					exit;
				});

		$crud = $this->add('xepan\hr\CRUD',
						null,
						null,
						['view\report\all-report-grid']
					);
		
		$model = $this->add('xepan\accounts\Model_Report_Layout');
		$crud->setModel($model);
		$run_executer = $crud->grid->addColumn('button','Run');

		if($crud->isEditing()){
			$groups = $this->add('xepan\accounts\Model_Group')->getRows(['name']);
			$ledgers = $this->add('xepan\accounts\Model_Ledger')->getRows(['name']);
			$bshead = $this->add('xepan\accounts\Model_BalanceSheet')->getRows(['name']);

			$f = $crud->form->getElement('layout');
			$f->addStaticHelperList($groups,'G',false);
			$f->addStaticHelperList($ledgers,'L',false);
			$f->addStaticHelperList($bshead,'H',false);

			$f->mention_options=[
					'insert'=>$this->js(null,'function(item){return "<span>" + item.id +":(" + item.name + ")</span>";}'),
					'render'=>$this->js(null,"function(item) { return '<li>' +'<a href=\"javascript:;\"><span>' + item.id+ ' : ' + item.name + '</span></a>' +'</li>';}")
				];
			$f->mention_options['items']=10000;
			$f->mention_options['delay']=100;

			$f->setFieldHint('Selection Helpers G: Groups, L: Ledgers, H: BalanceSheet Heads ');
		}

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
            ->frameURL('Adding new function',$this->app->url('xepan_accounts_reportfunction'));


	}

}