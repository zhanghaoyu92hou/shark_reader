<?php
namespace app\channel\controller;

use think\Controller;
use app\common\model\myValidate;
use app\channel\model\cLogin;
use think\captcha\Captcha;

class Login extends Controller{
    
    //登录页面
    public function index(){
        if($this->request->isAjax()){
            $field = 'login_name,password,verify_code';
            $data = myValidate::getData(cLogin::$rules, $field);
            $captcha = new Captcha();
            if(!$captcha->check($data['verify_code'])){
            	res_return('验证码输入错误');
            }
            cLogin::checkLogin($data['login_name'], $data['password']);
            $url = my_url('Index/index');
            res_return(['url'=>$url]);
        }else{
            return $this->fetch();
        }
    }
    
    //验证码
    public function verify(){
    	$config =    [
    			'fontSize'    =>    30,
    			'length'      =>    4,
    			'codeSet' 	  => 	'ABCDEFGHIJKLMNPQRSTUVWXYZ123456789'
    	];
    	$captcha = new Captcha($config);
    	return $captcha->entry();
    }
}