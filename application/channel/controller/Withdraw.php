<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\channel\model\cWithdraw;
use app\channel\model\cAgent;

class Withdraw extends Common{
    
    //提现申请列表
    public function index(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['a.to_channel_id','=',$loginId],['a.status','between',[0,2]]],
                'eq' => 'status:a.status'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cWithdraw::getAgentList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //处理提现申请
    public function doWithdraw(){
        $field = 'id,event,remark';
        $data = myValidate::getData(cWithdraw::$rules,$field);
        $re = cWithdraw::doWithdraw($data);
        if($re){
            res_return();
        }else{
            res_return('操作失败，请重试');
        }
    }
    
    //我的结算
    public function mine(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['channel_id','=',$loginId],['status','between',[0,2]]],
                'eq' => 'status:status'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cWithdraw::getPageList('Withdraw', $where, '*', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = cWithdraw::getStatusName($v['status']);
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            $cur = cWithdraw::getCountData();
            $this->assign('cur',$cur);
            return $this->fetch();
        }
    }
    
    //设置账号
    public function setAccount(){
        global $loginId;
        if($this->request->isAjax()){
            $field = 'bank_user,bank_no,bank_name';
            $data = myValidate::getData(cAgent::$rules, $field);
            $re = cAgent::save('Channel',[['id','=',$loginId]], $data);
            if($re){
                res_return();
            }else{
                res_return('设置失败');
            }
        }else{
            $cur = cWithdraw::getById('Channel',$loginId,'id,bank_user,bank_name,bank_no');
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('mine')
            ];
            $this->assign($variable);
            return $this->fetch('setAccount');
        }
    }
    
    //一键提现
    public function doAll(){
        $res = cWithdraw::doneAll();
        if($res){
            res_return();
        }else{
            res_return('提现失败，请重试');
        }
    }
}
