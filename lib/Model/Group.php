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

	function getBalance($from_date=null,$to_date=null){
		// if(!$this->loaded()) throw new \Exception("Group Model Must Be Loaded", 1);
		
		return rand(999,99999);
	}

	public $defaultGroups=[

		/**

		Liabilities Section

		*/
		// Liabilities(Share Holder Fund)
		['name'=>'Share Capital','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Share Holder Fund'],
		['name'=>'Reserves & Surplus','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Share Holder Fund'],
		['name'=>'Money Received Against Share Warrants','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Share Holder Fund'],

		// Liabilities(Share Holder Fund -> Reserves & Surplus)
		['name'=>'Capital Reserves','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Capital Redemption Reserve','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Securities Premium Reserve','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Debenture Redemption Reserve','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Revaluation Reserve','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Share Options Outstanding Account','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Other Reserves','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		['name'=>'Surplus','root_group'=>null,'parent_group'=>'Reserves & Surplus','balance_sheet'=>'Share Holder Fund'],
		
		// Liabilities(Non Current Liabilities)
		['name'=>'Long Term Borrowing','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Deffered Tax Liabilities (Net)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Other Long Term Liabilities','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Long Term Provisions','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Long Term Borrowing)
		['name'=>'Bonds / Debenture','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Current Liabilities'],
		['name'=>'Term Loans','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Deffered Payment Liabilities','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Deposits (Long Term Liabilities)','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Loans And Advances From Related Parties (Long Term)','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Other Loans And Advances (Long Term Liabilities)','root_group'=>null,'parent_group'=>'Long Term Borrowing','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Long Term Borrowing -> Term Loans)
		['name'=>'Term Loans From Bank','root_group'=>'Long Term Borrowing','parent_group'=>'Term Loans','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Term Loans From Other Parties','root_group'=>'Long Term Borrowing','parent_group'=>'Term Loans','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Other Long Term Liabilities)
		['name'=>'Others (Other Long Term Liabilities)','root_group'=>null,'parent_group'=>'Other Long Term Liabilities','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Non Current Liabilities -> Long Term Provisions)
		['name'=>'Provision For Employee Benefits','root_group'=>null,'parent_group'=>'Long Term Provisions','balance_sheet'=>'Non Current Liabilities'],
		['name'=>'Others (Long Term Provisions)','root_group'=>null,'parent_group'=>'Long Term Provisions','balance_sheet'=>'Non Current Liabilities'],
		
		// Liabilities(Current Liabilities)
		['name'=>'Short Term Borrowing','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Trade Payables','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Other Current Liabilities','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],
		['name'=>'Short Term Provisions','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Liabilities'],

		// Liabilities(Current Liabilities -> Short Term Borrowing)
		['name'=>'Loans Repayable On Demand','root_group'=>null,'parent_group'=>'Short Term Borrowing','balance_sheet'=>'Current Liabilities'],
		['name'=>'Loans And Advances From Related Parties (Short Term)','root_group'=>null,'parent_group'=>'Short Term Borrowing','balance_sheet'=>'Current Liabilities'],
		['name'=>'Deposits (Short Term Liabilities)','root_group'=>null,'parent_group'=>'Short Term Borrowing','balance_sheet'=>'Current Liabilities'],
		['name'=>'Other Loans And Advances (Short Term Liabilities)','root_group'=>null,'parent_group'=>'Short Term Borrowing','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Short Term Borrowing -> Loans Repayable On Demand)
		['name'=>'Loans From Banks','root_group'=>'Short Term Borrowing','parent_group'=>'Loans Repayable On Demand','balance_sheet'=>'Current Liabilities'],
		['name'=>'Loans From Other Parties','root_group'=>'Short Term Borrowing','parent_group'=>'Loans Repayable On Demand','balance_sheet'=>'Current Liabilities'],
		['name'=>'Bank OD','root_group'=>'Short Term Borrowing','parent_group'=>'Loans Repayable On Demand','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Other Current Liabilities)
		['name'=>'Current Maturities Of Long Term Debt','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Current Maturities Of Financial Lease Obligations','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Interest Accrued But Not Due On Borrowings','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Interest Accrued And Due On Borrowings','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Income Received In Advance','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Unpaid Divindends','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Interest Accrued On Not Alloted Security Money','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Interest Accrued On Unpaid Matured Deposits','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Interest Accrued On Unpaid Matured Debentures','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		['name'=>'Other Payables','root_group'=>null,'parent_group'=>'Other Current Liabilities','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Other Current Liabilities -> Other Payables)
		['name'=>'Tax Payable','root_group'=>'Other Current Liabilities','parent_group'=>'Other Payables','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Trade Payables)
		['name'=>'Sundry Creditor','root_group'=>null,'parent_group'=>'Trade Payables','balance_sheet'=>'Current Liabilities'],
		
		// Liabilities(Current Liabilities -> Short Term Provisions)
		['name'=>'Provision For Employee Benefits','root_group'=>null,'parent_group'=>'Short Term Provisions','balance_sheet'=>'Current Liabilities'],
		['name'=>'Others (Short Term Provisions)','root_group'=>null,'parent_group'=>'Short Term Provisions','balance_sheet'=>'Current Liabilities'],
		
		/**

		Assests Section

		*/
		// Assets(Non Current Assets)
		['name'=>'Fixed Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Non Current Investments','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Differed Tax Assets (Net)','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Long Term Loans And Advances','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		['name'=>'Other Non Current Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets)
		['name'=>'Tangible Assets','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Intangible Assets','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Capital Work In Progress','root_group'=>null,'parent_group'=>'Fixed Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets -> Tangible Assets)
		['name'=>'Land & Building','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Buildings','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Plant & Equipment','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Furniture & Fixtures','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Computers & Printers','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Vehicles','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Office Equipment','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Others (Tangible Assets)','root_group'=>'Fixed Assets','parent_group'=>'Tangible Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets -> Tangible Assets -> Land & Building)
		['name'=>'Land (Appreciable)','root_group'=>'Fixed Assets','parent_group'=>'Land & Building','balance_sheet'=>'Non Current Assets'],
		['name'=>'Building (Depreciable)','root_group'=>'Fixed Assets','parent_group'=>'Land & Building','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Fixed Assets -> Intangible Assets)
		['name'=>'GoodWill','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Brand / Trademarks','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Computer Software','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Mastheads And Publisihing Titles','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Mining Rights','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Copyrights And Patents','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Licences And Franchise','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Others (Intangible Assets)','root_group'=>'Fixed Assets','parent_group'=>'Intangible Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Long Terms Loans And Advances)
		['name'=>'Capital Advances','root_group'=>null,'parent_group'=>'Long Term Loans And Advances','balance_sheet'=>'Non Current Assets'],
		['name'=>'Security Deposits','root_group'=>null,'parent_group'=>'Long Term Loans And Advances','balance_sheet'=>'Non Current Assets'],
		['name'=>'Loans And Advances To Related Parties (Long Term)','root_group'=>null,'parent_group'=>'Long Term Loans And Advances','balance_sheet'=>'Non Current Assets'],
		['name'=>'Other Loans And Advances (Assets) ','root_group'=>null,'parent_group'=>'Long Term Loans And Advances','balance_sheet'=>'Non Current Assets'],
		['name'=>'Allowance For Bad And Doubtful Advances','root_group'=>null,'parent_group'=>'Long Term Loans And Advances','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Other Non Current Assets)
		['name'=>'Others (Other Non Current Assets)','root_group'=>null,'parent_group'=>'Other Non Current Assets','balance_sheet'=>'Non Current Assets'],
		['name'=>'Long Term Trade Receivables','root_group'=>null,'parent_group'=>'Other Non Current Assets','balance_sheet'=>'Non Current Assets'],
		
		// Assets(Non Current Assets -> Other Non Current Assets -> Long term Trade Receivables)
		['name'=>'Unsecured','root_group'=>'Other Non Current Assets','parent_group'=>'Long Term Trade Receivables','balance_sheet'=>'Non Current Assets'],
		['name'=>'Secured','root_group'=>'Other Non Current Assets','parent_group'=>'Long Term Trade Receivables','balance_sheet'=>'Non Current Assets'],
		['name'=>'Doubtful','root_group'=>'Other Non Current Assets','parent_group'=>'Long Term Trade Receivables','balance_sheet'=>'Non Current Assets'],

		// Assets(Current Assets)
		['name'=>'Current Investments','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Inventories','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Trade Receivables','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Cash And Cash Equivalents','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Short Term Loan And Advances','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],
		['name'=>'Other Current Assets','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Current Assets'],

		// Assets(Current Assets -> Inventories)
		// ['name'=>'Raw Materials','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Work In Progress','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Finished Goods','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Stock In Trade','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Stores And Spares','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Loose Tools','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],
		// ['name'=>'Others','root_group'=>null,'parent_group'=>'Inventories','balance_sheet'=>'Current Assets'],

		// Assets(Current Assets -> Cash And Cash Equivalents)
		['name'=>'Bank Account','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Cheque, Drafts On Hand','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Cash In Hand','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		['name'=>'Others (Cash Equivalents)','root_group'=>null,'parent_group'=>'Cash And Cash Equivalents','balance_sheet'=>'Current Assets'],
		
		// Assets(Current Assets -> Short Term Loan And Advances)
		['name'=>'Loans And Advances To Related Parties','root_group'=>null,'parent_group'=>'Short Term Loan And Advances','balance_sheet'=>'Current Assets'],
		['name'=>'Others (Short Term Loans & Advances)','root_group'=>null,'parent_group'=>'Short Term Loan And Advances','balance_sheet'=>'Current Assets'],

		// Assets(Current Assets -> Trade Receivables)
		['name'=>'Sundry Debtor','root_group'=>null,'parent_group'=>'Trade Receivables','balance_sheet'=>'Current Assets'],
		
		// Assets(Current Assets -> Other Current Assets)
		['name'=>'Tax Receivable','root_group'=>null,'parent_group'=>'Other Current Assets','balance_sheet'=>'Current Assets'],
		
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
		['name'=>'Direct Income','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Direct Income'],

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
