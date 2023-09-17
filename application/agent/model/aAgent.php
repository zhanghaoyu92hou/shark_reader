<?php
namespace app\agent\model;
use app\agent\model\Common;
use app\common\model\myValidate;
use app\common\model\myCache;

class aAgent extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:20",["require"=>"请输入代理名称",'max'=>'代理名称最多支持20个字符']],
    	'login_name' => ["require|alphaDash|length:5,12",["require"=>"请输入登陆账户名","alphaDash"=>'登陆账户名必须是英文、数字、下划线和破折号',"length"=>"请输入5至12位符合规范的登陆账户名"]],
        'password' => ["require|length:6,16",["require"=>"请输入登陆密码","length"=>"请输入6-16位登陆密码"]],
        'url' =>  ["require|max:50",["require"=>"请输入微信绑定域名",'url'=>'绑定域名长度超出限制']],
        'is_location' => ["require|in:1,2",["require"=>"请选择是否开启域名跳转","in"=>"未指定该域名跳转状态"]],
        'location_url' => ["max:50|requireIf:is_location,1",["requireIf"=>"请输入跳转域名","max"=>"跳转域名长度超出限制"]],
        'status' => ["require|in:1,2",["require"=>"请选择渠道状态","in"=>"未指定该渠道状态"]],
        'appid' => ['require',['require'=>'请输入公众号appid']],
        'appsecret' => ['require',['require'=>'请输入公众号secret']],
        'qrcode' => ['require',['require'=>'请上传公众号二维码图片']],
        'deduct_min' => ["number",["number"=>"请输入正确格式的距扣量数"]],
        'deduct_num' => ["number",["number"=>"请输入正确格式的扣量数"]],
        'ratio' => ["number|between:0,100",["number"=>"请输入正确格式的返额比例",'between'=>'请输入0-100区间的返额比例']],
        'wefare_days' => ["number",["number"=>"请输入正确格式的代理福利时长"]],
        'pay_type' => ["require|in:1,2,3",['require'=>'请选择支付方式',"in"=>"请选择指定的支付方式"]],
        'bank_user' => ["max:20",['max'=>'开户人姓名长度超出限制']],
        'bank_name' => ["max:20",['max'=>'开户银行长度超出限制']],
        'bank_no' => ["number|min:16",['max'=>'银行卡号长度超出限制']],
        'event' => ["require|in:on,off,delete,resetpwd",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ]; 
    
    
    //保存代理信息
    public static function doneAgent($field){
        $data = myValidate::getData(self::$rules, $field);
        $key = '';
        $data['location_url'] = '';
        if(array_key_exists('id', $data)){
            $cur = parent::getById('Channel',$data['id'],'id,url,location_url');
            if(!$cur){
                res_return('代理信息异常');
            }
            $key = 'channel_info_'.$data['id'];
            $re = parent::saveIdData('Channel', $data);
        }else{
            if($data['url']){
                self::checkUrlRepeat($data['url']);
            }
            global $loginId;
            $data['type'] = 2;
            $data['is_wx'] = 2;
            $data['status'] = 0;
            $data['parent_id'] = $loginId;
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
    private static function checkUrlRepeat($url,$id=0,$is_site=false){
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