<?php
namespace xepan\accounts;

class Model_BalanceSheet extends \xepan\base\Model_Table{
	public $table="account_balance_sheet";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');
		
		$this->addField('name')->mandatory(true);
		$this->addField('positive_side')->enum(array('LT'))->mandatory(true);
		$this->addField('is_pandl')->type('boolean')->mandatory(true);
		$this->addField('show_sub')->enum(array('SchemeGroup','SchemeName','Accounts'))->mandatory(true);
		$this->addField('subtract_from')->enum(array('DR'))->mandatory(true);
		$this->addField('order');
		$this->addField('created_at')->type('date')->defaultValue($this->app->today);

		$this->hasMany('xepan\accounts\Group','balance_sheet_id');

	}


	function loadDepositLiabilities(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Deposits - Liabilities')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',6)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isDepositLiabilities(){
		return $this['name'] =='Deposits - Liabilities';
	}

	function loadCurrentAssets(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Current Assets')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isCurrentAssets(){
		return $this['name'] == "Current Assets";
	}

	function loadCapitalAccount(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Capital Account')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}
	
	function isCapitalAccount(){
		return $this['name'] == 'Capital Account';
	}

	function loadExpenses(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Expenses')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',true)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isExpenses(){
		return $this['name'] == 'Expenses';
	}

	function loadIncome(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Income')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',true)
		->addCondition('subtract_from','DR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isIncome(){
		return $this['name'] == 'Income';
	}
	function loadSuspenseLedger(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Suspence Account')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isSuspenseLedger(){
		return $this['name'] == 'Suspence Account';
	}

	function loadSales(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Sales')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',true)
		->addCondition('subtract_from','DR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isSales(){
		return $this['name'] == 'Sales';
	}

	function loadPurchase(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Purchase')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',true)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isPurchase(){
		return $this['name'] == 'Purchase';
	}

	function loadDutiesAndTaxes(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Duties & Taxes')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isDutiesAndTaxes(){
		return $this['name'] == 'Duties & Taxes';
	}

	

	function loadFixedAssets(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Fixed Assets')
		->addCondition('positive_side','LT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','CR')
		->addCondition('order',5)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isFixedAssets(){
		return $this['name'] == 'Fixed Assets';
	}

	function loadBranchDivisions(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Branch/Divisions')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','DR')
		->addCondition('order',6)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isBranchDivisions(){
		return $this['name'] == 'Branch/Divisions';
	}

	function loadCurrentLiabilities(){
		if($this->loaded())
			$this->unload();
		$this
		->addCondition('name','Current Liabilities')
		->addCondition('positive_side','RT')
		->addCondition('is_pandl',false)
		->addCondition('subtract_from','DR')
		->addCondition('order',3)
		->tryLoadAny();
		if(!$this->loaded()) $this->save();
		return $this;
	}

	function isCurrentLiabilities(){
		return $this['name'] == 'Current Liabilities';
	}

	// function loadDefaults(){
	// 	$data= file_get_contents(getcwd().'/../vendor/xepan/accounts/default-heads.xepan');
	// 	$arr = json_decode($data,true);
	// 	foreach ($arr as $dg) {
	// 		unset($dg['id']);
	// 		unset($dg['epan_id']);
	// 		$this->newInstance()->set($dg)->save();
	// 	}
	// }

}
