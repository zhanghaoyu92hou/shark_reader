<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
class mConfig extends Common{
    
    //站点信息配置
    public static $rules = [
        'name' =>  ["require|max:20",["require"=>"请输入站点名称",'max'=>'站点名称最多支持20个字符']],
        'url' =>  ["require|max:50",["require"=>"请输入安全域名",'url'=>'安全域名长度超出限制']],
        'is_location' => ["require|in:1,2",["require"=>"请选择是否开启跳转域名","in"=>"未指定该跳转状态"]],
        'is_sign' => ["require|in:1,2",["require"=>"请选择是否开启签到","in"=>"未指定该签到状态"]],
        'pay_type' => ["require|in:1,2,3,4,5",["require"=>"请选择支付方式","in"=>"未指定该支付方式"]],
        'share_money' => ["number",["number"=>"请输入数值类型的分享奖励"]],
        'sign_day1' => ["number|requireIf:is_sign,1",["number"=>"第一天签到奖励格式不规范",'requireIf'=>'请输入第一天签到奖励','gt'=>'第一天签到奖励格式不规范']],
        'sign_day2' => ["number|requireIf:is_sign,1",["number"=>"第二天签到奖励格式不规范"],'requireIf'=>'请输入第二天签到奖励','gt'=>'第二天签到奖励格式不规范'],
        'sign_day3' => ["number|requireIf:is_sign,1",["number"=>"第三天签到奖励格式不规范"],'requireIf'=>'请输入第三天签到奖励','gt'=>'第三天签到奖励格式不规范'],
        'sign_day4' => ["number|requireIf:is_sign,1",["number"=>"第四天签到奖励格式不规范"],'requireIf'=>'请输入第四天签到奖励','gt'=>'第四天签到奖励格式不规范'],
        'sign_day5' => ["number|requireIf:is_sign,1",["number"=>"第五天签到奖励格式不规范"],'requireIf'=>'请输入第五天签到奖励','gt'=>'第五天签到奖励格式不规范'],
        'sign_day6' => ["number|requireIf:is_sign,1",["number"=>"第六天签到奖励格式不规范"],'requireIf'=>'请输入第六天签到奖励','gt'=>'第六天签到奖励格式不规范'],
        'sign_day7' => ["number|requireIf:is_sign,1",["number"=>"第七天签到奖励格式不规范"],'requireIf'=>'请输入第七天签到奖励','gt'=>'第七天签到奖励格式不规范'],
        'location_url' => ["max:50|requireIf:is_location,1",["requireIf"=>"请输入跳转域名","max"=>"跳转域名长度超出限制"]],
        'reward_money' => ["require|array",['require'=>'打赏金额未配置','array'=>'打赏金额格式错误']],
        'charge_money' => ["require|array",['require'=>'充值金额未配置','array'=>'充值金额格式错误']],
    	'contactQQ' => ['number|length:5,11',['number'=>'qq号必须是数字类型','length'=>'qq号必须是5到11位']],
    	'contactWx' => ['regex:/^[a-zA-Z0-9_-]{5,20}$/',['regex'=>'微信号支持6到20位字符，由字母数字下划线减号组成']],
    	'contactTel' => ['regex:/^[0-9+-]{8,15}$/',['regex'=>'联系电话支持8到15位字符，由+-数字组成']],
    ];
    
    //支付信息配置规则
    public static $pay = [
        'APPID' => ['require',['require'=>'请输入微信支付APPID']],
        'MCHID' => ['require',['require'=>'请输入微信支付商户ID']],
        'APIKEY' => ['require',['require'=>'请输入微信支付KEY']]
    ];
    
    //短信配置
    public static $message = [
        'appid' => ['require',['require'=>'请输入短信appid']],
        'appkey' => ['require',['require'=>'请输入短信appkey']],
        'sign' => ['require',['require'=>'请输入短信签名']],
        'content' => ['require',['require'=>'请输入内容']]
    ];
    
    //阿里云配置规则
    public static $alioss = [
        'accessKey' => ['require',['require'=>'请输入阿里云accessKey']],
        'secretKey' => ['require',['require'=>'请输入阿里云secretKey']],
        'bucket' => ['require',['require'=>'请输入阿里云空间名称']],
        'url' => ['require',['require'=>'请输入阿里云绑定域名']]
    ];
    
    //充值配置参数
    public static $charge = [
        'money' => ['require|array',['require'=>'充值金额列数据异常','array'=>'充值金额列数据异常']],
        'reward' => ['require|array',['require'=>'赠送金额列数据异常','array'=>'赠送金额列数据异常']],
        'coin' => ['require|array',['require'=>'充值书币列数据异常','array'=>'充值书币列数据异常']],
        'is_hot' => ['require|array',['require'=>'是否热门列数据异常','array'=>'是否热门列数据异常']],
        'package' => ['require|array',['require'=>'套餐列数据异常','array'=>'套餐列数据异常']],
        'is_on' => ['require|array',['require'=>'是否开启列数据异常','array'=>'是否开启列数据异常']],
        'is_checked' => ['require|array',['require'=>'是否选中列数据异常','array'=>'是否选中列数据异常']]
    ];
    
    //图标参数
    public static $icon_rules = [
        'src' => ["array",['array'=>'图片格式格式不规范']],
        'text' => ["array",['array'=>'标题格式不规范']],
        'link' => ["array",['array'=>'链接不规范']],
        'area' => ["array",['array'=>'发布区域参数格式不规范']],
        'category' => ["array",['array'=>'类型参数格式不规范']]
    ];
    
    //保存配置
    public static function saveConfig($key,$data){
        $flag = false;
        $json = json_encode($data,JSON_UNESCAPED_UNICODE);
        $re = Db::name('Config')->where('key','=',$key)->setField('value',$json);
        if($re !== false){
            $flag = true;
        }
        return $flag;
    }
    
    //新增配置
    public static function addConfig($key,$data){
        $flag = false;
        $json = json_encode($data,JSON_UNESCAPED_UNICODE);
        $save = [
            'key' => $key,
            'value' => $json
        ];
        $re = Db::name('Config')->insert($save);
        if($re){
            $flag = true;
        }
        return $flag;
    }
    
    //获取配置
    public static function getConfig($key){
        $config = Db::name('Config')->where('key','=',$key)->field('key,value')->find();
        if($config){
            $cur = json_decode($config['value'],true);
        }else{
            $cur = false;
        }
        return $cur;
    }
}