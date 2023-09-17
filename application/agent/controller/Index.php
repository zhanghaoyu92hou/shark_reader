<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myValidate;
use app\agent\model\aLogin;
use app\agent\model\aIndex;
use app\agent\model\aAgent;

class Index extends Common
{
    
    //后台首页
    public function index()
    {
        $cur = aLogin::getCache();
        aLogin::createMenu();
        $menu = aLogin::getMenu();
        $variable = [
            'menu' => $menu,
            'cur' => $cur,
            'site_name' => $cur['name']
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //控制台
    public function console(){
        $number = aIndex::getNumbersData();
        $charge_rank = aIndex::getChangeRank();
        $variable = [
            'number' => $number,
            'charge_rank' => $charge_rank,
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //检查是否有未读公告
    public function getReadMessage(){
        $res = aIndex::checkMessage();
        if($res['id'] > 0){
            $res['url'] = my_url('Message/showInfo',['id'=>$res['id']]);
        }
        res_return($res);
    }
    
    //获取用户趋势图
    public function getUserChartData(){
        $data = aIndex::getUserChartData();
        res_return($data);
    }
    
    
    //基础信息
    public function userinfo(){
        global $loginId;
        if($this->request->isAjax()){
            $name = myValidate::getData(aAgent::$rules, 'name');
            $re = aAgent::setField('Channel',[['id','=',$loginId]], 'name', $name);
            if($re){
                cache('AGENT_USER_'.$loginId,null);
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = aAgent::getById('Channel',$loginId,'id,name,login_name,status');
            $login_msg = aLogin::getLastLoginMsg($cur['login_name']);
            if($login_msg){
                $cur = array_merge($cur,$login_msg);
            }
            $this->assign('cur',$cur);
            return $this->fetch();
        }
    }
    
    //修改密码
    public function password(){
        if($this->request->isAjax()){
            global $loginId;
            $data = myValidate::getData(aLogin::$rules, 'old_pwd,new_pwd,re_pwd');
            $cur = aIndex::getById('Channel', $loginId,'id,password');
            if(createPwd($data['old_pwd']) !== $cur['password']){
                res_return('原密码输入不正确');
            }
            $password = createPwd($data['new_pwd']);
            $re = aIndex::setField('Channel',[['id','=',$loginId]],'password', $password);
            if($re){
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            
            return $this->fetch();
        }
    }
    
    //退出登录
    public function logOut(){
        aLogin::clearCache();
        $url = my_url('Login/index');
        echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
        exit;
    }

}
