<?php
namespace app\admin\model;
use app\admin\model\Common;
use app\common\model\myValidate;
use app\common\model\myCache;

class mPlatform extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:20",["require"=>"请输入代理名称",'max'=>'代理名称最多支持20个字符']],
        'login_name' => ["require|alphaDash|length:5,12",["require"=>"请输入登陆账户名","alphaDash"=>'登陆账户名必须是英文、数字、下划线和破折号',"length"=>"请输入5至12位符合规范的登陆账户名"]],
        'password' => ["require|length:6,16",["require"=>"请输入登陆密码","length"=>"请输入6-16位登陆密码"]],
        'url' =>  ["max:50",['url'=>'绑定域名长度超出限制']],
        'is_location' => ["require|in:1,2",["require"=>"请选择是否开启域名跳转","in"=>"未指定该域名跳转状态"]],
        'location_url' => ["max:50|requireIf:is_location,1",["requireIf"=>"请输入跳转域名","max"=>"跳转域名长度超出限制"]],
        'status' => ["require|in:1,2",["require"=>"请选择渠道状态","in"=>"未指定该渠道状态"]],
        'appid' => ['alphaNum|max:32',['alphaNum'=>'公众号appid格式不规范','max'=>'公众号appid长度超出限制']],
        'appsecret' => ['alphaNum|max:64',['alphaNum'=>'公众号secret格式不规范','max'=>'公众号secret长度超出限制']],
        'apptoken' => ['alphaNum|max:32',['alphaNum'=>'公众号token格式不规范','max'=>'公众号token长度超出限制']],
        'qrcode' => ['url',['url'=>'公众号二维码图片格式不规范']],
        'deduct_min' => ["number",["number"=>"请输入正确格式的距扣量数"]],
        'deduct_num' => ["number",["number"=>"请输入正确格式的扣量数"]],
        'ratio' => ["number|between:0,100",["number"=>"请输入正确格式的返额比例",'between'=>'请输入0-100区间的返额比例']],
        'wefare_days' => ["number",["number"=>"请输入正确格式的代理福利时长"]],
        'pay_type' => ["require|in:1,2,3",['require'=>'请选择支付方式',"in"=>"请选择指定的支付方式"]],
        'bank_user' => ["max:20",['max'=>'开户人姓名长度超出限制']],
        'bank_name' => ["max:20",['max'=>'开户银行长度超出限制']],
        'bank_no' => ["max:100",['max'=>'账号长度超出限制']],
        'event' => ["require|in:on,off,delete,resetpwd,pass,fail",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ]; 
    
    //获取渠道选项
    public static function getChannelOptions(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [
                    ['val'=>1,'text'=>'启用','default'=>1],
                    ['val'=>2,'text'=>'禁用','default'=>0]
                ]
            ],
            'is_location' => [
                'name' => 'is_location',
                'option' => [
                    ['val'=>1,'text'=>'开启','default'=>0],
                    ['val'=>2,'text'=>'关闭','default'=>1]
                ]
            ],
        ];
        return $option;
    }
    
    //保存渠道信息
    public static function doneChannel($field){
        $data = myValidate::getData(self::$rules, $field);
        $key = '';
        if($data['url'] && $data['url'] == $data['location_url']){
            res_return('绑定域名和跳转域名不能一致');
        }
        if(array_key_exists('id',$data)){
        	if($data['url']){
        		self::checkUrlRepeat($data['url'],$data['id']);
        	}
            if($data['location_url']){
                self::checkUrlRepeat($data['location_url'],$data['id']);
            }
            $cur = parent::getById('Channel',$data['id'],'id,url,location_url');
            if(!$cur){
                res_return('渠道信息异常');
            }
            $key = 'channel_info_'.$data['id'];
            $re = parent::saveIdData('Channel', $data);
        }else{
        	if($data['url']){
        		self::checkUrlRepeat($data['url']);
        	}
            if($data['location_url']){
                self::checkUrlRepeat($data['location_url']);
            }
            $data['type'] = 1;
            $data['is_wx'] = 1;
            $data['password'] = createPwd($data['password']);
            $data['create_time'] = time();
            $re = parent::add('Channel', $data);
        }
        if($re){
            if($key){
                cache($key,null);
                if($cur['url']){
                	cache(md5($cur['url']),null);
                }
                if($cur['location_url']){
                	cache(md5($cur['location_url']),null);
                }
            }
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //检查域名是否重复
    public static function checkUrlRepeat($url,$id=0,$is_site=false){
        $where = [['url|location_url','=',$url]];
        if($id){
            $where[] = ['id','<>',$id];
        }
        $repeat = parent::getCur('Channel', $where,'id,name');
        if($repeat){
            res_return('该域名已占用，请更换');
        }
        if(!$is_site){
            $site = myCache::getWebSiteCache();
            if($url == $site['location_url'] || $url == $site['url']){
                res_return('该域名已占用，请更换');
            }
        }
    }
    
    //获取更新代理选项
    public static function getAgentOptions(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [
                    ['val'=>1,'text'=>'启用','default'=>1],
                    ['val'=>2,'text'=>'禁用','default'=>0]
                ]
            ]
        ];
        return $option;
    }
    
    //保存代理信息
    public static function doneAgent($field){
        $data = myValidate::getData(self::$rules, $field);
        $data['location_url'] = '';
        $key = '';
        if(array_key_exists('id', $data)){
        	if($data['url']){
        		self::checkUrlRepeat($data['url'],$data['id']);
        	}
            $cur = parent::getById('Channel',$data['id'],'id,url');
            if(!$cur){
                res_return('代理信息异常');
            }
            $key = 'channel_info_'.$data['id'];
            $re = parent::saveIdData('Channel', $data);
        }else{
        	if($data['url']){
        		self::checkUrlRepeat($data['url']);
        	}
            $data['type'] = 1;
            $data['is_wx'] = 2;
            $data['password'] = createPwd($data['password']);
            $data['create_time'] = time();
            $re = mPlatform::add('Channel', $data);
        }
        if($re){
            if($key){
                cache($key,null);
                if($cur['url']){
                    cache(md5($cur['url']),null);
                }
            }
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //跳转代理后台
    public static function intoBackstage($channel_id){
        $cur = parent::getById('Channel', $channel_id);
        if(!$cur){
            res_return('代理参数异常');
        }
        $url = '/channel';
        $key = 'CHANNEL_LOGIN_ID';
        if($cur['is_wx'] == 2){
            $key = 'AGENT_LOGIN_ID';
            $url = '/agent';
        }
        session($key,$cur['id']);
        return $url;
    }
    
    /**
     * 获取代理状态名称
     * @param number $status 状态值
     * @return string
     */
    public static function getStatusName($status){
        $name = '未知';
        switch ($status){
            case 0:
                $name = '待审核';
                break;
            case 1:
                $name = '正常';
                break;
            case 2:
                $name = '禁用';
                break;
            case 3:
                $name = '审核不通过';
                break;
        }
        return $name;
    }
}