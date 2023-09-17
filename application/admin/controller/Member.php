<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mMember;
use app\common\model\myValidate;
use app\admin\model\mComplaint;
use app\admin\model\mFeedback;

class Member extends Common{
    
    //用户列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]]],
                'eq' => 'status:a.status,subscribe:a.subscribe',
                'like' => 'keyword:a.nickname'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mMember::getMemberList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //设置用户书币信息
    public function doMemberMoney(){
        $field = 'id,event';
        $data = myValidate::getData(mMember::$rules, $field);
        $key = 'status';
        switch ($data['event']){
            case 'charge':
                $money = myValidate::getData(mMember::$rules, 'money');
                if($money == 0){
                    res_return('用户书币未变动');
                }
                if($money < 0){
                    $cur = mMember::getById('Member',$data['id'],'id,money');
                    if(!$cur){
                        res_return('用户数据异常');
                    }
                    if($cur['money'] <= 0){
                        res_return('不能设置此调整书币金额');
                    }
                    $abs_money = abs($money);
                    if($abs_money > $cur['money']){
                        $money = '-'.$cur['money'];
                    }
                }
                $re = mMember::setMemberMoney($data['id'], $money);
                break;
            case 'vipon':
                $month = myValidate::getData(mMember::$rules, 'month');
                $time = time()+$month*30*86400;
                $re = mMember::setField('Member', [['id','=',$data['id']]],'viptime',$time);
                break;
            case 'vipoff':
                $re = mMember::setField('Member', [['id','=',$data['id']]],'viptime',0);
                break;
            default:
                res_return('按钮绑定事件错误');
                break;
        }
        if($re){
            $key = 'member_info_'.$data['id'];
            cache($key,null);
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //设置用户账户状态
    public function doMemberState(){
        $field = 'id,event';
        $data = myValidate::getData(mMember::$rules, $field);
        $key = 'status';
        switch ($data['event']){
            case 'statuson':
                $value = 1;
                break;
            case 'statusoff':
                $value = 2;
                break;
            default:
                res_return('按钮绑定事件错误');
                break;
        }
        $re = mMember::setField('Member', [['id','=',$data['id']]],$key, $value);
        if($re){
            $key = 'member_info_'.$data['id'];
            cache($key,null);
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //用户详情
    public function info(){
        $id = myRequest::getId('用户');
        $cur = mMember::getById('Member',$id,'id,nickname,headimgurl,money,total_money,subscribe,viptime,create_time');
        if(empty($cur)){
            res_return('用户信息异常');
        }
        $count = mMember::getMemberCountMsg($id);
        $cur['charge_money'] = $count['charge'];
        $cur['consume_money'] = $count['consume'];
        $variable = [
            'cur' => $cur,
            'url' => [
                'charge' => my_url('getRecordList',['uid'=>$id,'type'=>1]),
                'activity' => my_url('getRecordList',['uid'=>$id,'type'=>2]),
                'reward' => my_url('getRecordList',['uid'=>$id,'type'=>3]),
                'sign' => my_url('getRecordList',['uid'=>$id,'type'=>4]),
                'consume' => my_url('getRecordList',['uid'=>$id,'type'=>5])
            ]
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //获取各种记录列表
    public function getRecordList(){
        $get = myRequest::get('type');
        $pages = myRequest::getPageParams();
        switch ($get['type']){
            case 1:
                $config = [
                    'default' => [['type','=',1]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = mMember::getOrderList($where,$pages);
                break;
            case 2:
                $config = [
                    'default' => [['type','=',2]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = mMember::getOrderList($where,$pages);
                break;
            case 3:
                $config = [
                    'default' => [['type','=',3]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = mMember::getOrderList($where,$pages);
                break;
            case 4:
                $config = ['eq' => 'uid'];
                $where = mySearch::getWhere($config);
                $res = mMember::getSignList($where,$pages);
                break;
            case 5:
                $config = ['eq' => 'uid'];
                $where = mySearch::getWhere($config);
                $res = mMember::getConsumeList($where,$pages);
                break;
            default:
                res_return('请求数据异常');
                break;
        }
        res_return('ok',$res['data'],$res['count']);
    }
    
    //用户投诉列表
    public function complaint(){
        if($this->request->isAjax()){
            $config = [
                'default' => [],
                'eq' => 'keyword:a.uid',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mComplaint::getComplaintList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //用户反馈列表
    public function feedback(){
        if($this->request->isAjax()){
            $config = [
                'default' => [],
                'eq' => 'keyword:uid',
                'between' => 'between_time:create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mFeedback::getFeedbackList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //处理反馈
    public function doFeedback(){
    	mFeedback::doFeedback();
    }
    
}