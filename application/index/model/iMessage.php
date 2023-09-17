<?php
namespace app\index\model;
use app\index\model\Common;
use app\common\model\myCache;
use AlibabaCloud\Client\AlibabaCloud;
use AlibabaCloud\Client\Request\Request;
use other\myHttp;


class iMessage extends Common{
    
    //发送短信
    public static function send($phone){
        self::saiyouSend($phone);
    }
    
    private static function aliSend($phone){
    	$key = $phone.'_code';
    	$code = cache($key);
    	if(!$code){
    		$code = mt_rand(100000,999999);
    	}
    	$config = myCache::getMessageCache();
    	if(!$config){
    		res_return('您尚未配置短信参数');
    	}
    	$options = [
    			'query'=>[
    					'PhoneNumbers' => $phone,
    					'SignName' => $config['signName'],
    					'TemplateCode' => $config['templateCode'],
    					'TemplateParam' => '{"code":"'.$code.'"}'
    			]
    	];
    	AlibabaCloud::accessKeyClient($config['accessKey'],$config['secretKey'])->regionId('cn-hangzhou')->asDefaultClient();
    	$result = AlibabaCloud::rpc()
    	->product('Dysmsapi')
    	->version('2017-05-25')
    	->action('SendSms')
    	->method('POST')
    	->options($options)
    	->request();
    	$res = $result->toArray();
    	if(is_array($res)){
    		if(isset($res['Code']) && $res['Code'] === 'OK'){
    			cache($key,$code,120);
    			res_return();
    		}else{
    			res_return($res['Message']);
    		}
    	}else{
    		res_return('发送失败');
    	}
    }
    
    //赛邮短信发送
    public static function saiyouSend($phone){
    	$key = $phone.'_code';
    	$code = cache($key);
    	if(!$code){
    		$code = mt_rand(100000,999999);
    	}
    	$config = myCache::getMessageCache();
    	if(!$config){
    		res_return('您尚未配置短信参数');
    	}
    	$url = 'https://api.mysubmail.com/message/send';
    	$data = [
    		'appid' => $config['appid'],
    		'to' => $phone,
    		'content' => '【'.$config['sign'].'】'.str_replace('CODE', $code, $config['content']),
    		'timestamp' => time(),
    		'sign_type' => 'normal',
    		'signature' => $config['appkey']
    	];
    	$re = myHttp::postData($url,$data);
    	if(isset($re['status']) && $re['status'] === 'success'){
    		cache($key,$code,120);
    		res_return('ok');
    	}else{
    		res_return('发送失败');
    	}
    }
    
    //检测验证码是否正确
    public static function check($phone,$code){
        $key = $phone.'_code';
        $cache_code = cache($key);
        if(!$cache_code){
            res_return('验证码已过期');
        }
        if($cache_code != $code){
            res_return('验证码不正确');
        }
        cache($key,null);
        return true;
    }
}