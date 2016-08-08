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
		$this->addField('is_pandl')->type('boolean')->mandatory(true);
		$this->addField('show_sub')->enum(array('SchemeGroup','SchemeName','Accounts'))->mandatory(true);
		$this->addField('subtract_from')->enum(array('DR'))->mandatory(true);
		$this->addField('order');
		$this->addField('created_at')->type('date')->defaultValue($this->app->today);

		$this->hasMany('xepan\accounts\Group','balance_sheet_id');

	}

	function loadDefaults(){
		$data= $this->defaultHeads;
		foreach ($arr as $dg) {
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
		['name'=>'Capital Account','positive_side'=>'LT','is_pandl'=>0,'subtract_from'=>'DR','order'=>1],
	];

}
