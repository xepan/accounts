<?php
namespace xepan\accounts;

class Model_BalanceSheet extends \xepan\base\Model_Table{
	
	public $table="account_balance_sheet";
	public $acl=false;
	
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');
		
		$this->addField('name')->mandatory(true);
		$this->addField('positive_side')->enum(array('LT','RT'))->mandatory(true);
		$this->addField('report_name')->enum(array('BalanceSheet','Trading','Profit & Loss'))->mandatory(true);
		$this->addField('show_sub')->enum(array('SchemeGroup','SchemeName','Accounts'))->mandatory(true);
		$this->addField('subtract_from')->enum(array('DR'))->mandatory(true);
		$this->addField('order');
		$this->addField('created_at')->type('date')->defaultValue($this->app->today);

		$this->hasMany('xepan\accounts\Group','balance_sheet_id');

		$this->is([
				'name|to_trim|unique_in_epan'
			]);

	}

	function loadDefaults(){
		$data= $this->defaultHeads;
		foreach ($data as $dg) {
			if($this->newInstance()->tryLoadBy('name',$dg['name'])->loaded()) continue;
			$this->newInstance()->set($dg)->save();
		}
	}

	function load($id_name){
		if(is_numeric($id_name)) return parent::load($id_name);
		
		$this->unload();

		$this->tryLoadBy('name',$id_name);
		if($this->loaded()) return $this;

		foreach ($this->defaultHeads as $acc) {
			if($acc['name']==$id_name){
				$this->set($acc)->save();
				return $this;
			}
		}

		throw $this->exception('Could Not Load Balancesheet Head');
	}

	function check($name){
		return $this['name']===$name;
	}

	public $defaultHeads=[

		// Liabilities
		['name'=>'Capital Account','positive_side'=>'LT','report_name'=>'BalanceSheet','subtract_from'=>'CR','order'=>1],
		['name'=>'Loans & Liabilities','positive_side'=>'LT','report_name'=>'BalanceSheet','subtract_from'=>'CR','order'=>2],
		['name'=>'Current Liabilities','positive_side'=>'LT','report_name'=>'BalanceSheet','subtract_from'=>'CR','order'=>3],
		['name'=>'Sundry Creditor','positive_side'=>'LT','report_name'=>'BalanceSheet','subtract_from'=>'CR','order'=>4],
		['name'=>'Suspense Account','positive_side'=>'LT','report_name'=>'BalanceSheet','subtract_from'=>'CR','order'=>5],
		
		// Assets
		['name'=>'Fixed Assets','positive_side'=>'RT','report_name'=>'BalanceSheet','subtract_from'=>'DR','order'=>1],
		['name'=>'Current Assets','positive_side'=>'RT','report_name'=>'BalanceSheet','subtract_from'=>'DR','order'=>2],
		['name'=>'Deposit Assets','positive_side'=>'RT','report_name'=>'BalanceSheet','subtract_from'=>'DR','order'=>3],
		['name'=>'Sundry Debtor','positive_side'=>'RT','report_name'=>'BalanceSheet','subtract_from'=>'DR','order'=>4],
		['name'=>'Stock In Hand','positive_side'=>'RT','report_name'=>'BalanceSheet','subtract_from'=>'DR','order'=>5],
		
		// Trading LT
		['name'=>'Opening Stock','positive_side'=>'LT','report_name'=>'Trading','subtract_from'=>'CR','order'=>1],
		['name'=>'Sales','positive_side'=>'RT','report_name'=>'Trading','subtract_from'=>'CR','order'=>2],
		['name'=>'InDirect Expenses For Sale','positive_side'=>'LT','report_name'=>'Trading','subtract_from'=>'CR','order'=>3],
		['name'=>'Purchase Returns','positive_side'=>'RT','report_name'=>'Trading','subtract_from'=>'CR','order'=>4],
		
		// Trading RT
		['name'=>'Purchase','positive_side'=>'LT','report_name'=>'Trading','subtract_from'=>'DR','order'=>2],
		['name'=>'Closing Stock','positive_side'=>'RT','report_name'=>'Trading','subtract_from'=>'DR','order'=>3],
		['name'=>'Sales Returns','positive_side'=>'LT','report_name'=>'Trading','subtract_from'=>'DR','order'=>4],

		// Expenses 
		['name'=>'Expenses','positive_side'=>'LT','report_name'=>'Profit & Loss','subtract_from'=>'CR','order'=>1],
		// Income 
		['name'=>'Income','positive_side'=>'RT','report_name'=>'Profit & Loss','subtract_from'=>'DR','order'=>1]

	];

}
