<?php

namespace app\index\controller;

use think\Controller;
use app\index\model\iClient;
use app\common\model\myRequest;
use other\myHttp;
use app\common\model\myCache;
use weixin\wx;
use weixin\wxpay;
use think\Db;
use mihua\mihuaPay;
use app\index\model\iOrder;
 
class Pay extends Controller {

    //jsapi支付
    public function jsPay() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
            res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
        wx::$config = $config;
        if (!session('PAY_OPEN_ID')) {
            self::_auto_login($config);
        }
        $get = myRequest::get('order_no,table,back_url');
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }

        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 1) {
            res_return('支付方式有误');
        }
        $config = myCache::getWxPayCache();
        if (!$config) {
            res_return('未配置支付参数');
        }
        $config['notify_url'] = 'http://' . $website['url'] . '/index/Notify/index.html';
        wxpay::$config = $config;
        $params = [
            'body' => '书币充值',
            'out_trade_no' => $order['order_no'],
            'total_fee' => $order['money'],
            'attach' => json_encode(['table' => $table, 'order_id' => $order['id']], JSON_UNESCAPED_UNICODE),
            'trade_type' => 'JSAPI',
            'openid' => session('PAY_OPEN_ID')
        ];
        $paymsg = wxpay::getPayMsg($params);
        if (!$paymsg) {
            res_return('支付数据异常');
        }
        $variable = [
            'paymsg' => json_encode($paymsg, JSON_UNESCAPED_UNICODE),
            'cur' => $order,
            'back_url' => $get['back_url']
        ];
        $this->assign($variable);
        return $this->fetch('jsPay');
    }

    //米花支付
    public function mihuaPay() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
           res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
        wx::$config = $config;
        if (!session('PAY_OPEN_ID')) {
            self::_auto_login($config);
        }
        $get = myRequest::get('order_no,table,back_url');
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }
        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 2) {
            res_return('支付方式有误');
        }
        $config = myCache::getmihuaPayCache();
        if (!$config) {
            res_return('未配置支付参数');
        }
        $config['notify_url'] = 'http://' . $website['url'] . '/index/Notify/mihua.html';
        $config['back_url'] = $get['back_url'];
        mihuaPay::$config = $config;
        $params = [
            'ip' => request()->ip(),
            'body' => '书币充值',
            'out_trade_no' => $order['order_no'],
            'total_fee' => $order['money'],
            'attach' => json_encode(['table' => $table, 'order_id' => $order['id']], JSON_UNESCAPED_UNICODE),
            'openid' => session('PAY_OPEN_ID')
        ];
        $payurl = mihuaPay::doPay($params);
        if ($payurl) {
            iOrder::save('Order', [['id', '=', $order['id']]], ['pay_url' => $payurl]);
            $this->redirect($payurl);
        } else {
            res_return('创建支付数据失败');
        }
    }

    // milabao
    public function milabaoPay() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
            res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
        wx::$config = $config;
        if (!session('PAY_OPEN_ID')) {
            self::_auto_login($config);
        }
        $get = myRequest::get('order_no,table,back_url,paytype');
        $get['paytype'] = isset($get['paytype']) ? $get['paytype'] : 1;
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }
        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 3) {
            res_return('支付方式有误');
        }

        // start build payinfo
        // 基本配置
        $config = myCache::getmilabaoPayCache();
        // 基本参数
        $param = [
            'customerid' => $config['appid'],
            'sdcustomno' => $order['order_no'],
            'orderAmount' => $order['money'] * 100,
            'cardno' => 32,
            'noticeurl' => 'http://' . $website['url'] . '/index/Notify/milabao.html',
            'backurl' => $get['back_url'],
            'mark' => json_encode(['table' => $table, 'order_id' => $order['id']], JSON_UNESCAPED_UNICODE),
        ];
        // 进入签名
        $md5Str = "customerid={$param['customerid']}&sdcustomno={$param['sdcustomno']}&orderAmount={$param['orderAmount']}&cardno={$param['cardno']}&noticeurl={$param['noticeurl']}&backurl={$param['backurl']}{$config['key']}";
        $param['sign'] = strtoupper(md5($md5Str));
        // 建立请求
     $html=$config['gateway'];
        switch ($get['paytype']){
            case 1:
                $html='http://api.milabao.com/intf/wapali.html?';
                break;
             case 2:
                $html='http://api.milabao.com/intf/wapwpay.html?';
                break;
        }
        $rqeuestStr = $html. http_build_query($param);


        iOrder::save('order', [['id', '=', $order['id']]], ['pay_url' => $rqeuestStr]);
        $this->redirect($rqeuestStr);
    }

    // 支付猫
    public function paycat() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
            res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
        wx::$config = $config;
        if (!session('PAY_OPEN_ID')) {
            self::_auto_login($config);
        }
        $get = myRequest::get('order_no,table,back_url,paytype');
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }
        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 4) {
            res_return('支付方式有误');
        }

        // start build payinfo
        // 基本配置
        $config = myCache::getPayCatCache();
        // 基本参数

        $goodsname = '充值'; //商品名称
        $istype = $get['paytype']; //支付猫支付方式1=支付宝2=微信
        $notify_url = 'http://' . $website['url'] . '/index/Notify/paycat.html'; //回调地址
        $orderid = $get['order_no']; //订单id
        $orderuid = $get['table']; //付款用户名称
        $price = $order['money']; //价格
        $return_url = $get['back_url']; //支付成功后访问的页面
        $token = $config['token']; //商户支付猫平台token
        $uid = $config['uid']; //商户支付猫平台uid

        $key = md5($goodsname . $istype . $notify_url . $orderid . $orderuid . $price . $return_url . $token . $uid);
        //经常遇到有研发问为啥key值返回错误，大多数原因：1.参数的排列顺序不对；2.上面的参数少传了，但是这里的key值又带进去计算了，导致服务端key算出来和你的不一样。
        $param = [
            'goodsname' => $goodsname,
            'istype' => $istype,
            'key' => $key,
            'notify_url' => $notify_url,
            'orderid' => $orderid,
            'orderuid' => $orderuid,
            'price' => $price,
            'return_url' => $return_url,
            'uid' => $uid,
        ];


        // 建立请求
        $rqeuestStr = $config['gateway'] . http_build_query($param);
