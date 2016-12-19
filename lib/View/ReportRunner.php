<?php

namespace xepan\accounts;


class View_ReportRunner extends \View {
	public $from_date=null;
	public $to_date=null;

	function init(){
		parent::init();
		$this->from_date = $this->app->stickyGET('from_date');
		$this->to_date = $this->app->stickyGET('to_date');

	}

	/*
	
	[[[ 

	VAR_N = {
	BALANCE/DR/CR/LOOP/Row_Field_Name
	:: HEAD/GROUP/GROUPONLY/LEDGER
	:: name=Current Assets,OR_Name2/group=ABCD,OR_XYZ/head=head1,OR_head2
	:: from_date/FYSTART/var1
	:: to_date/FYEND/var2
	:: DRPLUS/CRPLUS
	:: template(HTML WITH EXPRESSIONS)
	}
	
	]]]

	*/
	function solveBlock($block){
		// check if we have variable
		// fetch expression
		// $this->solveExpression(expression)
		// if we have variable ... setVar
		// return final value
	}

	/*
	{
	BALANCE/DR/CR/LOOP/Row_Field_Name     	// 	RETURN_TYPE
	:: HEAD/GROUP/GROUPONLY/LEDGER 			//	BaseModel
	:: name=Current Assets,OR_Name2/group=ABCD,OR_XYZ/head=head1,OR_head2	//	Condition
	:: from_date/FYSTART/var1				//	FROM_DATE
	:: to_date/FYEND/var2					//	TO_DATE
	:: DRPLUS/CRPLUS						//	POSITIVE_SIDE
	:: template(HTML WITH EXPRESSIONS)		//	TEMPLATE
	}

	1. {DR::GROUP::name=Sundary Debtors::from_date::to_date::DRPLUS}-{CR::GROUP::name=Sundary Debtor::FYSTART::FYEND::CRPLUS}
	2. {name}  ---- {{BalanceDR} - {BalanceCR}}
	*/

	function solveExpression($expression){
		/* 	
			foreach(sub expressions by taking {}){
				expression_replace by value returned = $this->solveExpression($expression);
			}

			explode by ::
			swicth RETURN_TYPE
			case BALANCE/DR/CR
				$obj = $this->getBaseModel()
				condition_base = explode by '='
				swicth condition_base[0]
					case name
						$obj->addCondition('name',condition_base[1])
					case group
						$obj->addCondition('group',condition_base[1])
					case head
						$obj->addCondition('head',condition_base[1])
					default 
						throw exception, not ccepted value

				switch from_date & to_date
					case is_date(from_date)
						$obj->addCondition('from_date','>=',$from_date);
					case "FYSTART"
						$obj->addCondition('from_date','>=',$this->app->getFY($from_date,'start'));
					default // Looks like it is a var
						$from_date = $this->getVar($from_date);
						if($from_date) $obj->addCondition('from_date','>=',$from_date);

				$obj->tryLoadAny();
				// Get value required based on balance/dr/cr
				return $obj[]

			case LOOP
			default // may be row_field_name
		*/
	}

	/*
	Remove start closing </p> tag or <br/><br> and remove end <p> tag kind of set if any 
	*/
	function senitizeTemplate($template){

	}

	function recursiveRender(){
		
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