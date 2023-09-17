<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mLogin;
use app\common\model\myValidate;
use app\admin\model\mUser;
use app\admin\model\mIndex;
use app\common\model\myCache;

class Index extends Common
{
    
    //后台首页
    public function index()
    {
        $siteMsg = myCache::getWebSiteCache();
//        echo '<pre>';
//        print_r($siteMsg);exit;
        $site_name = '后台管理';
        if($siteMsg && isset($siteMsg['name']) && $siteMsg['name']){
            $site_name = $siteMsg['name'];
        }
        $cur = mLogin::getCache();
        $menu = mLogin::getMenu();
        $variable = [
            'menu' => $menu,
            'cur' => $cur,
            'site_name' => $site_name
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //控制台
    public function console(){
        $number = mIndex::getNumbersData();
        $charge_rank = mIndex::getChangeRank();
        $complaint_rank = mIndex::getComplaintRank();
        $feedback = mIndex::getFeedBackList();
        $variable = [
            'number' => $number,
            'charge_rank' => $charge_rank,
            'complaint_rank' => $complaint_rank,
            'feedback' => $feedback
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //获取用户趋势图
    public function getUserChartData(){
        $data = mIndex::getUserChartData();
        res_return($data);
    }
    
    //修改个人信息
    public function userinfo(){
        global $loginId;
        if($this->request->isAjax()){
            $name = myValidate::getData(mUser::$rules, 'name');
            $re = mIndex::setField('Manage',[['id','=',$loginId]], 'name', $name);
            if($re){
                cache('ADMIN_USER_'.$loginId,null);
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mIndex::getById('Manage',$loginId);
            $login_msg = mLogin::getLastLoginMsg($cur['login_name']);
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
            $data = myValidate::getData(mLogin::$rules, 'old_pwd,new_pwd,re_pwd');
            $cur = mIndex::getById('Manage', $loginId,'id,password');
            $old_pwd = createPwd($data['old_pwd']);
            if($old_pwd !== $cur['password']){
                res_return('原密码输入不正确');
            }
            $password = createPwd($data['new_pwd']);
            $re = mIndex::setField('Manage',[['id','=',$loginId]], 'password', $password);
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
        mLogin::clearCache();
        $url = my_url('Login/index');
        echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
        exit;
    }

}
