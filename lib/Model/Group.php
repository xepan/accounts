<?php
namespace xepan\accounts;

class Model_Group extends \xepan\base\Model_Table{
	public $table="account_group";
	public $acl=false;
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');

		$this->hasOne('xepan\accounts\BalanceSheet','balance_sheet_id')->sortable(true);

		$this->hasOne('xepan\accounts\ParentGroup','parent_group_id')->sortable(true);
		$this->hasOne('xepan\accounts\RootGroup','root_group_id')->sortable(true);

		$this->addField('name')->caption('Group Name')->mandatory(true)->sortable(true);
		$this->addField('created_at')->type('date')->defaultValue(date('Y-m-d'))->sortable(true);
		$this->addField('path')->type('text')->system(true);


		$this->hasMany('xepan\accounts\Ledger','group_id');

		$this->is([
			'name!|to_trim|unique'
			]
			);
		
		$this->hasMany('xepan\accounts\Group','parent_group_id',null,'ParentGroup');
		$this->hasMany('xepan\accounts\Group','root_group_id',null,'RootGroup');

		$this->addHook('beforeDelete',[$this,'checkLedgerExistance']);
		$this->addHook('afterSave',[$this,'manageRootGroupIdAndPath']);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function checkLedgerExistance(){
		$account = $this->ref('xepan\accounts\Ledger')->count()->getOne();
		if($account)
			throw $this->exception('Cannot Delete, First Delete Ledgers');
	}

	function manageRootGroupIdAndPath(){
		if(!$this['parent_group_id']){
			$this['root_group_id']= $this->id;
			$this['path']= '.'.$this->id.'.';
		}
		else{
			$parent= $this->ref('parent_group_id');
			$this['root_group_id']= $parent['root_group_id'];
			$this['path']= $parent['path'].$this->id.'.';
		}
		$this->save();
	}

	
	function createNewGroup($name,$balance_sheet_id,$other_values=array()){
		
		$this['name'] = $name;
		$this['balance_sheet_id'] = $balance_sheet_id;
		foreach ($other_values as $field => $value) {
			$this[$field] = $value;
		}

		$this->save();
	}

