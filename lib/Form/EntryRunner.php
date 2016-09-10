<?php


namespace xepan\accounts;

class Form_EntryRunner extends \Form {

    public $template_id=null;

    function setModel($model,$related_id=null, $related_type=null, $pre_filled_values=[],$default_narration=null){
        $date = $this->app->today;
        $narration=$default_narration;

        if($model instanceof \xepan\accounts\Model_Transaction){
            if($model['related_transaction_id']){
                $model->load($model['related_transaction_id']);
            }
            $transaction_to_edit = $model;

            if(!$model['transaction_template_id']){
                $this->owner->add('View')->set('It is related document');
                throw $this->exception('','StopInit');
            }

            $transaction_m = $transaction_to_edit->ref('transaction_template_id');
            $transactions = $transaction_m->ref('xepan\accounts\EntryTemplateTransaction');
            $related_id = $transaction_to_edit['related_id'];
            $related_type = $transaction_to_edit['related_type'];
            $date = $transaction_to_edit['created_at'];
            $narration= $transaction_to_edit['Narration'];
        }elseif($model instanceof \xepan\accounts\Model_EntryTemplate){
            $transaction_m = $model;
            $transactions = $model->ref('xepan\accounts\EntryTemplateTransaction');
            $transaction_to_edit=null;
        }else{
            throw $this->exception('Only Loaded, Transaction and Entry Template Models permitted')
                        ->addMOreInfo('transaction_provided',get_class($transaction_m));
        }


        $this->template_id = $transaction_m->id;

        $template = $this->add('GiTemplate');
        $template->loadTemplate('view/form/entrytransaction');
        $template->trySetHTML('date','{$date}');
        $template->trySetHTML('editing_transaction_id','{$editing_transaction_id}');
        $template->trySetHTML('narration','{$narration}');
        
        foreach ($transactions as $trans) {
            $transaction_template = $this->add('GiTemplate');
            $transaction_template->loadTemplate('view/form/entrytransactionsides');

            $transaction_template->trySetHTML('transaction_name','{$transaction_name_'.$trans->id.'}');

            foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
                if($row['side']=="DR"){
                    $row_left_template = $this->add('GiTemplate');
                    $row_left_template->loadTemplate('view/form/entrytransactionsiderows');
                    $row_left_template->trySetHTML('ledger','{$left_ledger_'.$row->id.'}');
                    $row_left_template->trySetHTML('amount','{$left_amount_'.$row->id.'}');
                    $row_left_template->trySetHTML('currency','{$left_currency_'.$row->id.'}');
                    $row_left_template->trySetHTML('exchange_rate','{$left_exchange_rate_'.$row->id.'}');
                    $row_left_template->trySetHTML('side','left');
                    $transaction_template->appendHTML('transaction_row_left',$row_left_template->render());
                    
                }else{
                    $row_right_template = $this->add('GiTemplate');
                    $row_right_template->loadTemplate('view/form/entrytransactionsiderows');
                    $row_right_template->trySetHTML('ledger','{$right_ledger_'.$row->id.'}');
                    $row_right_template->trySetHTML('amount','{$right_amount_'.$row->id.'}');
                    $row_right_template->trySetHTML('currency','{$right_currency_'.$row->id.'}');
                    $row_right_template->trySetHTML('exchange_rate','{$right_exchange_rate_'.$row->id.'}');
                    $row_right_template->trySetHTML('side','right');
                    $transaction_template->appendHTML('transaction_row_right',$row_right_template->render());
                }   
            }
            $template->appendHTML('transactions',$transaction_template->render());
        }

        // echo (htmlentities($template->render()));
        // exit;

        $template->loadTemplateFromString($template->render());
        $this->setLayout($template);

        if($transaction_to_edit && $transaction_to_edit->loaded()){
            $pre_filled_values = $this->populatePreFilledValues($transaction_to_edit);
        }

