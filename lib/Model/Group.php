<?php
namespace xepan\accounts;

class Model_Group extends \xepan\base\Model_Table{
	public $table="account_group";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');

		$this->hasOne('xepan\accounts\BalanceSheet','balance_sheet_id');

		$this->hasOne('xepan\accounts\ParentGroup','parent_group_id');
		$this->hasOne('xepan\accounts\RootGroup','root_group_id');

		$this->addField('name')->caption('Group Name')->mandatory(true);
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d'));


		$this->hasMany('xepan\accounts\Ledger','group_id');

		$this->is([
						'name!|to_trim|unique'
					]
				);
		
		$this->hasMany('xepan\accounts\Group','parent_group_id',null,'ParentGroup');
		$this->hasMany('xepan\accounts\Group','root_group_id',null,'RootGroup');

		$this->addHook('beforeDelete',$this);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function beforeDelete(){
		$account = $this->ref('xepan\accounts\Ledger')->count()->getOne();
		if($account)
			throw $this->exception('Cannot Delete, First Delete Ledgers');
	}

		
	function createNewGroup($name,$balance_sheet_id,$other_values=array()){
		
		$this['name'] = $name;
		$this['balance_sheet_id'] = $balance_sheet_id;
		foreach ($other_values as $field => $value) {
			$this[$field] = $value;
		}

		$this->save();
	}

	function loadCashLedger(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Cash Account')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isCashLedger(){
		return $this['name'] == "Cash Account";
	}

	function loadBankLedgers(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Bank Accounts')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isBankLedgers(){
		return $this['name'] == "Bank Accounts";
	}

	function loadBankOD(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Bank OD')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;
	}

	function isBankOD(){
		return $this['name'] == "Bank OD";
	}

	function loadFDAssets(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','F.D. Assets')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isFDAssets(){
		return $this['name'] == "F.D. Assets";
	}

	function loadShareCapital(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Share Capital')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isShareCapital(){
		return $this['name'] == "Share Capital";
	}

	function loadDirectExpenses(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadExpenses()->fieldquery('id'));
		$this->addCondition('name','Direct Expenses')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();
		
		return $this;	
	}

	function isDirectExpenses(){
		return $this['name'] == "Direct Expenses";
	}

	function loadDirectIncome(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadIncome()->fieldquery('id'));
		$this->addCondition('name','Direct Income')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isDirectIncome(){
		return $this['name'] == "Direct Income";
	}

	function loadDutiesAndTaxes(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadDutiesAndTaxes()->fieldquery('id'));
		$this->addCondition('name','Duties & Taxes')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isDutiesAndTaxes(){
		return $this['name'] == "Duties & Taxes";
	}

	function loadFixedAssets(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadFixedAssets()->fieldquery('id'));
		$this->addCondition('name','Fixed Assets')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isFixedAssets(){
		return $this['name'] == "Fixed Assets";
	}

	function loadIndirectExpenses(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadExpenses()->fieldquery('id'));
		$this->addCondition('name','Indirect Expenses')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isIndirectExpenses(){
		return $this['name'] == "Indirect Expenses";
	}

	function loadIndirectIncome(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadIncome()->fieldquery('id'));
		$this->addCondition('name','Indirect Income')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isIndirectIncome(){
		return $this['name'] == "Indirect Income";
	}

	function loadLoanAdvanceAssets(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Loan Advances (Assets)')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isLoanAdvanceAssets(){
		return $this['name'] == "Loan Advances (Assets)";
	}

	function loadLoanLiabilities(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentLiabilities()->fieldquery('id'));
		$this->addCondition('name','Loan (Liabilities)')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isLoanLiabilities(){
		return $this['name'] == "Loan (Liabilities)";
	}

	function loadMiscExpensesAssets(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Misc Expenses (Assets)')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isMiscExpensesAssets(){
		return $this['name'] == "Misc Expenses (Assets)";
	}

	// function loadProvision(){
	// 	if($this->loaded())
	// 		$this->unload();
	// 	$this->addCondition('balance_sheet_id',$this->add('xAccount/Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
	// 	$this->addCondition('name','Provision')
	// 		->tryLoadAny();
	// 	return $this;	
	// }

	// function isProvision(){
	// 	return $this['name'] == "Provision";
	// }

	function loadReserveSurpuls(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Reserve Surpuls')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isReserveSurpuls(){
		return $this['name'] == "Reserve Surpuls";
	}

	function loadRetainedEarnings(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Retained Earnings')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isRetainedEarnings(){
		return $this['name'] == "Retained Earnings";
	}

	function loadSecuredLoan(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Secured (Loan)')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isSecuredLoan(){
		return $this['name'] == "Secured (Loan)";
	}

	function loadSundryCreditor(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentLiabilities()->fieldquery('id'));
		$this->addCondition('name','Sundry Creditor')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isSundryCreditor(){
		return $this['name'] == "Sundry Creditor";
	}

	function loadSundryDebtor(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Sundry Debtor')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();

		return $this;	
	}

	function isSundryDebtor(){
		return $this['name'] == "Sundry Debtor";
	}

	function loadSuspenseLedger(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadCurrentAssets()->fieldquery('id'));
		$this->addCondition('name','Suspense Account')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();
		
		return $this;	
	}


	function isSuspenseLedger(){
		return $this['name'] == "Suspense Account";
	}

	function loadSalesGroup(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadSales()->fieldquery('id'));
		$this->addCondition('name','Sales')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();
		
		return $this;	
	}

	function loadPurchaseGroup(){
		if($this->loaded())
			$this->unload();
		$this->addCondition('balance_sheet_id',$this->add('xepan\accounts\Model_BalanceSheet')->loadPurchase()->fieldquery('id'));
		$this->addCondition('name','Purchase')
			->tryLoadAny();
		
		if(!$this->loaded()) $this->save();
		
		return $this;	
	}

}
