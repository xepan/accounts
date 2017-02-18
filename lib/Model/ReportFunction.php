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

		$this->addField('start_date')->type('date');
		$this->addField('end_date')->type('date');
		
		// $this->addHook('beforeSave',$this);
	}

	// function beforeSave(){

	// 	if(preg_match('/\s/',$this['name']))
	// 		throw $this->exception('whitespace are not allowed', 'ValidityCheck')->setField('name');

	// }

	function getResult(){
		if(!$this->loaded()) throw new \Exception("layout model must loaded", 1);

		switch ($this['type']) {
		
			case 'GroupBalance':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['group_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;

			case 'GroupOnlyBalance':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date'],'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;
			
			case 'GroupDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'GroupCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceCr'];
			break;

			case 'GroupTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['group_id']);
				return $model['TransactionsDr'];
			break;

			case 'GroupTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['group_id']);
				return $model['TransactionsCr'];
			break;

			case 'GroupOnlyDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date'],'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'GroupOnlyCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date'],'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['ClosingBalanceCr'];
			break;


			case 'GroupOnlyTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date'],'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['TransactionsDr'];
			break;

			case 'GroupOnlyTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSGroup',['from_date'=>$this['start_date'],'to_date'=>$this['end_date'],'no_sub_groups'=>true]);
				$model->load($this['group_id']);
				return $model['TransactionsCr'];
			break;

			case 'LedgerBalance':
				$model = $this->add('xepan\accounts\Model_BSLedger',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
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
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['head_id']);
				if($model['subtract_from'] == "DR")
					return $model['ClosingBalanceDr'] - $model['ClosingBalanceCr'];
				else
					return $model['ClosingBalanceCr'] - $model['ClosingBalanceDr'];
			break;

			case 'HeadDR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['head_id']);
				return $model['ClosingBalanceDr'];
			break;

			case 'HeadCR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['head_id']);
				return $model['ClosingBalanceCr'];
			break;

			case 'HeadTransactionSUMDR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['head_id']);
				return $model['TransactionsDr'];
			break;

			case 'HeadTransactionSUMCR':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet',['from_date'=>$this['start_date'],'to_date'=>$this['end_date']]);
				$model->load($this['head_id']);
				return $model['TransactionsCr'];
			break;

			case 'PANDL':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet');
				$result = $model->getPandL($this['start_date'],$this['end_date']);
				return $result['net_profit'] - $result['net_loss'];
			break;

			case 'Trading':
				$model = $this->add('xepan\accounts\Model_BSBalanceSheet');
				$result = $model->getTradingBalance($this['start_date'],$this['end_date']);
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