        $this->addField('DatePicker','date')->set($date);
        $this->addField('Hidden','editing_transaction_id')->set($transaction_to_edit?$transaction_to_edit->id:0);

        
        $tr_no=1;
        foreach ($transactions as $trans) {
            $this->layout->add('View',null,'transaction_name_'.$trans->id)->set($trans['name']);
            $tr_row_no=1;
            foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {

                if($row['is_allow_add_ledger'])
                    $field_type= 'xepan\base\Plus';
                else
                    $field_type= 'autocomplete\Basic';
                
                $spot = $row['side']=='DR'?'left':'right';          

                $field = $this->addField($field_type,['name'=>'ledger_'.$row->id,'hint'=>'Select Ledger'], $row['title'],null,$spot.'_ledger_'.$row->id);
                $field->addClass('ledger')->addClass($spot);
                $field->show_fields= ['name'];

                $row_ledger_present = $row['ledger']?true:false;
                $row_ledger=null;
                if($row_ledger_present){
                    $row_ledger = $this->add('xepan\accounts\Model_Ledger')->tryLoadBy('name',$row['ledger']);
                }

                $row_group_present = $row['group']?true:false;
                if($row_group_present){
                    $row_group = $this->add('xepan\accounts\Model_Group')->tryLoadBy('name',$row['group']);
                }else{
                    $row_group = $row_ledger->ref('group_id');
                }

                $ledger = $this->add('xepan\accounts\Model_Ledger');

                // if($row['is_include_subgroup_ledger_account']){
                //  $ledger->addCondition('root_group_id',$row_group['root_group_id']);
                // }else{
                    $ledger->addCondition('group_id',$row_group->id);
                // }

                if(!$row['is_ledger_changable']){
                    $ledger->addCondition('name',$row['ledger']);

                    if($row_ledger_present)
                        $field->set($row_ledger->id);
                }

                if(isset($pre_filled_values[$tr_no][$row['code']]['ledger'])){                  
                    $ledger->addCondition('id',is_numeric($pre_filled_values[$tr_no][$row['code']]['ledger'])?$pre_filled_values[$tr_no][$row['code']]['ledger']:$pre_filled_values[$tr_no][$row['code']]['ledger']->id);
                }

                $field->setModel($ledger);
                
                if(isset($pre_filled_values[$tr_no][$row['code']]['ledger'])){                  
                    $field->set(is_numeric($pre_filled_values[$tr_no][$row['code']]['ledger'])?$pre_filled_values[$tr_no][$row['code']]['ledger']:$pre_filled_values[$tr_no][$row['code']]['ledger']->id);
                }

                if($row['is_include_currency']){
                    $form_currency = $this->addField('Dropdown','bank_currency_'.$row->id,'Currency Name',null,$spot.'_currency_'.$row->id);
                    $form_currency->addClass('currency')->addClass($spot);
                    $form_currency->setModel('xepan\accounts\Currency');
                    if(isset($pre_filled_values[$tr_no][$row['code']]['currency'])){
                        $form_currency->set(is_numeric($pre_filled_values[$tr_no][$row['code']]['currency'])?$pre_filled_values[$tr_no][$row['code']]['currency']:$pre_filled_values[$tr_no][$row['code']]['currency']->id);
                    }

                    $exchange_rate = $this->addField('line','to_exchange_rate_'.$row->id,'Currency Rate',null,$spot.'_exchange_rate_'.$row->id)->validateNotNull(true)->addClass('exchange-rate');
                    $exchange_rate->addClass('exchange_rate')->addClass($spot);
                    if(isset($pre_filled_values[$tr_no][$row['code']]['exchange_rate'])){
                        $exchange_rate->set($pre_filled_values[$tr_no][$row['code']]['exchange_rate']);
                    }
                }
                $field = $this->addField('line','amount_'.$row->id,'Amount',null,$spot.'_amount_'.$row->id);
                $field->addClass('amount')->addClass($spot);

                if(isset($pre_filled_values[$tr_no][$row['code']]['amount'])){
                    $field->set($pre_filled_values[$tr_no][$row['code']]['amount']);
                }

                $tr_row_no++;
            }
            $tr_no++;
        }

        $this->addField('Text','narration')->set($narration);

        $this->addSubmit('DO')->addClass('btn btn-primary');