//        iOrder::save('order', [['id', '=', $order['id']]], ['pay_url' => $rqeuestStr]);
        $this->redirect($rqeuestStr);
    }
    
      // chinaxing
    public function chinaxing() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
           // res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
 
   
          
        $get = myRequest::get('order_no,table,back_url,paytype');
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }
 
        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 5) {
            res_return('支付方式有误');
        }

        // start build payinfo
        // 基本配置
        $config = myCache::getPayCatCache();
        
          
/**************************请求参数**************************/
        $notify_url = 'http://' . $website['url'] . '/index/Notify/chinaxing.html'; //回调地址
        $return_url = $get['back_url'];
        //商户订单号
        $out_trade_no = $get['order_no'];
        //商户网站订单系统中唯一订单号，必填
        $alipay_config=array();
        $alipay_config['partner']		=  $config['uid'];

		//商户KEY
		$alipay_config['key']			= $config['token'];;
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('gbk');
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		//支付API地址
		$alipay_config['apiurl']    =$config['gateway'];
		//支付方式
        $type =$get['paytype'];
        //商品名称
        $name = '会员充值'. $order['money'] . '元';
		//付款金额
        $money =        $order['money'];
		//站点名称
        $sitename = '易支付';
        //必填

        //订单描述

/************************************************************/
//构造要请求的参数数组，无需改动
$parameter = array(
		"pid" => $config['uid'],
		"type" => $type,
		"notify_url"	=> $notify_url,
		"return_url"	=> $return_url,
		"out_trade_no"	=> $out_trade_no,
		"name"	=> $name,
		"money"	=> $money,
		"sitename"	=> $sitename
);

