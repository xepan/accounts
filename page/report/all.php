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

	function init(){
		parent::init();

		// $tag_helper = $this->add('VirtualPage')
		// 		->set(function($page){
		// 			if($_GET['delimiter']=='!')
		// 				echo json_encode([['name'=>'{$a '. $_GET['query'] .'}'],['name'=>'{$b !}'],['name'=>'{$c !}']]);
		// 			if($_GET['delimiter']=='$')
		// 				echo json_encode([['name'=>'{$a $}'],['name'=>'{$b $}'],['name'=>'{$c $}']]);
		// 			exit;
		// 		});

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
			$f->js_widget='xepan_account_report_richtext';
			$f->extra_options=[$groups,$ledgers,$bshead];
			// $f->addAjaxHelper($tag_helper->getURL(),['!','$']);
			$f->addStaticHelperList($this->possible_templates,'@',false);
			$f->addStaticHelperList($groups,'G',false);
			$f->addStaticHelperList($ledgers,'L',false);
			$f->addStaticHelperList($bshead,'H',false);

			$f->mention_options=[
					'insert'=>$this->js(null,'function(item){
						 if(item.hasOwnProperty("value")) return item.value; else return "<span>" + item.id +":(" + item.name + ")</span>";
						 }
						 	'),
					'render'=>$this->js(null,"function(item) { return '<li>' +'<a href=\"javascript:;\"><span>' + item.id+ ' : ' + item.name + '</span></a>' +'</li>';}")
				];
			$f->mention_options['items']=10000;
			$f->mention_options['delay']=100;

			$f->setFieldHint('Selection Helpers @: possible templates G: Groups, L: Ledgers, H: BalanceSheet Heads ');
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