        if($this->isSubmitted()){
            $data=[];
            foreach ($transactions as $trans) {
                $transaction = [];
                $transaction['type'] = $trans['type'];
                $transaction['date'] = $this['date'];
                $transaction['narration'] = $this['narration'];
                $transaction['related_id'] = $related_id;
                $transaction['related_type'] = $related_type;
                $transaction['narration'] = $this['narration'];
                $transaction['rows']=[];

                foreach ($trans->ref('xepan\accounts\EntryTemplateTransactionRow') as $row) {
                    if(!$this['ledger_'.$row->id]) continue;

                    $transaction_row=[];
                    
                    $currency=$this->app->epan->default_currency->id;
                    $exchange_rate = 1.0;

                    if($row['is_include_currency']){
                        $currency = $this['bank_currency_'.$row->id] ;//$this->add('xepan\accounts\Model_Currency')->load($form['bank_currency_'.$row->id]);
                        $exchange_rate = $this['to_exchange_rate_'.$row->id];
                        if($currency == $this->app->epan->default_currency->id && empty($exchange_rate))
                            $exchange_rate = 1.0;
                        elseif($currency != $this->app->epan->default_currency->id && empty($exchange_rate))
                            $this->displayError('to_exchange_rate_'.$row->id,'Please fill');
                    }

                    $transaction_row['currency'] = $currency;
                    $transaction_row['exchange_rate'] = $exchange_rate;


                    if($row['side']=='CR')
                        $transaction_row['side']='CR';
                    else
                        $transaction_row['side']='DR';

                    $transaction_row['ledger'] = $this['ledger_'.$row->id];//$this->add('xepan\accounts\Model_Ledger')->load($form['ledger_'.$row->id]);
                    $transaction_row['amount'] = $this['amount_'.$row->id];

                    $transaction['rows'][$row['code']] = $transaction_row;
                }
                $data[] = $transaction;

            }

            try{
                $this->api->db->beginTransaction();
                    $new_id = $this->execute($data, $this['editing_transaction_id']);
                $this->api->db->commit();
            }catch(\Exception_StopInit $e){

            }catch(\Exception $e){
                $this->api->db->rollback();
                throw $e;
            }

            if($this['editing_transaction_id']){
                // Most probabaly you are comming from CRUD in editing mode
                // Oops ;) last transactions was deleted and this is all new transaction..
                // Don't reload form with old id, or you will see EXCEPTIONS .. hahahahaha

                $js=[];
                $js[] = $this->js()->univ()->closeDialog();
                $js[] = $this->js()->_selector('.account_grid')->trigger('reload');
                if($this->app->db->inTransaction()) $this->app->db->commit();
                $this->js(null,$js)->execute();
            }else{
                // Most probabely you are doing new entry so its ok to reload form.
                $this->app->page_action_result = $this->js(null,$this->js()->univ()->closeDialog())->reload();
            }
        }

        $this->app->js(true)
                    ->_load('xepan_accounts_widget')
                    ->_selector('.xepan-accounts-transaction-block')
                    ->xepan_accounts_widget();