//建立请求
$alipaySubmit = new \chinaxing\AlipaySubmit($alipay_config);
$html_text = $alipaySubmit->buildRequestForm($parameter);
echo $html_text;
 
    }
    
    //彩虹易支付
    public function epay() {
        $device_type = iClient::getDeviceType();
        if ($device_type != 1) {
            res_return('请在微信中完成支付');
        }
        $config = myCache::getUrlCache();
        $website = myCache::getWebSiteCache();
        if ($config['url'] !== $website['url']) {
            res_return('非法访问');
        }
        wx::$config = $config;
        if (!session('PAY_OPEN_ID')) {
            self::_auto_login($config);
        }
        $get = myRequest::get('order_no,table,back_url,paytype');
        if (!$get['order_no']) {
            res_return('订单参数异常');
        }
        $table = in_array($get['table'], ['order', 'sale_order']) ? $get['table'] : 'order';
        $order = Db::name($table)->where('order_no', '=', $get['order_no'])->where('status', '=', 1)->field('id,order_no,money,status,pay_type')->find();
        if (!$order) {
            res_return('订单数据异常');
        }
        if ($order['status'] != 1) {
            res_return('该订单已支付');
        }
        if ($order['pay_type'] != 5) {
            res_return('支付方式有误');
        }

        // start build payinfo
        // 基本配置
        $config = myCache::getPayCatCache();
        // 基本参数

        $goodsname = '充值'; //商品名称
        $istype = $get['paytype']; //彩虹易支付方式1=支付宝2=微信
        $notify_url = 'http://' . $website['url'] . '/index/Notify/pay.html'; //回调地址
        $orderid = $get['order_no']; //订单id
        $orderuid = $get['table']; //付款用户名称
        $price = $order['money']; //价格
        $return_url = $get['back_url']; //支付成功后访问的页面
        $token = $config['token']; //商户支付猫平台token
        $uid = $config['uid']; //商户支付猫平台uid

        $key = md5($goodsname . $istype . $notify_url . $orderid . $orderuid . $price . $return_url . $token . $uid);
        //经常遇到有研发问为啥key值返回错误，大多数原因：1.参数的排列顺序不对；2.上面的参数少传了，但是这里的key值又带进去计算了，导致服务端key算出来和你的不一样。
        $param = [
            'goodsname' => $goodsname,
            'istype' => $istype,
            'key' => $key,
            'notify_url' => $notify_url,
            'orderid' => $orderid,
            'orderuid' => $orderuid,
            'price' => $price,
            'return_url' => $return_url,
            'uid' => $uid,
        ];


        // 建立请求
        $rqeuestStr = $config['gateway'] . http_build_query($param);
//        iOrder::save('order', [['id', '=', $order['id']]], ['pay_url' => $rqeuestStr]);
        $this->redirect($rqeuestStr);
    }

    // 尝试自动登录
    private function _auto_login($config) {
        $get = myRequest::get('code,state,order_no,table');
        if ($get['code'] && $get['state'] === 'getnow') {
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $config['appid'] . "&secret=" . $config['appsecret'] . "&code=" . $get['code'] . "&grant_type=authorization_code";
            $res = myHttp::getData($url);
            if ($res && isset($res['openid'])) {
                if (isset($res['openid']) && $res['openid']) {
                    session('PAY_OPEN_ID', $res['openid']);
                } else {
                    $wxerror = isset($res['errmsg']) ? $res['errmsg'] : '';
                    $error = '授权失败';
                    if ($wxerror) {
                        $error .= ':' . $wxerror;
                    }
                    res_return($error);
                }
            } else {
                res_return('获取openid失败');
            }
        } else {
            $param = myRequest::get();
            $redirect_url = self::getLocationUrl($config['url'], $param);
            $redirect_url = urlencode($redirect_url);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $config['appid'] . "&redirect_uri=" . $redirect_url . "&response_type=code&scope=snsapi_base&state=getnow#wechat_redirect";
            $this->redirect($url);
        }
    }

    //拼装跳转url
    private function getLocationUrl($url, $param = null) {
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $link = 'http://' . $url . '/' . $module . '/' . $controller . '/' . $action . '.html';
        if ($param) {
            if (is_array($param)) {
                $link .= '?' . http_build_query($param);
            } else {
                $link .= '?' . $param;
            }
        }
        return $link;
    }

}
