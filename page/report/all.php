<?php
namespace xepan\accounts;
class page_report_all extends page_report{
	public $title="Account Reports";

	public $possible_templates=[
				['name'=>'Ask from user before run','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Balance','value'=>'{{LB: LedgerHere(L) : AsOnDate(D) }}'],
				['name'=>'Group Balance','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Balance (Excluding Sub Groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Head Balance','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Dr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Cr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Dr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Cr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Dr (Excluding sub groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Cr (Excluding sub groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Head Dr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Head Cr','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Opening Balance','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Opening Balance','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Opening Balance (Excluding sub groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Transactions Dr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'Ledger Transactions Cr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Transactions Dr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Transactions Cr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Transactions Dr Sum (Excluding sub groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Group Transactions Cr Sum (Excluding sub groups)','value'=>'?(Your Variable to Ask)'],
				['name'=>'Head Transactions Dr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'Head Transactions Cr Sum','value'=>'?(Your Variable to Ask)'],
				['name'=>'PANDL BALANCE','value'=>'?(Your Variable to Ask)'],
				['name'=>'TRADING BALANCE','value'=>'?(Your Variable to Ask)'],

			];

	function page_index(){
		// parent::init();

		$update_report_vp = $this->add('VirtualPage');
		$update_report_vp->set([$this,'update_report_page']);

		$tag_helper = $this->add('VirtualPage')
				->set(function($page){
						$function_model = $this->add('xepan\accounts\Model_ReportFunction');

						$function_model->addExpression('display_value')->set(function($m,$q){
							return $q->expr('IF(list_of is null,CONCAT([name],":",[type]),CONCAT("loop:",[name]))',['name'=>$m->getElement('name'),'type'=>$m->getElement('type')]);
						});

						$function_model->addExpression('value')->set('name');

						$functions = $function_model->getRows();
						echo json_encode($functions);
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

			// $groups = $this->add('xepan\accounts\Model_Group')->getRows(['name']);
			// $ledgers = $this->add('xepan\accounts\Model_Ledger')->getRows(['name']);
			// $bshead = $this->add('xepan\accounts\Model_BalanceSheet')->getRows(['name']);

			$f = $crud->form->getElement('layout');
			$f->js_widget='xepan_account_report_richtext';
			// $f->extra_options=[$groups,$ledgers,$bshead];
			$f->addAjaxHelper($tag_helper->getURL(),['@']);
			// $f->addStaticHelperList($this->possible_templates,'@',false);
			// $f->addStaticHelperList($groups,'G',false);
			// $f->addStaticHelperList($ledgers,'L',false);
			// $f->addStaticHelperList($bshead,'H',false);

			$f->mention_options['insert'] = $this->js(null,'function(item) {return item.value; }');
			$f->mention_options['render'] = $this->js(null,"function(item) { return '<li><a href=\"javascript:;\"><span>'  + item.display_value + '</span></a></li>';}");

			$f->mention_options['items']=10000;
			$f->mention_options['delay']=100;

			// $f->setFieldHint('Selection Helpers @: possible templates G: Groups, L: Ledgers, H: BalanceSheet Heads ');
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

		$report_function_btn = $crud->addButton('Report Function')->addClass('btn btn-primary');
		$report_function_btn->js('click')->univ()
            ->frameURL('Adding new function',$this->app->url('xepan_accounts_reportfunction'));

        $report_function_btn = $crud->addButton('Update Default Reports & Functions')->addClass('btn btn-primary');
		$report_function_btn->js('click')->univ()
            ->frameURL('Update Default Reports & Functions',$this->app->url($update_report_vp->getURL()));


        $save_report_vp = $this->add('VirtualPage');
        $save_report_vp->set([$this,'save_report_vp']);

        $report_save_btn = $crud->addButton('Update Default Reports & Functions')->addClass('btn btn-danger');
		$report_save_btn->js('click')->univ()
            ->frameURL('Save Reports & Functions',$this->app->url($save_report_vp->getURL()));


	}

	function update_report_page($page){
		$page->add('View_Console')
			->set(function($c){
				$c->out('Loading Report Functions and Loops');

				$path = './vendor/xepan/accounts/defaultReports/';
		        $p = scandir($path); 
		        unset($p[0]);
		        unset($p[1]);

		        asort($p);
		        $i=2;
		        
		        foreach ($p as $file) {

		        	if($file=='xepan_functions.json'){
		        		$c->out($file.' -functions');
		        		$functions = json_decode(file_get_contents($path.$file),true);
		        		$function_model = $this->add('xepan\accounts\Model_ReportFunction');
		        		foreach ($functions as $f) {
		        			unset($f['id']);
		        			foreach ($f as $field => $value) {
		        				$function_model[$field] = $value;
		        			}

		        			$function_model->saveAndUnload();
		        		}
		        	}elseif($file === 'xepan_loops.json'){
		        		$c->out($file.' -loops');
		        		$loops = json_decode(file_get_contents($path.$file),true);
		        		foreach ($loops as $l) {
			        		$loops_model = $this->add('xepan\accounts\Model_ReportLoop');
		        			unset($l['id']);
		        			foreach ($l as $field => $value) {
		        				$loops_model[$field] = $value;
		        			}
		        			$loops_model['type']='Loop';
		        			$loops_model->saveAndUnload();
		        		}
		        	}else{
		        		$c->out($file.' -report');
		        		$model = $this->add('xepan\accounts\Model_Report_Layout');
		        		$model->addCondition('name',str_replace(".json", '', $file));
		        		$model->tryLoadAny();
		        		$model['layout'] = json_decode(file_get_contents($path.$file),true)['layout'];
						$model->saveAndUnload();
			        }
			    }


			});
	}


	function save_report_vp($page){
		$page->add('View_Console')
			->set(function($c){
				$c->out('Saving Report Functions, Loops and Reports');
				$function_model = $this->add('xepan\accounts\Model_ReportFunction');
				$file_contents = $function_model->getRows();
				file_put_contents('./vendor/xepan/accounts/defaultReports/xepan_functions.json', json_encode($file_contents));

				$loop_model = $this->add('xepan\accounts\Model_ReportLoop');
				$file_contents = $loop_model->getRows();
				file_put_contents('./vendor/xepan/accounts/defaultReports/xepan_loops.json', json_encode($file_contents));

				$model = $this->add('xepan\accounts\Model_Report_Layout');
				foreach ($model as $m) {
					$file_contents = $m->data;
					file_put_contents('./vendor/xepan/accounts/defaultReports/'.$m['name'].'.json', json_encode($file_contents));
					$c->out('Saved report - '.$m['name']);
				}
			});	
	}

}