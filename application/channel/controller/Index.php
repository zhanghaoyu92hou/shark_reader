<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myValidate;
use app\channel\model\cLogin;
use app\channel\model\cIndex;
use app\channel\model\cAgent;

class Index extends Common
{
    
    //后台首页
    public function index()
    {
        $cur = cLogin::getCache();
        cLogin::createMenu();
        $menu = cLogin::getMenu();
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
        $number = cIndex::getNumbersData();
        $charge_rank = cIndex::getChangeRank();
        $complaint_rank = cIndex::getComplaintRank();
        $feedback = cIndex::getFeedBackList();
        $variable = [
            'number' => $number,
            'charge_rank' => $charge_rank,
            'complaint_rank' => $complaint_rank,
            'feedback' => $feedback
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //检查是否有未读公告
    public function getReadMessage(){
        $res = cIndex::checkMessage();
        if($res['id'] > 0){
            $res['url'] = my_url('Message/showInfo',['id'=>$res['id']]);
        }
        res_return($res);
    }
    
    //获取用户趋势图
    public function getUserChartData(){
        $data = cIndex::getUserChartData();
        res_return($data);
    }
    
    
    //基础信息
    public function userinfo(){
        global $loginId;
        if($this->request->isAjax()){
            $name = myValidate::getData(cAgent::$rules, 'name');
            $re = cAgent::setField('Channel',[['id','=',$loginId]], 'name', $name);
            if($re){
                $cur = cLogin::getCache();
                $key = 'channel_info_'.$cur['id'];
                cache($key,null);
                if($cur['url']){
                    cache(md5($cur['url']),null);
                }
                if($cur['location_url']){
                    cache(md5($cur['location_url']),null);
                }
                cache('CHANNEL_USER_'.$loginId,null);
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = cAgent::getById('Channel',$loginId,'id,name,login_name,status');
            $login_msg = cLogin::getLastLoginMsg($cur['login_name']);
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
            $data = myValidate::getData(cLogin::$rules, 'old_pwd,new_pwd,re_pwd');
            $cur = cIndex::getById('Channel', $loginId,'id,password');
            if(createPwd($data['old_pwd']) !== $cur['password']){
                res_return('原密码输入不正确');
            }
            $password = createPwd($data['new_pwd']);
            $re = cIndex::setField('Channel',[['id','=',$loginId]],'password', $password);
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
        cLogin::clearCache();
        $url = my_url('Login/index');
        echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
        exit;
    }

}
