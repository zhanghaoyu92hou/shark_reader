<?php
namespace app\index\controller;
use think\Controller;
use weixin\wx;
use app\index\model\iClient;
use app\common\model\myCache;
use app\index\model\iMember;
use app\common\model\myValidate;
use app\index\model\iMessage;
use think\Db;
use think\captcha\Captcha;
use app\common\model\myRequest;
class Login extends Controller{
    
    //登录
    public function index(){
        $device_type = iClient::getDeviceType();
        //echo   $device_type ;exit;
        
        $server_url = $_SERVER['HTTP_HOST'];
        $urlData = myCache::getUrlCache($server_url);
        
        $get = myRequest::get();
        if(isset($get['location_key']) && $get['location_key']){
        	$config = cache($get['location_key']);
        	if(!$config){
        		res_return('访问该链接已超时');
        	}
        }else{
        	$config = $urlData;
        }
        
        
        switch($device_type){
            case 1:
                
        //微信环境下如果没有用户信息
        if($device_type == 1 && ($config['appid'] || $config['appsecret'])){
            $this->redirect('User/index');
        }else{
            $tpl = 'pc';
        }
        
        		//$this->redirect('User/index');
        		break;
        	case 2:
        		$tpl = 'pc';
        		break;
        	case 3:
        		$tpl = 'pc';
        		break;
        }
        $code = myCache::createLoginCode();
        $urlData = myCache::getUrlCache();
        $qrcode = self::createLoginQr($code,$urlData);
        $variable = [
        	'code' => $code,
        	'qrcode' => $qrcode,
        	'site_title' =>$urlData['name']
        ];
        $this->assign($variable);
        return $this->fetch($tpl);
    }
    
    //手机号登陆
    public function doLogin(){
    	$rules = [
    		'phone' =>  ["require|mobile",['require'=>'请输入手机号','mobile'=>'请输入正确格式的手机号']],
    		'code' => ['require|number|length:6',['require'=>'请输入验证码','number'=>'验证码格式错误','length'=>'验证码为6位数字']]
    	];
    	$data = myValidate::getData($rules, 'phone,code');
    	iMessage::check($data['phone'], $data['code']);
        //判断是否注册
        $re = Db::name('Member')->where('phone','=',$data['phone'])->value('id');
        if($re){
            //登录
            iMember::phoneLogin($data['phone']);
        }else{
            $this->phoneRegiest($data['phone']);
        }
    }

    //手机注册
    public function phoneRegiest($phone){
        $data = array(
            'channel_id' => 0,
            'agent_id' => 0,
            'wx_id' => 0,
            'headimgurl' => '/static/templet/default/headimg.jpeg',
            'spread_id' => 0,
            'nickname' => $phone,
            'sex' => 0,
            'create_time' => time(),
            'phone'=>$phone,
            'money'=>60
        );
        $re = Db::name('Member')->insertGetId($data);
        if($re){
            if($data['spread_id']){
                Db::name('Spread')->where('id','=',$data['spread_id'])->setInc('visitor_num');
            }
            $member = Db::name('Member')->where('phone','=',$phone)->field('id')->find();
            session('INDEX_LOGIN_ID',$member['id']);
            res_return(['flag'=>22]);
        }else{
            res_return('用户创建失败');
        }
    }
     public function PcLogin(){
     	    	$post = myRequest::post('username,password');
     	      	if(empty($post['username']) || empty($post['password'])){
    		       res_return('登录失败');
             	}
               $inp_password = createPwd($post['password']);
               $data=array('username'=>$post['username'],'password'=>$inp_password );
            $info=   Db::name('Member')->where( $data)->field('id')->find(); 
           if(!$info){
         		res_return('用户名不存在或者密码不正确');
           }else{
           	     session('INDEX_LOGIN_ID',$info['id']);
                 $url = my_url('user/index');  
                 res_return(['url'=>$url,'code'=>1]);
           }
     }
        //用户注册
    public function Regiest(){
    	$post = myRequest::post('username,password,code');
    	if(empty($post['username']) || empty($post['password']) || empty($post['code'])){
    		  res_return('请输入完整注册参数');
    	}
    	 $captcha = new Captcha();
            if(!$captcha->check($post['code'])){
            	res_return('验证码输入错误');
         }
         //检查用户是否存在
         $info=   Db::name('Member')->where('username','=',$post['username'])->find(); 
         if($info){
         		res_return('用户名已存在，请重新选择');
         }
       $inp_password = createPwd($post['password']);
       
       $server_url = $_SERVER['HTTP_HOST'];
       $cinfo = Db::name('channel')->where(['url'=>$server_url])->find();
       $channel_id = 0;
       $agent_id = 0;
       
       if(!empty($cinfo)){
           $type =  $cinfo['type'];
           $parent_id = $cinfo['parent_id'];
           $pcinfo = Db::name('channel')->where(['id'=>$parent_id])->find();
           if($type ==1 ){
                $channel_id = $cinfo['id'];
           }
           if($type == 2 && !empty($pcinfo)){
               $channel_id = $cinfo['parent_id'];
               $agent_id = $cinfo['id'];
           }
       }
       
       
	//	res_return($inp_password);
        $data = array(
            'channel_id' => $channel_id,
            'agent_id' => $agent_id,
            'wx_id' => 0,
            'username'=>$post['username'],
            'headimgurl' => '/static/templet/default/headimg.jpeg',
            'spread_id' => 0,
            'nickname' => $post['username'],
            'password'=>  $inp_password ,
            'sex' => 0,
            'create_time' => time(),
            'money'=>0
        );
        
        // echo '<pre>';
        // print_r($data);
        // exit;
        $re = Db::name('Member')->insertGetId($data);
        if($re){
            if($data['spread_id']){
                Db::name('Spread')->where('id','=',$data['spread_id'])->setInc('visitor_num');
            }
            $member = Db::name('Member')->where('username','=',$post['username'])->field('id')->find();
            
            session('INDEX_LOGIN_ID',$member['id']);
         
            $url = my_url('user/index');  
            res_return(['url'=>$url,'code'=>1]);
        }else{
            res_return('用户创建失败');
        }
    }
    //创建登陆二维码
    private function createLoginQr($code,$config){
    	wx::$config = $config;
    	$qr_url = wx::createTmpQrcode($code);
    	return $qr_url;
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
    //检查登陆情况
    public function checkQrLogin(){
    	$rules = ['code' => ['require|alphaNum',['require'=>'参数异常','alphaNum'=>'参数格式不规范']]];
    	$code = myValidate::getData($rules,'code');
    	$data = cache($code);
    	if($data){
    		if(is_numeric($data)){
    			iMember::saveLogin($data);
    			cache($code,null);
    			res_return(['url'=>my_url('User/index')]);
    		}else{
    			res_return(['url'=>0]);
    		}
    	}else{
    		res_return('二维码已过期');
    	}
    }
}