        return $model;
    }

    // As per given rules of this template, ie groups accounts etc.
    function verifyData($data){ //dr=>[['acc'=>'amt'],['acc'=>amt]],cr=>[['acc'=>amt]]

    }

    function execute($data=[],$editing_transaction_id=null){ //transaction_no=>[dr=>[['acc'=>$acc,'amt'=>$amt,'currency'=>$curr,'exchange_rate'=>$exchange_rate],['acc'=>$acc ....]],cr=>[['acc'=>...]]]
        $this->hook('beforeExecute',[$data]);
        $transactions=[];
        $total_amount=[];
        $related_transaction_id = null;

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // throw new \Exception("Error Processing Request", 1);

        if($editing_transaction_id){
            $this->add('xepan\accounts\Model_Transaction')->load($editing_transaction_id)->delete();
            $this->add('xepan\accounts\Model_Transaction')
                    ->addCondition('related_transaction_id',$editing_transaction_id)
                    ->each(function($m){
                        $m->delete();
                    });
        }
        

        foreach ($data as $transaction) {
            $transactions[] = $new_transaction = $this->add('xepan\accounts\Model_Transaction');
            $new_transaction->createNewTransaction($transaction['type'],null,$transaction['date'],$transaction['narration'],$transaction['currency'],$transaction['exchange_rate'],$transaction['related_id'],$transaction['related_type'],$related_transaction_id, $transaction_template_id = $this->template_id);
            $total_amount[$transaction['type']] = 0;
            foreach ($transaction['rows'] as $code => $row) {
                if(strtolower($row['side'])=='dr'){
                    $new_transaction->addDebitLedger($row['ledger'],$row['amount'],$row['currency'],$row['exchange_rate'],$remark=null,$code);
                    $total_amount[$transaction['type']] += $row['amount']* $row['exchange_rate'];
                }else{
                    $new_transaction->addCreditLedger($row['ledger'],$row['amount'],$row['currency'],$row['exchange_rate'],$remark=null,$code);
                }
            }
            if($total_amount[$transaction['type']] > 0)
                $new_transaction->execute();
            if($related_transaction_id == null)
                $related_transaction_id = $new_transaction->id;
        }       
        $this->hook('afterExecute',[$transactions,$total_amount,$data]);
        return $related_transaction_id;
    }

    function populatePreFilledValues($transaction_model){
        // self tranasction pre load
        $pre_filled_values=[];
        $tr_no=1;
        foreach ($transaction_model->ref('TransactionRows') as $transaction_row) {
            $pre_filled_values[$tr_no][$transaction_row['code']]=[
                                                'ledger'=>$transaction_row['ledger_id'],
                                                'amount'=>$transaction_row['_amountCr']?:$transaction_row['_amountDr'],
                                                'currency'=>$transaction_row['currency_id'],
                                                'exchange_rate'=>$transaction_row['exchange_rate'],
                                            ];
        }

        $related_transactions = $this->add('xepan\accounts\Model_Transaction')
                                    ->addCondition('related_transaction_id',$transaction_model->id)
                                    ->setOrder('id')
                                    ;
        $tr_no++;
        foreach ($related_transactions  as $tr) {
            foreach ($tr->ref('TransactionRows') as $transaction_row) {
                $pre_filled_values[$tr_no][$transaction_row['code']]=[
                                                    'ledger'=>$transaction_row['ledger_id'],
                                                    'amount'=>$transaction_row['_amountCr']?:$transaction_row['_amountDr'],
                                                    'currency'=>$transaction_row['currency_id'],
                                                    'exchange_rate'=>$transaction_row['exchange_rate'],
                                                ];
            }
        }

        return $pre_filled_values;

    }


	function addField($type, $options = null, $caption = null, $attr = null, $spot=null)
    {

        $insert_into = $this->layout ?: $this;

        if (is_object($type) && $type instanceof AbstractView && !($type instanceof Form_Field)) {

            // using callback on a sub-view
            $insert_into = $type;
            list(,$type,$options,$caption,$attr)=func_get_args();

        }

        if ($options === null) {
            $options = $type;
            $type = 'Line';
        }

        if (is_array($options)) {
            $name = isset($options["name"]) ? $options["name"] : null;
        } else {
            $name = $options; // backward compatibility
        }
        $name = preg_replace('|[^a-z0-9-_]|i', '_', $name);

        if ($caption === null) {
            $caption = ucwords(str_replace('_', ' ', $name));
        }

        /* normalzie name and put name back in options array */
        $name = $this->app->normalizeName($name);
        if (is_array($options)){
            $options["name"] = $name;
        } else {
            $options = array('name' => $name);
        }

        switch (strtolower($type)) {
            case 'dropdown':     $class = 'DropDown';     break;
            case 'checkboxlist': $class = 'CheckboxList'; break;
            case 'hidden':       $class = 'Hidden';       break;
            case 'text':         $class = 'Text';         break;
            case 'line':         $class = 'Line';         break;
            case 'upload':       $class = 'Upload';       break;
            case 'radio':        $class = 'Radio';        break;
            case 'checkbox':     $class = 'Checkbox';     break;
            case 'password':     $class = 'Password';     break;
            case 'timepickr':    $class = 'TimePicker';   break;
            default:             $class = $type;
        }
        $class = $this->app->normalizeClassName($class, 'Form_Field');

        if ($insert_into === $this) {
            $template=$this->template->cloneRegion('form_line');
            $field = $this->add($class, $options, null, $template);
        } else {
            if ($insert_into->template->hasTag($name)) {
                $template=$this->template->cloneRegion('field_input');
                $options['show_input_only']=true;
                $field = $insert_into->add($class, $options, $name);
            } else {
                $template=$this->template->cloneRegion('form_line');
                $field = $insert_into->add($class, $options, $spot, $template);
            }

            // Keep Reference, for $form->getElement().
            $this->elements[$options['name']]=$field;
        }


        $field->setCaption($caption);
        $field->setForm($this);
        $field->template->trySet('field_type', strtolower($type));

        if($attr) {
            if($this->app->compat) {
                $field->setAttr($attr);
            }else{
                throw $this->exception('4th argument to addField is obsolete');
            }
        }

        return $field;
    }

    function defaultTemplate(){
        return ['form/stacked'];
    }
}