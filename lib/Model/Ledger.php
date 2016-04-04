<?php
namespace xepan\accounts;

class Model_Ledger extends \xepan\base\Model_Table{
	public $table="ledger";
	public $acl=false;	
	
	function init(){
		parent::init();


		
		$this->hasOne('xepan\base\Contact','contact_id');
		$this->hasOne('xepan\accounts\Group','group_id')->mandatory(true);
		$this->hasOne('xepan\base\Epan','epan_id');
		
		$this->addField('name');
		$this->addField('related_id'); // user for related like tax/vat
		$this->addField('ledger_type'); //

		$this->addField('LedgerDisplayName')->caption('Account Displ. Name');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->addField('OpeningBalanceDr')->type('money')->defaultValue(0);
		$this->addField('OpeningBalanceCr')->type('money')->defaultValue(0);
		// $this->addField('CurrentBalanceDr')->type('money')->defaultValue(0);
		// $this->addField('CurrentBalanceCr')->type('money')->defaultValue(0);
		
		$this->addField('created_at')->type('date')->defaultValue($this->app->now);
		$this->addField('updated_at')->type('date');

		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(true);

		$this->hasMany('xepan\accounts\TransactionRow','ledger_id',null,'TransactionRows');

		$this->addExpression('parent_group')->set(function($m,$q){
			return $this->add('xepan\accounts\Model_Group',['table_alias'=>'parent_group'])
					->addCondition('id',$m->refSQL('group_id')->fieldQuery('parent_group_id'))
					->fieldQuery('name');
		});

		$this->addExpression('root_group')->set(function($m,$q){
			return $this->add('xepan\accounts\Model_Group',['table_alias'=>'root_group'])
					->addCondition('id',$m->refSQL('group_id')->fieldQuery('root_group_id'))
					->fieldQuery('name');
		});

		$this->addExpression('CurrentBalanceDr')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountDr');
		});
		$this->addExpression('CurrentBalanceCr')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountCr');
		});

		$this->addExpression('balance_signed')->set(function($m,$q){
			// return '"123"';
			return $q->expr("((IFNULL([0],0) + IFNULL([1],0))- (IFNULL([2],0)+IFNULL([3],0)))",[$m->getField('OpeningBalanceDr'),$m->getField('CurrentBalanceDr'),$m->getField('OpeningBalanceCr'),$m->getField('CurrentBalanceCr')]);
		});
		
		$this->addExpression('balance_sign')->set(function($m,$q){
			return $q->expr("IF([0]>0,'DR','CR')",[$m->getElement('balance_signed')]);
		});

		$this->addExpression('balance')->set(function($m,$q){
			return $q->expr("Concat(ABS([0]),' ',[1])",[$m->getElement('balance_signed'),$m->getElement('balance_sign')]);
		});




		$this->addHook('beforeDelete',$this);
		
		// $this->is([
		// 		'name|required|unique_in_epan'
		// 	]);
	}

	function beforeDelete(){
		if($this->ref('TransactionRows')->count()->getOne())
			throw new \Exception("This Account Cannot be Deleted, its has content Many. Please delete Transaction Row first", 1);
	}


	//creating customer ledger
	function createCustomerLedger($app,$customer_for){
		
		if(!($customer_for instanceof \xepan\commerce\Model_Customer))
			throw new \Exception("must pass customer model", 1);	
		
		if(!$customer_for->loaded())
			throw new \Exception("must pass customer loaded model", 1);	

		$debtor = $app->add('xepan\accounts\Model_Group')->loadSundryDebtor();
		
		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($customer_for,$debtor,"Customer");
	}

	//creating supplier ledger
	function createSupplierLedger($app,$supplier_for){
		
		if(!($supplier_for instanceof \xepan\commerce\Model_Supplier))
			throw new \Exception("must pass supplier model", 1);	

		if(!$supplier_for->loaded())
			throw new \Exception("must pass loaded supplier", 1);	

		$creditor = $app->add('xepan\accounts\Model_Group')->loadSundryCreditor();

		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($supplier_for,$creditor,"Supplier");

	}


	function createNewLedger($contact_for,$group,$ledger_type=null){

		$ledger = $this->add('xepan\accounts\Model_Ledger');
		$ledger->addCondition('contact_id',$contact_for->id);
		$ledger->addCondition('group_id',$group->id);
		$ledger->addCondition('ledger_type',$ledger_type);

		$ledger->tryLoadAny();

		$ledger['name'] = $contact_for['name'];
		$ledger['LedgerDisplayName'] = $contact_for['name'];
		$ledger['updated_at'] =  $this->api->now;
		$ledger['related_id'] =  $contact_for->id;
		return $ledger->save();
	}

	function createTaxLedger($app,$tax_obj){
		
		if(!($tax_obj instanceof \xepan\commerce\Model_Taxation))
			throw new \Exception("must pass taxation model", 1);	

		if(!$tax_obj->loaded())
			throw new \Exception("must loaded taxation", 1);

		$ledger = $app->add('xepan\accounts\Model_Ledger');
		$ledger->addCondition('group_id',$app->add('xepan\accounts\Model_Group')->loadDutiesAndTaxes()->get('id'));
		$ledger->addCondition('ledger_type',$tax_obj['name']);
		$ledger->addCondition('related_id',$tax_obj->id);

		$ledger->tryLoadAny();

		$ledger['name'] = $tax_obj['name'];
		$ledger['LedgerDisplayName'] = $tax_obj['name'];
		$ledger['updated_at'] =  $app->api->now;
		return $ledger->save();
	}

	function LoadTaxLedger($tax_obj){
		if(!($tax_obj instanceof \xepan\commerce\Model_Taxation))
			throw new \Exception("must pass taxation model", 1);	

		if(!$tax_obj->loaded())
			throw new \Exception("must loaded taxation", 1);

		$ledger = $this->add('xepan\accounts\Model_Ledger');
		$ledger->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadDutiesAndTaxes()->get('id'));
		$ledger->addCondition('ledger_type',$tax_obj['name']);
		$ledger->addCondition('related_id',$tax_obj->id);
		return $ledger->tryLoadAny();
	}


	function debitWithTransaction($amount,$transaction_id,$currency_id,$exchange_rate){

		$transaction_row=$this->add('xepan\accounts\Model_TransactionRow');
		$transaction_row['_amountDr']=$amount;
		$transaction_row['side']='DR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['ledger_id']=$this->id;
		$transaction_row['currency_id']=$currency_id;
		$transaction_row['exchange_rate']=$exchange_rate;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		$this->debitOnly($amount);
	}

	function creditWithTransaction($amount,$transaction_id,$currency_id,$exchange_rate){

		$transaction_row=$this->add('xepan\accounts\Model_TransactionRow');
		$transaction_row['_amountCr']=$amount;
		$transaction_row['side']='CR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['ledger_id']=$this->id;
		$transaction_row['currency_id']=$currency_id;
		$transaction_row['exchange_rate']=$exchange_rate;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		// if($only_transaction) return;
		
		$this->creditOnly($amount);
	}

	function debitOnly($amount){ 
		$this->hook('beforeAccountDebited',array($amount));
		$this['CurrentBalanceDr']=$this['CurrentBalanceDr']+$amount;
		$this->save();
		$this->hook('afterAccountDebited',array($amount));
	}

	function creditOnly($amount){
		$this->hook('beforeAccountCredited',array($amount));
		$this['CurrentBalanceCr']=$this['CurrentBalanceCr']+$amount;
		$this->save();
		$this->hook('afterAccountCredited',array($amount));
	}

	function getOpeningBalance($on_date=null,$side='both',$forPandL=false) {
		if(!$on_date) $on_date = '1970-01-02';
		if(!$this->loaded()) throw $this->exception('Model Must be loaded to get opening Balance','Logic');
		

		$transaction_row=$this->add('xepan\accounts\Model_TransactionRow');
		$transaction_join=$transaction_row->join('account_transaction.id','transaction_id');
		$transaction_join->addField('transaction_date','created_at');
		$transaction_row->addCondition('transaction_date','<',$on_date);
		$transaction_row->addCondition('ledger_id',$this->id);

		if($forPandL){
			$financial_start_date = $this->api->getFinancialYear($on_date,'start');
			$transaction_row->addCondition('created_at','>=',$financial_start_date);
		}

		$transaction_row->addExpression('sdr')->set(function($m,$q){
			return $q->expr('sum([0])',[$m->getField('amountDr')]);
		});

		$transaction_row->addExpression('scr')->set(function($m,$q){
			return $q->expr('sum([0])',[$m->getField('amountCr')]);
		});

		// $transaction_row->_dsql()->del('fields')->field('SUM(amountDr) sdr')->field('SUM(amountCr) scr');
		$result = $transaction_row->_dsql()->getHash();

		if($this['OpeningBalanceCr'] ==null){
			$temp_account = $this->add('xepan\accounts\Model_Account')->load($this->id);
			$this['OpeningBalanceCr'] = $temp_account['OpeningBalanceCr'];
			$this['OpeningBalanceDr'] = $temp_account['OpeningBalanceDr'];
		}

		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $this['OpeningBalanceCr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $this['OpeningBalanceDr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function loadDefaultSalesAccount(){
		$this->addCondition('name','Sales Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadDirectIncome()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}

	function loadDefaultPurchaseAccount(){
		$this->addCondition('name','Purchase Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadDirectExpenses()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;	
	}

	function loadDefaultRoundAccount(){
		$this->addCondition('name','Round Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadIndirectIncome()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}

	function loadDefaultTaxAccount(){
		$this->addCondition('name','Tax Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadDutiesAndTaxes()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}


	function loadDefaultDiscountAccount(){
		$this->addCondition('name','Discount Given');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadDirectExpenses()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}

	function loadDefaultShippingAccount(){
		$this->addCondition('name','Shipping Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadIndirectExpenses()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;	
	}


	function loadCashAccounts(){
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadCashAccount()->fieldQuery('id'));
		return $this;
	}

	function loadDefaultCashAccount(){
		$this->addCondition('name','Cash Account');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadCashAccount()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}

	function loadBankAccounts(){
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadBankAccounts()->fieldQuery('id'));
		return $this;
	}

	function loadDefaultBankAccount(){
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadBankAccounts()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this['name']='Your Default Bank Account';
			$this->save();
		}

		return $this;
	}

	function loadDefaultBankChargesAccount(){
		$this->addCondition('name','Bank Charges');
		$this->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->loadIndirectExpenses()->fieldQuery('id'));
		$this->tryLoadAny();

		if(!$this->loaded()){
			$this->save();
		}

		return $this;
	}

	function contact(){
		if($this['contact_id'])
			return $this->ref('contact_id');

		return false;
	}

	function group(){
		return $this->ref('group_id');
	}

	function isSundryDebtor(){
		return $this->group()->isSundryDebtor();
	}

	function isSundryCreditor(){
		return $this->group()->isSundryCreditor();
	}

	function loadCustomerLedger($customer_id){
		
		$this->addCondition('contact_id',$customer_id);
		$this->tryLoadAny();
		return $this;
	}

}
