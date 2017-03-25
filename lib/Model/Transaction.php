<?php
namespace xepan\accounts;

class Model_Transaction extends \xepan\base\Model_Table{
	public $table="account_transaction";
	public $acl=true;
	public $acl_type= 'Transaction';

	public $dr_accounts=array();
	public $cr_accounts=array();

	public $only_transaction=false;
	public $create_called=false;

	public $all_debit_accounts_are_mine = true;
	public $all_credit_accounts_are_mine = true;

	public $other_branch=null;
	public $other_branches_involved = array();

	public $executed=false;

	public $actions=[
		'All'=>['edit','delete','view']
	];
	
	function init(){
		parent::init();

		$this->hasOne('xepan\base\Epan','epan_id');
		$this->hasOne('xepan\hr\Employee','created_by_id')->defaultValue($this->app->employee->id);
		$this->hasOne('xepan\accounts\TransactionType','transaction_type_id');
		$this->hasOne('xepan\accounts\Currency','currency_id');
		$this->hasOne('xepan\accounts\EntryTemplate','transaction_template_id');

		$this->addField('related_id'); // used for sale invoice/purchase invoice
		$this->addField('related_type'); // Sale or Purchase
		$this->addField('related_transaction_id'); // To manage Multiple transaction in one go

		$this->addField('name')->caption('Voucher No');
		$this->addExpression('voucher_no')->set(function ($m,$q){
			return $q->getField('name');
		});


		$this->addField('Narration')->type('text');
		$this->addField('created_at')->type('date');
		$this->addField('updated_at')->type('date');
		$this->addField('exchange_rate')->type('number');

		$this->hasMany('xepan\accounts\TransactionRow','transaction_id',null,'TransactionRows');
		$this->hasMany('xepan\accounts\Transaction_Attachment','account_transaction_id',null,'Attachments');
		$this->addExpression('attachments_count')->set($this->refSQL('Attachments')->count());
		
		$this->addExpression('cr_sum')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('_amountCr');
		});

		$this->addExpression('dr_sum')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('_amountDr');
		});

		$this->addExpression('dr_sum_exchanged')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountDr');
		});

		$this->addExpression('cr_sum_exchanged')->set(function($m,$q){
			return $m->refSQL('TransactionRows')->sum('amountCr');
		});

		$this->addExpression('logged_amount')->set(function($m,$q){
			$lodge_model = $m->add('xepan\commerce\Model_Lodgement')
						->addCondition('account_transaction_id',$q->getField('id'));
			return $q->expr('IFNULL([0],0)',[$lodge_model->sum('amount')]);
		})->type('money');

		$this->addExpression('unlogged_amount')->set(function($m,$q){
			$party_row = $m->add('xepan\accounts\Model_TransactionRow',['table_alias'=>'abcd']);
			$party_row->addCondition('transaction_id',$q->getField('id'));
			$party_row->addCondition('root_group',['Trade Payables','Trade Receivables']);
			// $party_row->setLimit(1);

			// return $party_row->fieldQuery('_amountCr');

			return $q->expr("IFNULL([0],IFNULL([1],0))-[2]",[$party_row->fieldQuery('_amountDr'),$party_row->fieldQuery('_amountCr'),$m->getElement('logged_amount')]);
		})->type('money');

		$this->addExpression('party_currency_id')->set(function($m,$q){
			$party_row = $m->add('xepan\accounts\Model_TransactionRow',['table_alias'=>'abcde']);
			$party_row->addCondition('transaction_id',$q->getField('id'));
			$party_row->addCondition('root_group',['Trade Payables','Trade Receivables']);
			return $party_row->fieldQuery('currency_id');
		});


		// $this->addHook('beforeDelete',[$this,'deleteAllTransactionRow']);
		// $this->addHook('afterSave',[$this,'searchStringAfterSave']);
		// $this->add('dynamic_model/Controller_AutoCreator');

		// $this->is([
		// 	'created_at|required'
		// 	]);

		$this->addHook('beforeDelete',$this);
		$this->addHook('beforeDelete',[$this,'DeleteAttachements']);
	}

	function beforeDelete(){
		$this->add('xepan\accounts\Model_TransactionRow')
			 ->addCondition('transaction_id',$this->id)	
			 ->deleteAll();	
		$this->app->hook('deleteTransaction',[$this]);
	}

	function DeleteAttachements(){
		foreach($this->ref('Attachments') as $a){
			$a->delete();
		}
	}

	function searchStringAfterSave(){
		$str = "Transaction: ".$this['name']." ".
				$this['Narration'];

		foreach ($this->rows() as $tr) {
			$str .= $tr['side'];
			$str .= $tr->account()->get('name');
			$str .= $tr['amountCr']." ".$tr['amountDr']." ".$tr['Narration']." ".$tr['voucher_no']." ".$tr['transaction_type'];
		}

		$this['search_string'] = $str;
	}


	function cr_sum(){
		return $this->ref('TransactionRows')->sum('amountCr');
	}

	function dr_sum(){
		return $this->ref('TransactionRows')->sum('amountDr');
	}


	function rows(){
		return $this->ref('TransactionRows');
	}
	
	function createNewTransaction($transaction_type, $related_document=false, $transaction_date=null, $Narration=null, $Currency=null, $exchange_rate=1.00,$related_id=null,$related_type=null, $related_transaction_id=null, $transaction_template_id=null){
		// if($this->loaded()) throw $this->exception('Use Unloaded Transaction model to create new Transaction');
		
		$transaction_type_model = $this->add('xepan\accounts\Model_TransactionType');
		$transaction_type_model->tryLoadBy('name',$transaction_type);
		
		if(!$transaction_type_model->loaded()) $transaction_type_model->save();

		if(!$transaction_date) $transaction_date = date('Y-m-d H:i:s');

		if($Currency && !$exchange_rate) throw $this->exception('Exchange rate must be provided if providing currency');

		if(is_numeric($Currency))
			$Currency = $this->add('xepan\accounts\Model_Currency')->load($Currency);
		// Transaction TYpe Save if not available
		$this['transaction_type_id'] = $transaction_type_model->id;
		$this['name'] = $transaction_type_model->newVoucherNumber($transaction_date);
		$this['Narration'] = $Narration;
		$this['created_at'] = $transaction_date;
		$this['currency_id'] = $Currency ? $Currency->id : $this->app->epan->default_currency->id;
		$this['exchange_rate'] = $exchange_rate;
		$this['related_id'] = $related_id;
		$this['related_type'] = $related_type;
		$this['related_transaction_id'] = $related_transaction_id;
		$this['transaction_template_id'] = $transaction_template_id;

		$this->related_document = $related_document;

		$this->create_called=true;
	}

	function addDebitLedger($account, $amount, $Currency=null, $exchange_rate=1.00, $remark=null, $code=null){
		if(is_string($account)){
			$account = $this->add('xepan\accounts\Model_Ledger')->load($account);
		}

		if(is_string($Currency)){
			$Currency = $this->add('xepan\accounts\Model_Currency')->load($Currency);
		}

		$amount = $this->round($amount);						
		$this->dr_accounts[] = array('account_number'=>$account->id ,'amount'=>$amount,'account'=>$account, 'currency_id'=>$Currency?$Currency->id:$this->app->epan->default_currency->id, 'exchange_rate'=>$exchange_rate,'remark'=>$remark, 'code'=>$code);
		
	}

	function addCreditLedger($account, $amount, $Currency=null, $exchange_rate=1.00, $remark=null, $code=null){
		if(is_string($account)){
			$account = $this->add('xepan\accounts\Model_Ledger')->load($account);
		}

		if(is_string($Currency)){
			$Currency = $this->add('xepan\accounts\Model_Currency')->load($Currency);
		}

		$amount = $this->round($amount);
		
		$this->cr_accounts[] = array('account_number'=>$account->id,'amount'=>$amount,'account'=>$account, 'currency_id'=>$Currency?$Currency->id:$this->app->epan->default_currency->id, 'exchange_rate'=>$exchange_rate,'remark'=>$remark, 'code'=>$code);
	}

	function execute(){
		// if($this->loaded())
		// 	throw $this->exception('New Transaction can only be added on unLoaded Transaction Model ');

		if(!$this->create_called) throw $this->exception('Create Account Function Must Be Called First');
				
		if(($msg=$this->isValidTransaction($this->dr_accounts,$this->cr_accounts, $this['transaction_type_id'])) !== true)
			throw $this->exception('Transaction is Not Valid ' .  $msg)->addMoreInfo('message',$msg);


		try{
				$this->api->db->beginTransaction();
					$total_amount =  $this->executeSingleBranch();
				$this->api->db->commit();
			}catch(\Exception_StopInit $e){

			}catch(\Exception $e){
				$this->api->db->rollback();
				throw $e;
				
			}


		$this->executed=true;
		return $total_amount;
	}

	function executeSingleBranch(){

		$this->save();

		$total_debit_amount = 0;
		// Foreach Dr add new TransactionRow (Dr wali)
		foreach ($this->dr_accounts as $index => $dtl) {
			if($dtl['amount'] ==0) continue;
			$dtl['account']->debitWithTransaction($dtl['amount'],$this->id, $dtl['currency_id'], $dtl['exchange_rate'], $dtl['remark'], $dtl['code']);
			$total_debit_amount += ($dtl['amount']*$dtl['exchange_rate']);
		}

		
		$total_debit_amount = $this->round($total_debit_amount);

		$total_credit_amount = 0;
		// Foreach Cr add new Transactionrow (Cr Wala)
		foreach ($this->cr_accounts as $index => $dtl) {
			if($dtl['amount'] ==0) continue;
			$dtl['account']->creditWithTransaction($dtl['amount'],$this->id, $dtl['currency_id'], $dtl['exchange_rate'], $dtl['remark'], $dtl['code']);
			$total_credit_amount += ($dtl['amount']*$dtl['exchange_rate']);
		}
		
		$total_credit_amount = $this->round($total_credit_amount);
		
	// 	// Credit Sum Must Be Equal to Debit Sum
	// 	// throw new \Exception($total_credit_amount." = ".$total_debit_amount);
		if($total_debit_amount != $total_credit_amount){

			$e = $this->exception('Debit and Credit Must be Same');

			foreach ($this->dr_accounts as $index => $dtl) {
				$e->addMoreInfo('Debit: '.$dtl['account_number'],$dtl['amount'].' ['.$dtl['amount']*$dtl['exchange_rate'].'] ' . $dtl['account']['name']);
			}

			$e->addMoreInfo('DebitSum',$total_debit_amount);

			foreach ($this->cr_accounts as $index => $dtl) {
				$e->addMoreInfo('Credit: '.$dtl['account_number'],$dtl['amount'].' ['.$dtl['amount']*$dtl['exchange_rate'].'] ' . $dtl['account']['name']);
			}
			$e->addMoreInfo('CreditSum',$total_credit_amount);

			throw $e;
		}

		return $total_debit_amount;
	}

	function isValidTransaction($DRs, $CRs, $transaction_type_id){
		// if(count($DRs) > 1 AND count($CRs) > 1)
		// 	return "Dr and Cr both have multiple accounts";

		if(!count($DRs) or !count($CRs))
			return "Either Dr or Cr accounts are not present. DRs =>".count($DRs). " and CRs =>".count($CRs);

		if(!$this->all_debit_accounts_are_mine and !$this->all_credit_accounts_are_mine)
			return "Dr and Cr both contains other branch accounts";

		if(count($this->other_branches_involved) > 1)
			return "More then one other branch involved";

		return true;
	}

	function sendReceiptViaEmail($customer_email=false){
		
		if(!$this->loaded())
			return false;
		
		$order = $this->relatedDocument();

		$config = $this->add('xShop/Model_Configuration')->tryLoadAny();
		$subject = $config['cash_voucher_email_subject'];
		$subject = str_replace('{{order_no}}', $order['name'], $subject);
		$subject = str_replace('{{voucher_no}}', $this['name'], $subject);
		$email_body = $config['cash_voucher_email_body'];
		
		$email_body = str_replace('{{voucher_no}}', $this['name'], $email_body);
		$email_body = str_replace('{{order_no}}', $order['name'], $email_body);
		$email_body = str_replace('{{date}}', $this['created_at'], $email_body);
		$email_body = str_replace('{{amount}}', $this->ref('xepan/accounts/TransactionRow')->sum('amountCr'), $email_body);
		$email_body = str_replace('{{pay_to}}', $order->customer()->get('customer_name'), $email_body);
		$email_body = str_replace('{{approve_by}}', $order->searchActivity('approved'), $email_body);
		$email_body = str_replace('{{transaction_type}}', $this['transaction_type'], $email_body);

		if(strpos($this['transaction_type'],"CASH") !==false){
			$email_body = str_replace('{{cash}}',"Yes", $email_body);
			$email_body = str_replace('{{cheque}}',"No", $email_body);
		}

		if(strpos($this['transaction_type'],"BANK") !==false){
			$email_body = str_replace('{{cash}}',"No", $email_body);
			$email_body = str_replace('{{cheque}}',"Yes", $email_body);
		}

		$customer = $this->customer();
		if(!$customer_email){
			$customer_email=$customer->get('customer_email');
		}

		if(!$customer_email) return;

		$this->sendEmail($customer_email,$subject,$email_body,$ccs=array(),$bccs=array());
		if(!$order instanceof \Dummy)
			$order->createActivity('email',$subject,"Advanced Payment Voucher od Order (".$this['name'].")",$from=null,$from_id=null, $to='Customer', $to_id=$customer->id);
		return true;
	}

	function contact(){
		foreach ($this->rows() as $trrow) {
			$acc = $trrow->account();
			if($acc->isSundryDebtor())
				return $trrow->account()->contact();
		}
	}

	function round($amount){
		return round($amount,3);
	}

	function deleteTransactionRow(){
		$this->ref('TransactionRows')->deleteAll();
	}

	function customer(){
		if($this['related_id'] and $this['related_type'] ==="xepan\commerce\Model_SalesInvoice")
			return $this->add('xepan\commerce\Model_SalesInvoice')->load($this['related_id'])->customer();
		
		if($this['related_id'] and $this['related_type'] ==="xepan\accounts\Model_Ledger")
			return $this->add('xepan\accounts\Model_Ledger')->load($this['related_id'])->contact();
		
		return false;
	}

	/**
		@return ['normalize_salary_name' => [
												'ledger_id'=> 8799
												'salary _name' =>
											]
									]
	*/
	function getSalaryLedgerAssociation(){
		$sal_led_assoc = $this->add('xepan\accounts\Model_SalaryLedgerAssociation');
		$ledger_asso_array = $sal_led_assoc->getRows();

		$return = [];
		foreach ($ledger_asso_array as $key => $asso) {
			$norm_name = $this->app->normalizeName($asso['salary']);
			$return[$norm_name] = $asso;
		}
		return $return;
	}

	function updateSalaryTransaction($app,$salarysheet_mdl){

		if(!$salarysheet_mdl->loaded() AND !($salarysheet_mdl instanceof \xepan\hr\Model_SalarySheet))
			throw new \Exception("must pass Salary Sheet loaded model", 1);	

		$ledger_sal_asso_data = $this->getSalaryLedgerAssociation();
		
		$et = $this->add('xepan\accounts\Model_EntryTemplate');
		$et->loadBy('unique_trnasaction_template_code','SALARYDUE');

		if(!$et->loaded()){
			throw new \Exception("entry template not loaded");
		}

		$salary_total_amount = $salarysheet_mdl['net_amount'];
		$salary_provision_amount = $salarysheet_mdl['net_amount'];


		$pre_filled = [];

		// echo "<pre>";
		// print_r($ledger_sal_asso_data);
		// echo "</pre>";

		$sal = $this->add('xepan\hr\Model_Salary');
		foreach ($sal->getRows() as $s) {
			$nom_name = $this->app->normalizeName($s['name']);


			if(isset($ledger_sal_asso_data[$nom_name])){

				$code = $ledger_sal_asso_data[$nom_name]['code'];

				$row_model = $this->add('xepan\accounts\Model_EntryTemplateTransactionRow');
				$row_model->addCondition('code',$code);
				$row_model->addCondition('entry_template_id',$et->id);
				$row_model->tryLoadAny();

				if(!$row_model->loaded())
					continue;

				// echo " nom name =".$nom_name." code =".$code."<br/>";

				$pre_filled[$code] = [
										'ledger'=>$ledger_sal_asso_data[$nom_name]['ledger'],
										// 'ledger'=>$row_model['ledger'],
										'amount'=>$salarysheet_mdl[$nom_name],
										'currency'=>null
									];
				$salary_total_amount += $salarysheet_mdl[$nom_name];

			}
		}

		$salary_ledger = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('name','Salary');
		if($salary_ledger->loaded()){
			$pre_filled['salary'] = [
										'ledger'=>$salary_ledger['name'],
										'amount'=>$salary_total_amount,
										'currency'=>null
									];
		}

		$salary_to_pay_ledger = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('name','SalaryProvision');
		if($salary_to_pay_ledger->loaded()){
			$pre_filled['salarytopay'] = [
										'ledger'=>$salary_to_pay_ledger['name'],
										'amount'=>$salary_provision_amount,
										'currency'=>null
									];
		}

		// echo "salary_total_amount = ".$salary_total_amount."<br/>";
		// echo "salary_provision_amount = ".$salary_provision_amount."<br/>";
		
		// echo "<pre>";
		// print_r($pre_filled);
		// echo "</pre>";
		// die();
		
		// $entry_form = $this->add('xepan\accounts\Form_EntryRunner');
		// $entry_form->execute($pre_filled);

		// echo "<pre>";
		// print_r($asso_array);
		// echo "</pre>";
		// die();

		if(!in_array($salarysheet_mdl['status'], ['Approved']))			
			return;

		$transaction = $this->add('xepan\accounts\Model_Transaction');

		$new_transaction = $this->add('xepan\accounts\Model_Transaction');
		$new_transaction->createNewTransaction("SalarySheet",$salarysheet_mdl,$this['created_at'],'Salary Sheet',$this->app->epan->default_currency,1,$salarysheet_mdl->id,'xepan\hr\Model_SalarySheet');
								
		foreach ($pre_filled as $key => $value) {
		 	if($value['ledger'] === 'Salary'){
				//DR
				$salry_ledger = $this->add('xepan\accounts\Model_Ledger')->load('Salary');
				$new_transaction->addDebitLedger($salry_ledger,$value['amount'],$this->app->epan->default_currency,1.00,null,$key);
		 	}else{
				//CR
				$other_sal_ledger = $this->add('xepan\accounts\Model_Ledger')->load($value['ledger']);
				$new_transaction->addCreditLedger($other_sal_ledger, $value['amount'],$this->app->epan->default_currency,1.00,null,$key);
		 	}
		}
		$new_amount = $new_transaction->execute();
	}

	function deleteSalaryTransaction(){

	}

	function updateReimbursementTransaction($app,$rimbursement_model){		
		if(!$rimbursement_model->loaded() AND !($rimbursement_model instanceof \xepan\hr\Model_Reimbursement))
			throw new \Exception("must pass Rimbursement Model loaded model", 1);	
		
		if(!in_array($rimbursement_model['status'], ['Approved','Paid']))			
			return;

		$new_transaction = $this->add('xepan\accounts\Model_Transaction');
		$new_transaction->createNewTransaction("Reimbursement",$rimbursement_model,$rimbursement_model['created_at'],'Reimbursement To Employees',$this->app->epan->default_currency,1.00,$rimbursement_model['id'],'xepan\hr\Model_Reimbursement');

		//CR
		//Load Party Ledger
		$employee_ledger = $this->add('xepan\hr\Model_Employee')->load($rimbursement_model['employee_id'])->ledger();
		$new_transaction->addCreditLedger($employee_ledger,$rimbursement_model['amount'],$this->app->epan->default_currency);
		
		//DR
		//Load Reimbursement To Employees Ledger 
		$reimbursement_ledger = $this->add('xepan\accounts\Model_Ledger')->load("Reimbursement To Employees");
		$new_transaction->addDebitLedger($reimbursement_ledger, $rimbursement_model['amount'],$this->app->epan->default_currency);
		$new_amount = $new_transaction->execute();
	}

	function deleteReimbursementTransaction($app,$rimbursement_model){
		if(!$rimbursement_model->loaded() AND !($rimbursement_model instanceof \xepan\hr\Model_Reimbursement))
			throw new \Exception("must pass Rimbursement Model loaded model", 1);	

		$old_transaction = $this->add('xepan\accounts\Model_Transaction');
		$old_transaction->addCondition('related_id',$rimbursement_model->id);
		$old_transaction->addCondition('related_type',"xepan\hr\Model_Reimbursement");

		foreach ($old_transaction as $trans) {
			$old_transaction->deleteTransactionRow();
			$old_transaction->delete();
		}
		return true;
	}

	function updateDeductionTransaction($app,$deduction_model){		
		if(!$deduction_model->loaded() AND !($deduction_model instanceof \xepan\hr\Model_Deduction))
			throw new \Exception("must pass Rimbursement Model loaded model", 1);	
		
		if(!in_array($deduction_model['status'], ['Approved','Recieved']))			
			return;

		$new_transaction = $this->add('xepan\accounts\Model_Transaction');
		$new_transaction->createNewTransaction("Deduction",$deduction_model,$deduction_model['created_at'],'Deduction From Employees',$this->app->epan->default_currency,1.00,$deduction_model['id'],'xepan\hr\Model_Deduction');

		//CR
		//Load Deduction From Employees Ledger 
		$deduction_ledger = $this->add('xepan\accounts\Model_Ledger')->load("Deduction From Employees");
		$new_transaction->addCreditLedger($deduction_ledger, $deduction_model['amount'],$this->app->epan->default_currency);
		
		//DR
		//Load Party Ledger
		$employee_ledger = $this->add('xepan\hr\Model_Employee')->load($deduction_model['employee_id'])->ledger();
		$new_transaction->addDebitLedger($employee_ledger,$deduction_model['amount'],$this->app->epan->default_currency);
		$new_amount = $new_transaction->execute();
	}

	function deleteDeductionTransaction($app,$deduction_model){
		if(!$deduction_model->loaded() AND !($deduction_model instanceof \xepan\hr\Model_Deduction))
			throw new \Exception("must pass Deduction Model loaded model", 1);	

		$old_transaction = $this->add('xepan\accounts\Model_Transaction');
		$old_transaction->addCondition('related_id',$deduction_model->id);
		$old_transaction->addCondition('related_type',"xepan\hr\Model_Deduction");
		
		foreach ($old_transaction as $trans) {
			$old_transaction->deleteTransactionRow();
			$old_transaction->delete();
		}
		return true;
	}


	// used for transaction widget
	function populatePreFilledValues(){
		if(!$this->loaded()) throw new \Exception(" transaction model must loaded");
		
        // self tranasction pre load
        $pre_filled_values=[];

        // transaction prefilled values narration
        $tr_no=$this['transaction_type'];
        
        // transaction specific values
        $pre_filled_values[$tr_no]['narration'] = $this['Narration'];
        $pre_filled_values[$tr_no]['transaction_date'] = $this['created_at'];
        $pre_filled_values[$tr_no]['editing_transaction_id'] = $this['id'];

        foreach ($this->ref('TransactionRows') as $transaction_row) {
            $pre_filled_values[$tr_no][$transaction_row->id]=[
            							'code'=>$transaction_row['code'],
                                        'ledger'=>$transaction_row['ledger_id'],
                                        'ledger_name'=>$transaction_row['ledger'],
                                        'amount'=>$transaction_row['_amountCr']?:$transaction_row['_amountDr'],
                                        'currency'=>$transaction_row['currency_id'],
                                        'exchange_rate'=>$transaction_row['exchange_rate'],
                                        'side'=>$transaction_row['side']
                                    ];
        }

        $related_transactions = $this->add('xepan\accounts\Model_Transaction')
                                    ->addCondition('related_transaction_id',$this->id)
                                    ->setOrder('id')
                                    ;
        foreach ($related_transactions  as $tr) {
            $tr_no = $tr['transaction_type'];

            // transaction specific values
            $pre_filled_values[$tr_no]['narration'] = $tr['Narration'];
	        $pre_filled_values[$tr_no]['transaction_date'] = date("Y-m-d",strtotime($tr['created_at']));
	        $pre_filled_values[$tr_no]['editing_transaction_id'] = $tr->id;

            foreach ($tr->ref('TransactionRows') as $transaction_row) {
                $pre_filled_values[$tr_no][$transaction_row->id]=[    
                                            'code'=>$transaction_row['code'],
                                            'ledger'=>$transaction_row['ledger_id'],
                                            'ledger_name'=>$transaction_row['ledger'],
                                            'amount'=>$transaction_row['_amountCr']?:$transaction_row['_amountDr'],
                                            'currency'=>$transaction_row['currency_id'],
                                            'exchange_rate'=>$transaction_row['exchange_rate'],
                                            'side'=>$transaction_row['side'],
                                        ];
            }
        }

        return $pre_filled_values;

    }

}