	function loadDefaults(){

		$data= $this->defaultGroups;

		foreach ($data as $group) {
			// balancesheet id set and parent id set if in array

			if($this->newInstance()->tryLoadBy('name',$group['name'])->loaded()) continue;

			if($group['parent_group']){
				$group['parent_group_id'] = $this->newInstance()->load($group['parent_group'])->get('id');
			}
			
			if($group['root_group']){
				$group['root_group_id'] = $this->newInstance()->load($group['root_group'])->get('id');
			}

			$group['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->load($group['balance_sheet'])->get('id');

			$this->newInstance()->set($group)->save();
		}
	}

	function load($id_name){
		if(is_numeric($id_name)) return parent::load($id_name);
		
		$this->unload();

		$this->tryLoadBy('name',$id_name);
		if($this->loaded()) return $this;

		foreach ($this->defaultGroups as $group) {
			if($group['name']==$id_name){
				// balancesheet id set and parent id set if in array
				if($group['parent_group']){
					$group['parent_group_id'] = $this->newInstance()->load($group['parent_group'])->get('id');
				}
				
				if($group['root_group']){
					$group['root_group_id'] = $this->newInstance()->load($group['root_group'])->get('id');
				}

				$group['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->load($group['balance_sheet'])->get('id');

				$this->set($group)->save();
				return $this;
			}
		}

		throw $this->exception('Could Not Load Group');
	}

	function check($name){
		return $this['name']===$name;
	}

	public $defaultGroups=[

		/**

		Liabilities Section

		*/
		// Liabilities(Share Holder Fund)
		['name'=>'Reserved & Surplus','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Share Holder Fund'],
		['name'=>'Share Capital','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Share Holder Fund'],

		// Liabilities(Capital Account)
		// ['name'=>'Capital Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Capital Account'],
		// ['name'=>'Partner','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		// ['name'=>'Individual','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		// ['name'=>'Director','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		
		// Liabilities(Non Current Liabilities)
		['name'=>'Long Term Borrowing','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Deffered Tax Liabilities (Net)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Other Long Term Liabilities','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Long Term Provisions','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Current Liabilities)
		['name'=>'Short Term Borrowing','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Trade Payables','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Other Current Liabilities','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Short Term Provisions','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],

		// Liabilities(Current Liabilities -> Short Term Borrowing)
		['name'=>'Bank OverDraft','root_group'=>null,'parent_group'=>'Short Term Borrowing','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Long Term Borrowing)
		['name'=>'Unsecured Loans','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Current Liabilities'],
		['name'=>'Secured Loans','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Other Long Term Liabilities)
		['name'=>'Staff Security','root_group'=>null,'parent_group'=>'Other Long Term Liabilities','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Other Current Liabilities)
		['name'=>'Tax Payable','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		// ['name'=>'Provident Fund','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],

		// Liabilities(Current Liabilities -> Other Current Liabilities)
		['name'=>'Sundry Creditor','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Bills Payable','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Suspense Account)
		// ['name'=>'Suspense Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Suspense Account'],
		
		/**

		Assests Section

		*/
		// Assets(Non Current Assets)
		['name'=>'Fixed Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Non Current Investments','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Long Terms Loans And Advances','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Other Non Current Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets)
		['name'=>'Tangible Assets','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'InTangible Assets','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Capital Work In Progress','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets -> Tangible Assets)
		['name'=>'Plants & Machinery','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Computers & Printers','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Furniture & Fixture','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Land & Building','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets -> Tangible Assets -> Land & Building)
		['name'=>'Land (Appreciable)','root_group'=>'Fixed Assets','parent_group'=>'Land & Building','balance_sheet'=>'Non Current Assets'],
		['name'=>'Building (Depreciable)','root_group'=>'Fixed Assets','parent_group'=>'Land & Building','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Other Non Current Assets)
		['name'=>'Mortgage Deposit','root_group'=>null,'parent_group'=>'Other Non Current Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Security Deposit','root_group'=>null,'parent_group'=>'Other Non Current Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Current Assets)
		['name'=>'Inventories','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Trade Receivables','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Cash And Cash Equivalents','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Short Term Loan And Advances','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Other Current Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],

		// Assets(Current Assets -> Cash And Cash Equivalents)
		['name'=>'Cash In Hand','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Bank Account','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Sundry Debtor','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Bills Receivable','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		
		// Assets(Current Assets -> Other Current Assets)
		['name'=>'GoodWill','root_group'=>null,'parent_group'=>'Other Current Assets','balance_sheet'=>'Current Assets'],
		['name'=>'Bank FD','root_group'=>null,'parent_group'=>'Other Current Assets','balance_sheet'=>'Current Assets'],
		['name'=>'Tax Receivable','root_group'=>null,'parent_group'=>'Other Current Assets','balance_sheet'=>'Current Assets'],
		// ['name'=>'Provision (Assets)','root_group'=>null,'parent_group'=>'Other Current Assets','balance_sheet'=>'Current Assets'],
		
		// Assets(Current Assets -> Short Term Loan And Advances)
		['name'=>'Loan Given','root_group'=>null,'parent_group'=>'Short Term Loan And Advances','balance_sheet'=>'Current Assets'],
		
		/**

		Trading LT Section

		*/
		// Trading LT
		['name'=>'Opening Stock','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Opening Stock'],
		['name'=>'Purchase','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Purchase'],
		['name'=>'Direct Expenses','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Direct Expenses'],
		['name'=>'Sales Returns','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sales Returns'],
		
		/**

		Trading RT Section

		*/		
		// Trading RT
		['name'=>'Sales','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sales'],
		['name'=>'Purchase Returns','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Purchase Returns'],
		['name'=>'Closing Stock','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Closing Stock'],
		
		/**

		P&L Expense/Loss Section

		*/	
		// Loss By Expenses
		// InDirect Expenses Group
		['name'=>'Compensation To Employee (Indirect)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		['name'=>'Rebate & Discount Allowed','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		// Compensation To Employee Groups
		['name'=>'Renumeration To Directors (Indirect)','root_group'=>null,'parent_group'=>'Compensation To Employee (Indirect)','balance_sheet'=>'Indirect Expenses'],
		['name'=>'Salary (Indirect)','root_group'=>null,'parent_group'=>'Compensation To Employee (Indirect)','balance_sheet'=>'Indirect Expenses'],
		
		['name'=>'Commission Given','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		['name'=>'Power & Fuel','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		['name'=>'Interest Paid','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		['name'=>'Other Expenses','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses'],
		
		// Direct Expenses -> Other Expenses Group
		['name'=>'Miscellaneous Expenses','root_group'=>null,'parent_group'=>'Other Expenses','balance_sheet'=>'Indirect Expenses'],
		['name'=>'Shipping Expenses','root_group'=>null,'parent_group'=>'Other Expenses','balance_sheet'=>'Indirect Expenses'],
		['name'=>'Exchange Expenses','root_group'=>null,'parent_group'=>'Other Expenses','balance_sheet'=>'Indirect Expenses'],
		['name'=>'Bank Charges Expenses','root_group'=>null,'parent_group'=>'Other Expenses','balance_sheet'=>'Indirect Expenses'],
		
		// ['name'=>'Direct Expenses','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Expenses'],
		// // Direct Expenses Group
		// ['name'=>'Compensation To Employee (Direct)','root_group'=>null,'parent_group'=>'Direct Expenses','balance_sheet'=>'Expenses'],
		// ['name'=>'Salary (Direct)','root_group'=>null,'parent_group'=>'Compensation To Employee (Direct)','balance_sheet'=>'Expenses'],
		
		/**

		P&L Income/Profit Section

		*/	

		// Profit By Income
		// InDirect Income Group
		['name'=>'Rebate & Discount Received','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Income'],
		['name'=>'Interest Received','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Income'],
		['name'=>'Commission Received','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Income'],
		['name'=>'Other Income','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Income'],
		// InDirect Income -> Other Income Group
		['name'=>'Round Income','root_group'=>null,'parent_group'=>'Other Income','balance_sheet'=>'Indirect Income'],
		['name'=>'Exchange Income','root_group'=>null,'parent_group'=>'Other Income','balance_sheet'=>'Indirect Income']
		
	];

}
