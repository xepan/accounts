<?php
namespace xepan\accounts;

class Model_Transaction_Attachment extends \xepan\base\Model_Table{
	public $table = 'account_transaction_attachment';
	// public $acl = false;
	function init(){
		parent::init();

		$this->hasOne('xepan\accounts\Transaction','account_transaction_id');
		$this->add('xepan\filestore\Field_File','file_id');

		$this->addExpression('thumb_url')->set(function($m,$q){
			return $q->expr('[0]',[$m->getElement('file')]);
		});
		$this->addHook('beforeDelete',$this);

	}

	function beforeDelete(){
		$file = $this->add('xepan\filestore\Model_File');
		$file->addCondition('id',$this['file_id']);

		$file->each(function($m){
			$m->delete();
		});
	}
}