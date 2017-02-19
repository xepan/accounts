<?php
namespace xepan\accounts;

class Model_ReportFunction extends \xepan\base\Model_Table{
	public $table="report_function";
	public $acl=false;
	function init(){
		parent::init();

		$this->addField('name')->hint('without space between words');
		$this->addField('type')->enum([
								'HeadBalance',
								'GroupBalance',
								'GroupOnlyBalance',
								'GroupDR',
								'GroupCR',
								'GroupOnlyDR',
								'GroupOnlyCR',
								'LedgerBalance',
								'HeadDR',
								'HeadCR',
								'HeadTransactionSUMDR',
								'HeadTransactionSUMCR',
								'GroupTransactionSUMDR',
								'GroupTransactionSUMCR',
								'GroupOnlyTransactionSUMDR',
								'GroupOnlyTransactionSUMCR',
								'PANDL',
								'Trading'
							]);

		$this->hasOne('xepan\accounts\Group','group_id');
		$this->hasOne('xepan\accounts\BalanceSheet','head_id');
		$this->hasOne('xepan\accounts\Ledger','ledger_id');

		$this->addField('start_date'); //'FYStart',"PreviousFYStart","CurrentMonthStart","PreviousMonthStart",'CustomDate'
		$this->addField('end_date'); //'FYEnd','PreviousFYEnd','CurrentMonthEnd','PreviousMonthEnd','CustomDate'
				
		// $this->addHook('beforeSave',$this);
	}

	// function beforeSave(){

	// 	if(preg_match('/\s/',$this['name']))
	// 		throw $this->exception('whitespace are not allowed', 'ValidityCheck')->setField('name');

	// }

	// return array of start_date and end_date
	function getDate(){
		if(!$this->loaded()) throw new \Exception("layout model must loaded", 1);
		$return = [];

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
        $fy_start = $config_model['FY_Start'];
        $fy_end = $config_model['FY_End'];
        $current_month_start = $config_model['Current_Month_Start'];
        $current_month_end = $config_model['Current_Month_End'];
        
		if( (date('Y-m-d H:i:s', strtotime($this['start_date'])) == $this['start_date']) )
			$return['start_date'] = $this['start_date'];
		else{
			// 'FYStart',"PreviousFYStart","CurrentMonthStart","PreviousMonthStart"
			switch ($this['start_date']) {
				case 'FYStart':
					$return['start_date'] = $fy_start;
					break;
				case 'CurrentMonthStart':
					$return['start_date'] = $current_month_start;
					break;
				case 'PreviousFYStart':
  					$return['start_date'] = date("Y-m-d H:i:s", strtotime("-1 year", strtotime($fy_start)));
					break;
				case 'PreviousMonthStart':
  					$return['start_date'] = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($current_month_start)));
					break;
			}
		}

		if( (date('Y-m-d H:i:s', strtotime($this['end_date'])) == $this['end_date']) )
			$return['end_date'] = $this['end_date'];
		else{
			//'FYEnd','PreviousFYEnd','CurrentMonthEnd','PreviousMonthEnd'
			switch ($this['end_date']) {
				case 'FYEnd':
					$return['end_date'] = $fy_end;
					break;
				case 'CurrentMonthEnd':
					$return['end_date'] = $current_month_end;
					break;
				case 'PreviousFYEnd':
  					$return['end_date'] = date("Y-m-d H:i:s", strtotime("-1 year", strtotime($fy_end)));
					break;
				case 'PreviousMonthEnd':
  					$return['end_date'] = date("Y-m-d H:i:s", strtotime("-1 month", strtotime($current_month_end)));
					break;
			}
		}		

		return $return;
	}


	function getResult(){
		if(!$this->loaded()) throw new \Exception("layout model must loaded", 1);

		$start_end_date = $this->getDate();
		$start_date = $start_end_date['start_date'];
		$end_date = $start_end_date['end_date'];
		
		switch ($this['type']) {
		
			case 'GroupBalance':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['group_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;

			case 'GroupOnlyBalance':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date,'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;
			
			case 'GroupDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'GroupCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceCr'];
			break;

			case 'GroupTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['group_id']);
				return $model['TransactionsDr'];
			break;

			case 'GroupTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['group_id']);
				return $model['TransactionsCr'];
			break;

			case 'GroupOnlyDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date,'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'GroupOnlyCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date,'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceCr'];
			break;


			case 'GroupOnlyTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date,'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['TransactionsDr'];
			break;

			case 'GroupOnlyTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$start_date,'to_date'=>$end_date,'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['TransactionsCr'];
			break;

			case 'LedgerBalance':
				$model = $this->add('xepan\accounts\Model_BSLedger',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->addExpression('subtract_from')->set(function($m,$q){
					$bs_m = $m->add('xepan\accounts\Model_BalanceSheet');
					return $bs_m->addCondition('id',$q->getField('balance_sheet_id'))
							->fieldQuery($bs_m->getElement('subtract_from'));
				});

				$model->load($this['ledger_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;

			case 'HeadBalance':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['head_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;

			case 'HeadDR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['head_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'HeadCR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['head_id']);
				return $model['ClosingBalanceCr'];
			break;

			case 'HeadTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['head_id']);
				return $model['TransactionsDr'];
			break;

			case 'HeadTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$start_date,'to_date'=>$end_date]);
				$model->load($this['head_id']);
				return $model['TransactionsCr'];
			break;

			case 'PANDL':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet');
				$result = $model->getPandL($start_date,$end_date);
				return $result['net_profit'] - $result['net_loss'];
			break;

			case 'Trading':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet');
				$result = $model->getTradingBalance($start_date,$end_date);
				return $result['gross_profit'] - $result['gross_loss'];
			break;
		}

	}	
}


// HeadBalance(HeadName,StartDate,EndDate)
// GroupBalance
// GroupOnlyBalance
// LedgerBalance
// HeadDR
// HeadCR
// GroupDR
// GroupCR
// GroupOnlyDR
// GroupOnlyCR
// HeadTransactionSUMDR
// HeadTransactionSUMCR
// GroupTransactionSUMDR
// GroupTransactionSUMCR
// GroupOnlyTransactionSUMDR
// GroupOnlyTransactionSUMCR
// PANDL