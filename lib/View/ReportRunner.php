<?php

namespace xepan\accounts;


class View_ReportRunner extends \View {
	public $from_date=null;
	public $to_date=null;

	function init(){
		parent::init();
		$this->from_date = $this->app->stickyGET('from_date');
		$this->to_date = $this->app->stickyGET('to_date');

		$form = $this->add('Form');
		$form->addField('xepan\base\RichText','sample');
		$form->addSubmit('DO');

		$view = $this->add('View');
		$c = $view->add('xepan\accounts\Controller_ExpressionSolver');

		if($_GET['sample']){
			$c->setContent($_GET['sample']);
			$c->solve();
		}

		$view->set($c->getContent());

		$form->onSubmit(function($f)use($view){
			return $view->js()->reload(['sample'=>$f['sample']]);
		});


	}



	function recursiveRender1(){
		
		$template = $this->add('GiTemplate');
		$template->loadTemplate('view/report/balancesheet');
		$temp = $template->render();

		$this->setHTML($temp);
		return parent::recursiveRender();
		
		/*Find [{}] String from template*/

		// preg_match_all("/{([^:}]*):?([^}]*)}/", $temp,$match);		
		// preg_match_all("/\[[^\]]*\]/", $temp,$match);		
		preg_match_all('/( { ( (?: [^{}]* | (?1) )* ) } )/x', $temp,$match);		

		/*Include EvalMath Class*/
		$x = new \Webit\Util\EvalMath\EvalMath;

		/*Get value from specific BalanceSheet , Group And Ledger save in array*/
		$array_model=[];
		foreach ($match[2] as  $m) {
			$value=explode(":", $m);
			switch ($value[0]) {
				case 'HEAD':
					$model = $this->add('xepan\accounts\Model_BalanceSheet')
						->addCondition('name',$value[1])
						->tryLoadAny();
					$array_model[$value[0].':'.$value[1]] =  $model->getBalance($this->from_date,$this->to_date); 	
					break;
				case 'GROUP':
					$model = $this->add('xepan\accounts\Model_Group')
						->addCondition('name',$value[1])
						->tryLoadAny();
					$array_model[$value[0].':'.$value[1]] =  $model->getBalance($this->from_date,$this->to_date); 	
					break;
				case 'LEDGER':
					$model = $this->add('xepan\accounts\Model_Ledger')
						->addCondition('name',$value[1])
						->tryLoadAny();
					$array_model[$value[0].':'.$value[1]] =  $model->getBalance($this->from_date,$this->to_date); 	
							
					break;	
			}
		}

		/*Find Actual Value From Template String & Eval */

		$result = str_replace($match[0],$array_model,$temp);
		preg_match_all("/\[[^\]]*\]/", $result,$res);
		
		$r=[];
		foreach ($res[0] as  $str) {
			$res=substr($str, 1, -1);
			if($res){
				// echo $res. '<br/>';
				$r[$str]= $x->evaluate($res);
			}
		}

		/*set Final Value From String */

		foreach ($r as $key => $value) {
			$result = str_replace($key, $value, $result);
		}
		$this->setHTML($result);

		parent::recursiveRender();


	}
}