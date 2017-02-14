<?php

namespace xepan\accounts;

class Controller_ExpressionSolver extends \AbstractController {
	public $content=null;

	function setContent($content){
		$this->content = $content;
	}

	function getContent(){
		return $this->content;
	}


	function solve(){

	}


	// Get first level blocks in given strings
	function getBlocks($string){

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

	// Get one level deep expressions from either block or expression and return as array segments
	function getExpressions($block_or_expression){
		// preg_match_all('/{((?:[^{}]++|(?R))*+)}/', $str, $matches);
		// $result = $matches[1];
		// http://stackoverflow.com/questions/11024495/php-regex-and-multi-level-curly-brackets
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
}