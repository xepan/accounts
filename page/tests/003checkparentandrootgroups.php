<?php
namespace xepan\accounts;

class page_tests_003checkparentandrootgroups extends \xepan\base\Page_Tester {
    public $title = 'Parent & Root Group Tests';
    public $groups;
    public $proper_responses=[
    '-'=>'-'
    ];

    function init(){
        $this->add('xepan\accounts\page_tests_init');
        $this->createGroups();
        parent::init();
    }

    function createGroups(){
        $group=[];

        $group[1] = $this->add('xepan\accounts\Model_Group');
        $group[1]['name'] = 'A Top Root';
        $group[1]['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->tryLoadAny()->get('id');
        $group[1]['parent_group_id'] =null;
        $group[1]['created_at'] ="2016-04-21";
        $group[1]->save();

        $group[2] = $this->add('xepan\accounts\Model_Group');
        $group[2]['name'] = 'B Under A';
        $group[2]['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->tryLoadAny()->get('id');
        $group[2]['parent_group_id'] =$group[1]->id;
        $group[2]['created_at'] ="2016-04-21";
        $group[2]->save();

        $group[3] = $this->add('xepan\accounts\Model_Group');
        $group[3]['name'] = 'C Under B';
        $group[3]['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->tryLoadAny()->get('id');
        $group[3]['parent_group_id'] =$group[2]->id;
        $group[3]['created_at'] ="2016-04-21";
        $group[3]->save();

        $group[4] = $this->add('xepan\accounts\Model_Group');
        $group[4]['name'] = 'D Under B';
        $group[4]['balance_sheet_id'] = $this->add('xepan\accounts\Model_BalanceSheet')->tryLoadAny()->get('id');
        $group[4]['parent_group_id'] =$group[2]->id;
        $group[4]['created_at'] ="2016-04-21";
        $group[4]->save();

        $this->groups = $group;

        return $this;
    }

    function prepare_GroupA(){
        $this->proper_responses['test_GroupA']=[
            'name'=>'A Top Root',
            'balance_sheet_id'=>null,
            'created_at'=>$this->app->today,
            'parent_group_id'=>null,
            'root_group_id'=>$this->groups[1]->id
        ];
    }

    function test_GroupA(){
        $this->group = $this->add('xepan\accounts\Model_Group')
                                    ->loadBy('name','A Top Root');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_GroupA'] as $field => $value) {
            $result[$field] = $this->group[$field];            
        }
        return $result;   
    }

    function prepare_GroupB(){

        $this->proper_responses['test_GroupB']=[
            'name'=>'B Under A',
            'balance_sheet_id'=>$this->groups[2]['balance_sheet_id'],
            'created_at'=>$this->app->today,
            'parent_group_id'=>$this->groups[1]->id,
            'root_group_id'=>$this->groups[1]->id
        ];
    }

    function test_GroupB(){
        $this->group = $this->add('xepan\accounts\Model_Group')
                                    ->loadBy('name','B Under A');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_GroupB'] as $field => $value) {
            $result[$field] = $this->group[$field];            
        }
        return $result;   
    }

    function prepare_GroupC(){
        $this->proper_responses['test_GroupC']=[
            'name'=>'C Under B',
            'balance_sheet_id'=>null,
            'created_at'=>$this->app->today,
            'parent_group_id'=>$this->groups[2]->id,
            'root_group_id'=>$this->groups[1]->id
        ];
    }

    function test_GroupC(){
        $this->group = $this->add('xepan\accounts\Model_Group')
                                    ->loadBy('name','C Under B');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_GroupC'] as $field => $value) {
            $result[$field] = $this->group[$field];            
        }
        return $result;   
    }

    function prepare_GroupD(){
        $this->proper_responses['test_GroupD']=[
            'name'=>'D Under B',
            'balance_sheet_id'=>null,
            'created_at'=>$this->app->today,
            'parent_group_id'=>$this->groups[2]->id,
            'root_group_id'=>$this->groups[1]->id
        ];
    }

    function test_GroupD(){
        $this->group = $this->add('xepan\accounts\Model_Group')
                                    ->loadBy('name','D Under B');                                                             
        $result=[];
        foreach ($this->proper_responses
            ['test_GroupD'] as $field => $value) {
            $result[$field] = $this->group[$field];            
        }
        return $result;   
    }


}