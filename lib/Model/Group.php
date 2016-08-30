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

		// Liabilities(Capital Account)
		['name'=>'Capital Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Capital Account'],
		['name'=>'Partner','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		['name'=>'Individual','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		['name'=>'Director','root_group'=>null,'parent_group'=>'Capital Account','balance_sheet'=>'Capital Account'],
		['name'=>'Reserved & Surplus','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Capital Account'],
		['name'=>'Share Capital','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Capital Account'],

		// Liabilities(Loans & Liabilities)
		['name'=>'Bank OverDraft','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Loans & Liabilities'],
		['name'=>'Loans Taken','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Loans & Liabilities'],
		['name'=>'Provision (Liabilities)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Loans & Liabilities'],
		['name'=>'Staff Security','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Loans & Liabilities'],

		// Liabilities(Current Liabilities)
		['name'=>'TDS Payable','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Service Tax','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Tax Payable','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Provident Fund','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Sundry Creditor)
		['name'=>'Sundry Creditor','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sundry Creditor'],
		['name'=>'Bills Payable','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sundry Creditor'],
		
		// Liabilities(Suspense Account)
		['name'=>'Suspense Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Suspense Account'],

		// Assets(Fixed Assets)
		['name'=>'Plants & Machinery','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Fixed Assets'],
		['name'=>'Computers & Printers','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Fixed Assets'],
		['name'=>'Furniture & Fixture','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Fixed Assets'],
		['name'=>'Land (Appreciable)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Fixed Assets'],
		['name'=>'Building (Depreciable)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Fixed Assets'],
		
		// Assets(Current Assets)
		['name'=>'Cash In Hand','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Bank Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Loan Given','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Bank FD','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'GoodWill','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'TDS Receivable','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Provision (Assets)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		
		// Assets(Deposit Assets)
		['name'=>'Mortgage Deposit','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Deposit Assets'],
		['name'=>'Security Deposit','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Deposit Assets'],
		
		// Assets(Sundry Debtor)
		['name'=>'Sundry Debtor','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sundry Debtor'],
		['name'=>'Bills Receivable','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sundry Debtor'],
		
		// Trading LT
		['name'=>'Opening Stock','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Opening Stock'],
		['name'=>'Purchase','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Purchase'],
		['name'=>'Indirect Expenses For Sale','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Indirect Expenses For Sale'],
		['name'=>'Sales Returns','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sales Returns'],
				
		// Trading RT
		['name'=>'Sales','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Sales'],
		['name'=>'Purchase Returns','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Purchase Returns'],
		['name'=>'Closing Stock','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Closing Stock'],
		
		// Loss By Expenses
		['name'=>'Indirect Expenses','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Expenses'],
		// InDirect Expenses Group
		['name'=>'Compensation To Employee (Indirect)','root_group'=>null,'parent_group'=>'Indirect Expenses','balance_sheet'=>'Income'],
		// Compensation To Employee Groups
		['name'=>'Renumeration To Directors (Indirect)','root_group'=>null,'parent_group'=>'Compensation To Employee (Indirect)','balance_sheet'=>'Income'],
		['name'=>'Salary (Indirect)','root_group'=>null,'parent_group'=>'Compensation To Employee (Indirect)','balance_sheet'=>'Income'],
		
		['name'=>'Commission Given','root_group'=>null,'parent_group'=>'Indirect Expenses','balance_sheet'=>'Income'],
		['name'=>'Power & Fuel','root_group'=>null,'parent_group'=>'Indirect Expenses','balance_sheet'=>'Income'],
		['name'=>'Interest Paid','root_group'=>null,'parent_group'=>'Indirect Expenses','balance_sheet'=>'Income'],
		['name'=>'Other Expenses','root_group'=>null,'parent_group'=>'Indirect Expenses','balance_sheet'=>'Expenses'],
		
		['name'=>'Direct Expenses','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Expenses'],
		// Direct Expenses Group
		['name'=>'Compensation To Employee (Direct)','root_group'=>null,'parent_group'=>'Direct Expenses','balance_sheet'=>'Income'],
		['name'=>'Salary (Direct)','root_group'=>null,'parent_group'=>'Compensation To Employee (Direct)','balance_sheet'=>'Income'],
		

		// Profit By Income
		['name'=>'Indirect Income','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Income'],
		// InDirect Income Group
		['name'=>'Rebate & Discount','root_group'=>null,'parent_group'=>'Indirect Income','balance_sheet'=>'Income'],
		['name'=>'Interest Received','root_group'=>null,'parent_group'=>'Indirect Income','balance_sheet'=>'Income'],
		['name'=>'Commission Received','root_group'=>null,'parent_group'=>'Indirect Income','balance_sheet'=>'Income'],
		
		['name'=>'Direct Income','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Income']
		// Direct Income Group
	];

}
