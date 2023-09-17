<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\agent\model\aWithdraw;
use app\agent\model\aAgent;

class Withdraw extends Common{
    
    //我的结算
    public function index(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['channel_id','=',$loginId],['status','between',[0,2]]],
                'eq' => 'status:status'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aWithdraw::getPageList('Withdraw', $where, '*', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = aWithdraw::getStatusName($v['status']);
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            $cur = aWithdraw::getCountData();
            $this->assign('cur',$cur);
            return $this->fetch();
        }
    }
    
    //设置账号
    public function setAccount(){
        global $loginId;
        if($this->request->isAjax()){
            $field = 'bank_user,bank_no,bank_name';
            $data = myValidate::getData(aAgent::$rules, $field);
            $re = aAgent::save('Channel',[['id','=',$loginId]], $data);
            if($re){
                res_return();
            }else{
                res_return('设置失败');
            }
        }else{
            $cur = aWithdraw::getById('Channel',$loginId,'id,bank_user,bank_name,bank_no');
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('setAccount');
        }
    }
    
    //一键提现
    public function doAll(){
        $res = aWithdraw::doneAll();
        if($res){
            res_return();
        }else{
            res_return('提现失败，请重试');
        }
    }
}
