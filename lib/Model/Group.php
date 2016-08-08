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


		$this->hasMany('xepan\accounts\Ledger','group_id');

		$this->is([
			'name!|to_trim|unique'
			]
			);
		
		$this->hasMany('xepan\accounts\Group','parent_group_id',null,'ParentGroup');
		$this->hasMany('xepan\accounts\Group','root_group_id',null,'RootGroup');

		$this->addHook('beforeDelete',[$this,'checkLedgerExistance']);
		$this->addHook('afterSave',[$this,'manageRootGroupId']);
		//$this->add('dynamic_model/Controller_AutoCreator');
	}

	function checkLedgerExistance(){
		$account = $this->ref('xepan\accounts\Ledger')->count()->getOne();
		if($account)
			throw $this->exception('Cannot Delete, First Delete Ledgers');
	}

	function manageRootGroupId(){
		if(!$this['parent_group_id']) 
			$this['root_group_id']= $this->id;
		else
			$this['root_group_id']= $this->ref('parent_group_id')->get('root_group_id');
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

		foreach ($arr as $dg) {
			// balancesheet id set and parent id set if in array

			if($group['parent_group']){
				$group['parent_group_id'] = $this->newInstance()->load($group['parent_group'])->get('id');
			}
			
			if($group['root_group']){
				$group['root_group_id'] = $this->newInstance()->load($group['root_group'])->get('id');
			}

			$group['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->load($group['balance_sheet'])->get('id');

			$this->newInstance()->set($dg)->save();
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
		['name'=>'Capital Account','root_group'=>null,'parent_group'=>null,'balance_sheet'=>'Capital Account'],
	];

}
