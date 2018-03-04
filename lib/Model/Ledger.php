<?php
namespace xepan\accounts;

class Model_Ledger extends \xepan\base\Model_Table{
	public $table="ledger";
	public $acl_type='Ledger';	
	
	function init(){
		parent::init();
		
		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue(@$this->app->employee->id);
		
		$this->hasOne('xepan\base\Contact','contact_id')->display(['form'=>'xepan\base\Basic']);
		$this->hasOne('xepan\accounts\Group','group_id')->mandatory(true);
		$this->hasOne('xepan\base\Epan','epan_id');
		
		$this->addField('name')->sortable(true);
		$this->addField('related_id'); // user for related like tax/vat
		$this->addField('ledger_type'); //

		$this->addField('LedgerDisplayName')->caption('Ledger Displ. Name');
		$this->addField('is_active')->type('boolean')->defaultValue(true);

		$this->addField('OpeningBalanceDr')->type('money')->defaultValue(0);
		$this->addField('OpeningBalanceCr')->type('money')->defaultValue(0);
		// $this->addField('CurrentBalanceDr')->type('money')->defaultValue(0);
		// $this->addField('CurrentBalanceCr')->type('money')->defaultValue(0);
		
		$this->addField('created_at')->type('date')->defaultValue($this->app->now);
		$this->addField('updated_at')->type('date')->defaultValue($this->app->now);

		$this->addField('affectsBalanceSheet')->type('boolean')->defaultValue(true);

		$this->hasMany('xepan\accounts\TransactionRow','ledger_id',null,'TransactionRows');

		$this->addExpression('balance_sheet_id')->set(function($m,$q){
			return $m->refSQL('group_id')->fieldQuery('balance_sheet_id');
		});

		$this->addExpression('balance_sheet')->set(function($m,$q){
			return $m->refSQL('group_id')->fieldQuery('balance_sheet');
		});


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

		$this->addExpression('root_group_id')->set(function($m,$q){
			return $this->add('xepan\accounts\Model_Group',['table_alias'=>'root_group'])
					->addCondition('id',$m->refSQL('group_id')->fieldQuery('root_group_id'))
					->fieldQuery('id');
		});

		$this->addExpression('group_path')->set(function($m,$q){
			return $this->add('xepan\accounts\Model_Group',['table_alias'=>'group_path'])
					->addCondition('id',$m->getElement('group_id'))
					->fieldQuery('path');
		});

		$this->addExpression('report_name')->set(function($m,$q){
			return $m->add('xepan\accounts\Model_BalanceSheet',['table_alias'=>'for_report_name'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('report_name');
		});

		$this->addExpression('subtract_from')->set(function($m,$q){
			return  $m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('subtract_from');
		});

		$this->addExpression('positive_side')->set(function($m,$q){
			return  $q->expr('IF([0]="RT","Assets","Liabilities")',[$m->add('xepan\accounts\Model_BalanceSheet',['pandl_check'])
						->addCondition('id',$m->getElement('balance_sheet_id'))
						->fieldQuery('positive_side')]);
		});

		$this->addExpression('CurrentBalanceDr')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountDr');
		});
		$this->addExpression('CurrentBalanceCr')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountCr');
		});

		$this->addExpression('balance_signed')->set(function($m,$q){
			return $q->expr("((IFNULL([0],0) + IFNULL([1],0))- (IFNULL([2],0)+IFNULL([3],0)))",[$m->getField('OpeningBalanceDr'),$m->getField('CurrentBalanceDr'),$m->getField('OpeningBalanceCr'),$m->getField('CurrentBalanceCr')]);
		});
		
		$this->addExpression('balance_sign')->set(function($m,$q){
			return $q->expr("IF([0]>0,'DR','CR')",[$m->getElement('balance_signed')]);
		});

		$this->addExpression('balance')->set(function($m,$q){
			return $q->expr("Concat(ABS([0]),' ',[1])",[$m->getElement('balance_signed'),$m->getElement('balance_sign')]);
		});

		$this->addHook('beforeDelete',$this);
		
		$this->is([
				'name|required|unique_in_epan'
			]);
	}

	function beforeDelete(){
		if($this->ref('TransactionRows')->count()->getOne())
			throw new \Exception("This Account Cannot be Deleted, its has content Many. Please delete Transaction Row first", 1);
	}


	//creating Employee ledger
	function createEmployeeLedger($app,$employee_for){
		if(!($employee_for instanceof \xepan\hr\Model_Employee))
			throw new \Exception("must pass Employee model", 1);	
		
		if(!$employee_for->loaded())
			throw new \Exception("must pass Employee loaded model", 1);	

		$creditor = $app->add('xepan\accounts\Model_Group')->load("Sundry Creditor");
		
		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($employee_for['unique_name'],$creditor->id,$employee_for->id,['ledger_type'=>'Employee','LedgerDisplayName'=>$employee_for['name'],'contact_id'=>$employee_for->id]);
	}

	//creating customer ledger
	function createCustomerLedger($app,$customer_for){

		if(!($customer_for instanceof \xepan\commerce\Model_Customer))
			throw new \Exception("must pass customer model", 1);	
		
		if(!$customer_for->loaded())
			throw new \Exception("must pass customer loaded model", 1);	

		$debtor = $app->add('xepan\accounts\Model_Group')->load("Sundry Debtor");
		
		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($customer_for['unique_name'],$debtor->id,$customer_for->id,['ledger_type'=>'Customer','LedgerDisplayName'=>$customer_for['name'],'contact_id'=>$customer_for->id]);
	}

	//creating supplier ledger
	function createSupplierLedger($app,$supplier_for){	
		
		if(!($supplier_for instanceof \xepan\commerce\Model_Supplier))
			throw new \Exception("must pass supplier model", 1);	

		if(!$supplier_for->loaded())
			throw new \Exception("must pass loaded supplier", 1);	

		$creditor = $app->add('xepan\accounts\Model_Group')->load("Sundry Creditor");

		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($supplier_for['unique_name'],$creditor->id,$supplier_for->id,['ledger_type'=>'Supplier','LedgerDisplayName'=>$supplier_for['name'],'contact_id'=>$supplier_for->id]);

	}

	function createOutsourcePartyLedger($app,$outsource_party_for){	
		
		if(!($outsource_party_for instanceof \xepan\production\Model_OutsourceParty))
			throw new \Exception("must pass outsourceparty model", 1);	

		if(!$outsource_party_for->loaded())
			throw new \Exception("must pass loaded outsourceparty", 1);	

		$outsource = $app->add('xepan\accounts\Model_Group')->load("Sundry Creditor");

		return $app->add('xepan\accounts\Model_Ledger')->createNewLedger($outsource_party_for['unique_name'],$outsource->id,$outsource_party_for->id,['ledger_type'=>'OutsourceParty','LedgerDisplayName'=>$outsource_party_for['name'],'contact_id'=>$outsource_party_for->id]);
	}

	function createTaxLedger($tax_obj){
	
	if(!($tax_obj instanceof \xepan\commerce\Model_Taxation))
		throw new \Exception("must pass taxation model", 1);	

	if(!$tax_obj->loaded())
		throw new \Exception("must loaded taxation", 1);

	$ledger = $this->add('xepan\accounts\Model_Ledger');
	$ledger->addCondition('group_id',$this->add('xepan\accounts\Model_Group')->load("Tax Payable")->get('id'));
	$ledger->addCondition('ledger_type','SalesServiceTaxes');

	$ledger->tryLoadAny();

	$ledger['name'] = $tax_obj['name'];
	$ledger['ledger_type'] = 'SalesServiceTaxes';
	$ledger['LedgerDisplayName'] = 'SalesServiceTaxes';
	$ledger['related_id'] = $tax_obj['id'];
	$ledger['updated_at'] =  $this->api->now;
	return $ledger->save();
	}

	function createNewLedger($name,$group_id,$contact_id,$other_values=array()){
		$ledger = $this->newInstance();
		$ledger->addCondition('group_id',$group_id);
		$ledger->addCondition('contact_id',$contact_id);
		$ledger->tryLoadAny();
		
		if(!$ledger->loaded()){
			$ledger['name'] = $name;
		
			foreach ($other_values as $field => $value) {
				$ledger[$field] = $value;
			}
			$ledger->save();
		
		}else{
			if($ledger['name'] != $name){
				$ledger['name'] = $name;
				$ledger->save();
			}
		} 
		
		return $ledger;
	}

	function loadDefaults(){

		$data= $this->defaultLedger;

		foreach ($data as $ledger) {
			// group id set
			if($this->newInstance()->tryLoadBy('name',$ledger['name'])->loaded()) continue;
			$ledger['group_id'] = $this->add('xepan\accounts\Model_Group')->load($ledger['group'])->get('id');

			$this->newInstance()->set($ledger)->save();
		}
	}

	function load($id_name){
		if(is_numeric($id_name)) return parent::load($id_name);
		
		$this->unload();

		$this->tryLoadBy('name',$id_name);
		if($this->loaded()) return $this;

		foreach ($this->defaultLedger as $ledger) {
			if($ledger['name']==$id_name){
				// group id set 
				$ledger['group_id'] = $this->add('xepan\accounts\Model_Group')->load($ledger['group'])->get('id');

				$this->set($ledger)->save();
				return $this;
			}
		}

		throw $this->exception('Could Not Load Ledger');
	}

	function check($name){
		return $this['name']===$name;
	}

	public $defaultLedger=[

		['name'=>'Miscellaneous Expenses','group'=>'Miscellaneous Expenses','ledger_type'=>'Expenses','LedgerDisplayName'=>'Miscellaneous Expenses'],
		['name'=>'Sales Account','group'=>'Sales','ledger_type'=>'Sales','LedgerDisplayName'=>'Sales Account'],
		['name'=>'Purchase Account','group'=>'Purchase','ledger_type'=>'Purchase','LedgerDisplayName'=>'Purchase Account'],
		['name'=>'Round Account','group'=>'Round Income','ledger_type'=>'Income','LedgerDisplayName'=>'Round Account'],
		['name'=>'Tax Account','group'=>'Tax Payable','ledger_type'=>'Tax','LedgerDisplayName'=>'Tax Name'],
		['name'=>'Rebate & Discount Allowed','group'=>'Rebate & Discount Allowed','ledger_type'=>'Discount','LedgerDisplayName'=>'Discount Allowed'],
		['name'=>'Rebate & Discount Received','group'=>'Rebate & Discount Received','ledger_type'=>'Discount','LedgerDisplayName'=>'Discount Received'],
		['name'=>'Shipping Account','group'=>'Shipping Expenses','ledger_type'=>'Expenses','LedgerDisplayName'=>'Shipping Account'],
		['name'=>'Exchange Rate Different Loss','group'=>'Exchange Expenses','ledger_type'=>'Expenses','LedgerDisplayName'=>'Exchange Loss'],
		['name'=>'Exchange Rate Different Gain','group'=>'Exchange Income','ledger_type'=>'Income','LedgerDisplayName'=>'Exchange Gain'],
		['name'=>'Bank Charges','group'=>'Bank Charges Expenses','ledger_type'=>'Bank Charges','LedgerDisplayName'=>'Bank Charges'],
		['name'=>'Cash Account','group'=>'Cash In Hand','ledger_type'=>'Cash Account','LedgerDisplayName'=>'Cash Account'],
		['name'=>'Your Default Bank Account','group'=>'Bank Account','ledger_type'=>'Bank','LedgerDisplayName'=>'Your Default Bank Account'],
		['name'=>'Profit & Loss (Opening)','group'=>'Profit & Loss (Opening)','ledger_type'=>'Profit & Loss (Opening)','LedgerDisplayName'=>'Profit & Loss (Opening)']
	
	];	


	function debitWithTransaction($amount,$transaction_id,$currency_id,$exchange_rate, $remark=null,$code=null){

		$transaction_row=$this->add('xepan\accounts\Model_TransactionRow');
		$transaction_row['_amountDr']=$amount;
		$transaction_row['side']='DR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['ledger_id']=$this->id;
		$transaction_row['currency_id']=$currency_id;
		$transaction_row['exchange_rate']=$exchange_rate;
		$transaction_row['remark']=$remark;
		$transaction_row['code']=$code;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		$this->debitOnly($amount);
	}

	function creditWithTransaction($amount,$transaction_id,$currency_id,$exchange_rate,$remark=null, $code=null){

		$transaction_row=$this->add('xepan\accounts\Model_TransactionRow');
		$transaction_row['_amountCr']=$amount;
		$transaction_row['side']='CR';
		$transaction_row['transaction_id']=$transaction_id;
		$transaction_row['ledger_id']=$this->id;
		$transaction_row['currency_id']=$currency_id;
		$transaction_row['exchange_rate']=$exchange_rate;
		$transaction_row['remark']=$remark;
		$transaction_row['code']=$code;
		// $transaction_row['accounts_in_side']=$no_of_accounts_in_side;
		$transaction_row->save();

		// if($only_transaction) return;
		
		$this->creditOnly($amount);
	}

	function debitOnly($amount){ 
		$this->hook('beforeLedgerDebited',array($amount));
		$this['CurrentBalanceDr']=$this['CurrentBalanceDr']+$amount;
		$this->save();
		$this->hook('afterLedgerDebited',array($amount));
	}

	function creditOnly($amount){
		$this->hook('beforeLedgerCredited',array($amount));
		$this['CurrentBalanceCr']=$this['CurrentBalanceCr']+$amount;
		$this->save();
		$this->hook('afterLedgerCredited',array($amount));
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
		$result = $transaction_row->getRows();
		$result=$result[0];
		// if($this['OpeningBalanceCr'] ==null){
		// 	$temp_account = $this->add('xepan\accounts\Model_Ledger')->load($this->id);
		// 	$this['OpeningBalanceCr'] = $temp_account['OpeningBalanceCr'];
		// 	$this['OpeningBalanceDr'] = $temp_account['OpeningBalanceDr'];
		// }


		$cr = $result['scr'];
		if(!$forPandL) $cr = $cr + $this['OpeningBalanceCr'];
		if(strtolower($side) =='cr') return $cr;

		$dr = $result['sdr'];		
		if(!$forPandL) $dr = $dr + $this['OpeningBalanceDr'];
		if(strtolower($side) =='dr') return $dr;

		return array('CR'=>$cr,'DR'=>$dr,'cr'=>$cr,'dr'=>$dr,'Cr'=>$cr,'Dr'=>$dr);
	}

	function quickSearch($app,$search_string,&$result_array,$relevency_mode){

		$this->addExpression('Relevance')->set('MATCH(name, ledger_type, LedgerDisplayName) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$this->addCondition('Relevance','>',0);
 		$this->setOrder('Relevance','Desc');
 			
 		if($this->count()->getOne()){
 			foreach ($this->getRows() as $data) {	 				 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_accounts_accounts',['status'=>$data['status']])->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 				];
 			}
		}

		$groups = $this->add('xepan\accounts\Model_Group');
		$groups->addExpression('Relevance')->set('MATCH(name) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$groups->addCondition('Relevance','>',0);
 		$groups->setOrder('Relevance','Desc');
 		
 		if($groups->count()->getOne()){
 			foreach ($groups->getRows() as $data) {	 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_accounts_group')->getURL(),
 				];
 			}
		}

		$currency = $this->add('xepan\accounts\Model_Currency');
		$currency->addExpression('Relevance')->set('MATCH(search_string) AGAINST ("'.$search_string.'" '.$relevency_mode.')');
		$currency->addCondition('Relevance','>',0);
 		$currency->setOrder('Relevance','Desc');
 		
 		if($currency->count()->getOne()){
 			foreach ($currency->getRows() as $data) {	 				
 				$result_array[] = [
 					'image'=>null,
 					'title'=>$data['name'],
 					'relevency'=>$data['Relevance'],
 					'url'=>$this->app->url('xepan_accounts_currency')->getURL(),
 					'type_status'=>$data['type'].' '.'['.$data['status'].']',
 				];
 			}
		}
	}


	function contact(){
		if($this['contact_id'])
			return $this->ref('contact_id');

		return false;
	}

	function group(){
		return $this->ref('group_id');
	}

	function getBalance($from_date=null,$to_date=null){
		// if(!$this->loaded()) throw new \Exception("Ledger Model Must Be Loaded", 1);
		
		return rand(999,99999);	
	}

	function sendEmail($from_email=null,$to_emails=null,$cc_emails=null,$bcc_emails=null,$subject=null,$body=null){
		$email_setting = $this->add('xepan\communication\Model_Communication_EmailSetting');
		$email_setting->tryLoad($from_email?:-1);

		$communication = $this->add('xepan\communication\Model_Communication_Abstract_Email');					
		$communication->getElement('status')->defaultValue('Draft');
		$communication['direction']='Out';


		$communication->setfrom($email_setting['from_email'],$email_setting['from_name']);
		$communication->addCondition('communication_type','Email');
		
		$to_emails=explode(',', trim($to_emails));
		foreach ($to_emails as $to_mail) {
			$communication->addTo($to_mail);
		}
		if($cc_emails){
			$cc_emails=explode(',', trim($cc_emails));
			foreach ($cc_emails as $cc_mail) {
					$communication->addCc($cc_mail);
			}
		}
		if($bcc_emails){
			$bcc_emails=explode(',', trim($bcc_emails));
			foreach ($bcc_emails as $bcc_mail) {
					$communication->addBcc($bcc_mail);
			}
		}
		$communication->setSubject($subject);
		$communication->setBody($body);
		$communication->save();
		$communication->findContact('to');
		$communication->send($email_setting);
	}
}
