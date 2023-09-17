<?php

namespace app\index\controller;

use think\Controller;
use think\Db;
use weixin\wxpay;
use app\common\model\myCache;
use mihua\mihuaPay;
use app\common\model\myRequest;
class Notify extends Controller {

    private $pay_type = 1;

    //微信支付异步回调
    public function index() {
        $this->pay_type = 1;
        $postStr = file_get_contents('php://input');
        libxml_disable_entity_loader(true);
        $result = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if (isset($result['sign'])) {
            $sign = $result['sign'];
            unset($result['sign']);
            wxpay::$config = myCache::getWxPayCache();
            $create_sign = wxpay::getSign($result);
            if ($create_sign == $sign) {
                if ($result['result_code'] == 'SUCCESS') {
                    self::doOrderStatus($result);
                }
            }
            self::resFail();
        }
    }

    //米花支付回调
    public function mihua() {
        $this->pay_type = 2;
        $get = $this->request->get('data');
        $config = myCache::getmihuaPayCache();
        if (!$config) {
            self::resFail();
        }
        mihuaPay::$config = $config;
        $data = mihuaPay::decryptData($get);
        $data = json_decode($data, true);
        if ($data['orderStatus'] != 'SUCCESS') {
            self::resFail();
        }
        $result = [
            'attach' => $data['attach'],
            'total_fee' => $data['amount'] * 100,
            'out_trade_no' => $data['orderId']
        ];
        self::doOrderStatus($result);
    }

    // milabaoNotify
    public function milabao() {
        $this->pay_type = 3;
        $request = $_REQUEST;
        $data = [
            'main' => $request
        ];
        file_put_contents('./milabao.txt', json_encode($data));
        // die();
        if ($request['state'] == 1) {
            
        }
        $config = myCache::getmilabaoPayCache();
        $sign = strtoupper(md5("customerid={$request['customerid']}&sd51no={$request['sd51no']}&sdcustomno={$request['sdcustomno']}&mark={$request['mark']}&key={$config['key']}"));
        $data = [
            'old' => $request['sign'],
            'new' => $sign
        ];
        file_put_contents('./milabao.txt', json_encode(['a' => $request,]));
        if ($data['old'] == $data['new']) {
            $result = [
                'attach' => json_encode(array('order_id' => $request['sdcustomno'], 'table' => $request['des'])),
                'total_fee' => $request['ordermoney'] * 100,
                'out_trade_no' => $request['sdcustomno'],
            ];
            self::doOrderStatus($result);
        } else {
            self::resFail();
        }
        // mihuaPay::$config = $config;
        // $data = mihuaPay::decryptData($get);
        // $data = json_decode($data,true);
        // if($data['orderStatus'] != 'SUCCESS'){
        // 	self::resFail();
        // }
        // die();
        // if($result['sign'] == $sign){
        // 	$resign = strtoupper(md5("sign={$sign}&customerid={$request['customerid']}&ordermoney={$request['ordermoney']}&sd51no={$request['sd51no']}&state={$request['state']}&key={$config['key']}"));
        // 	if($result['resign'] == $resign){
        // 		$result = [
        // 			'attach' => $result['mark'],
        // 			'total_fee' =>$request['ordermoney'],
        // 			'out_trade_no' =>$result['sdcustomno'],
        // 		];
        // 		self::doOrderStatus($result);
        // 	}
        // }
    }
    public function chinaxing(){
    	 $this->pay_type=5;
    	 $request = $_REQUEST;
        $data = [
            'time' => date('Y-m-d H:i:s'),
            'main' => $request
        ];
        if (!is_dir('./chinaxing-log')) {
            mkdir('./chinaxing-log', 0777, true);
           // echo 11;
        }
        file_put_contents('./chinaxing-log/' . date('Y-m-d') . '.log', json_encode($data), FILE_APPEND);
        //exit;
    	$config = myCache::getPayCatCache(); 
    //	$get = myRequest::get('out_trade_no,total_fee');
        $orderid=$_REQUEST['out_trade_no'];
        $price=	$_REQUEST['money'];
        $config = myCache::getPayCatCache();
        $alipay_config=array();
        $alipay_config['partner']		=  $config['uid'];
  
		//商户KEY
		$alipay_config['key']			= $config['token'];;
		//↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
		//签名方式 不需修改
		$alipay_config['sign_type']    = strtoupper('MD5');
		//字符编码格式 目前支持 gbk 或 utf-8
		$alipay_config['input_charset']= strtolower('utf-8');
		//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
		$alipay_config['transport']    = 'http';
		//支付API地址
		$alipay_config['apiurl']    =$config['gateway'];
	//	print_r(	$alipay_config);exit;
    	//计算得出通知验证结果
     
		$alipayNotify = new \chinaxing\AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		
		if($verify_result) {//验证成功
		        //校验key成功，是自己人。执行自己的业务逻辑：加余额，订单付款成功，装备购买成功等等。

            $result = [
                'attach' => json_encode(array('order_id' =>$orderid, 'table' => '')),
                 'total_fee' => $price * 100,
                'out_trade_no' => $orderid,
            ];
            self::doOrderStatus($result);
		 }else{
	       echo 'fail';
		 }
    }
    // 支付猫Notify
    public function paycat() {
        $this->pay_type = 4;
        $request = $_REQUEST;
        $data = [
            'time' => date('Y-m-d H:i:s'),
            'main' => $request
        ];
        if (!is_dir('./paycat-log')) {
            mkdir('./paycat-log', 0777, true);
        }
        file_put_contents('./paycat-log/' . date('Y-m-d') . '.log', json_encode($data), FILE_APPEND); //写入日志到log文件中


        $platform_trade_no = $_POST["platform_trade_no"];
        $orderid = $_POST["orderid"];
        $price = $_POST["price"];
        $realprice = $_POST["realprice"];
        $orderuid = $_POST["orderuid"];
        $key = $_POST["key"];

        $config = myCache::getPayCatCache();

        $token = $config['token'];

        $temps = md5($orderid . $orderuid . $platform_trade_no . $price . $realprice . $token);

        if ($temps != $key) {
            $return['msg'] = 'key值不匹配';
            $return['data'] = '';
            $return['code'] = -1;
            $return['url'] = null;
            return json_encode($return);
        } else {
            //校验key成功，是自己人。执行自己的业务逻辑：加余额，订单付款成功，装备购买成功等等。

            $result = [
                'attach' => json_encode(array('order_id' =>$orderid, 'table' => $orderuid)),
                'total_fee' => $price * 100,
                'out_trade_no' => $orderid,
            ];
            self::doOrderStatus($result);
        }
    }

    //处理订单
    private function doOrderStatus($result) {
        $info = json_decode($result['attach'], true);
        if (isset($info['table']) && isset($info['order_id'])) {
            if ($info['table'] == 'sale_order') {
                self::doProductOrder($result);
            } else {
                self::doChargeOrder($result);
            }
        }
    }

    //处理订单状态
    private function doChargeOrder($result) {
        $attach = json_decode($result['attach'], true);
        $order = Db::name('Order')->where('id', '=', $attach['order_id'])->field('id,channel_id,order_no,uid,money,send_money,package,is_count,is_count_temp,status')->find();
        if (!$order) {
            $order = Db::name('Order')->where('order_no', '=', $attach['order_id'])->field('id,channel_id,order_no,uid,money,send_money,package,is_count,is_count_temp,status')->find();
        }
        if (!$order || $order['order_no'] != $result['out_trade_no']) {
            var_dump($order);
            die;
            self::resFail();
        }
        $payMoney = $result['total_fee'] / 100;
        if ($payMoney < $order['money']) {
            self::resFail();
        }
        if ($order['status'] != 1) {
            self::resOk();
        }
        $user = Db::name('Member')->where('id', '=', $order['uid'])->field('id,money,is_charge,viptime')->find();
        if (!$user) {
            self::resFail();
        }
        Db::startTrans();
        $flag = false;
        $is_change = false;
        $data = [
            'pay_time' => time(),
            'status' => 2,
            'is_count' => $order['is_count']
        ];
        if ($order['channel_id'] > 0) {
            if ($order['is_count_temp'] == 1) {
                $data['is_count'] = 2;
            }
        } else {
            $data['is_count'] = 2;
        }
        $re = Db::name('Order')->where('id', '=', $order['id'])->update($data);
        if ($re) {
            $do_res = true;

            if ($order['package'] > 0) {
                $do_res = false;
                $viptime = self::getVipTime($order['package'], $user['viptime']);
                $mdata = ['viptime' => $viptime, 'is_charge' => 1];
                $res = Db::name('Member')->where('id', '=', $user['id'])->setField('viptime', $viptime);
                if ($res !== false) {
                    $do_res = $is_change = true;
                }
            } elseif ($order['send_money'] > 0) {
                $do_res = false;
                $mdata = ['money' => Db::raw('money+' . $order['send_money']), 'is_charge' => 1];
                $res = Db::name('Member')->where('id', '=', $user['id'])->update($mdata);
                if ($res) {
                    $do_res = $is_change = true;
                }
            }
            if ($do_res) {
                $done_res = true;
                if ($data['is_count'] == 1) {
                    $done_res = false;
                    $info = Db::name('OrderCount')->where('order_id', '=', $order['id'])->find();
                    if ($info) {
                        $count_res = Db::name('OrderCount')->where('id', '=', $info['id'])->setField('status', 1);
                        if ($count_res) {
                            if ($info['channel_id'] > 0) {
                                $channel_data = [
                                    'total_charge' => Db::raw('total_charge+' . $order['money'])
                                ];
                                if ($info['channel_money'] > 0) {
                                    $channel_data['money'] = Db::raw('money+' . $info['channel_money']);
                                }
                                $channel_res = Db::name('Channel')->where('id', '=', $info['channel_id'])->update($channel_data);
                                if ($channel_res) {
                                    if ($info['agent_id'] > 0) {
                                        $agent_data = [
                                            'total_charge' => Db::raw('total_charge+' . $order['money'])
                                        ];
                                        if ($info['agent_money'] > 0) {
                                            $agent_data['money'] = Db::raw('money+' . $info['agent_money']);
                                        }
                                        $agent_res = Db::name('Channel')->where('id', '=', $info['agent_id'])->update($agent_data);
                                        if ($agent_res) {
                                            $done_res = true;
                                        }
                                    } else {
                                        $done_res = true;
                                    }
                                }
                            }
                        }
                    }
                }
                if ($done_res) {
                    $flag = true;
                }
            }
        }
        if ($flag) {
            Db::commit();
            if ($is_change) {
                cache('member_info_' . $order['uid'], null);
            }
            self::resOk();
        } else {
            Db::rollback();
            self::resFail();
        }
    }

    //处理商品订单
    private function doProductOrder($result) {
        $attach = json_decode($result['attach'], true);
        $order = Db::name('SaleOrder')->where('id', '=', $attach['order_id'])->field('id,order_no,money,status,pid')->find();
        if (!$order) {
            $order = Db::name('Order')->where('order_no', '=', $attach['order_id'])->field('id,channel_id,order_no,uid,money,send_money,package,is_count,is_count_temp,status')->find();
        }
        if (!$order || $order['order_no'] != $result['out_trade_no']) {
            echo $order;
            die;
            self::resFail();
        }
        $payMoney = $result['total_fee'] / 100;
        if ($payMoney < $order['money']) {
            self::resFail();
        }
        if ($order['status'] != 1) {
            self::resOk();
        }
        Db::startTrans();
        $flag = false;
        $data = [
            'pay_time' => time(),
            'status' => 2
        ];
        $re = Db::name('SaleOrder')->where('id', '=', $order['id'])->update($data);
        if ($re) {
            $res = Db::name('Product')->where('id', '=', $order['pid'])->select('buy_num');
            if ($res !== false) {
                $flag = true;
            }
        }
        if ($flag) {
            Db::commit();
            self::resOk();
        } else {
            Db::rollback();
            self::resFail();
        }
    }

    //返回失败状态
    private function resFail() {
        echo 'fail';
        exit;
    }

    //返回成功状态
    private function resOk() {
        if ($this->pay_type == 1) {
            $str = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
        } elseif ($this->pay_type == 4) {
            $str = 'OK';
        } else {
            $str = 'SUCCESS';
        }
        echo $str;
        exit;
    }

    //获取vip包月时间
    private function getVipTime($package, $viptime) {
        $time = time();
        if ($viptime) {
            if ($viptime == 1) {
                return 1;
            } else {
                if ($viptime > $time) {
                    $time = $viptime;
                }
            }
        }
        switch ($package) {
            case 1:
                $time += 86400;
                break;
            case 2:
                $time += 86400 * 30;
                break;
            case 3:
                $time += 86400 * 90;
                break;
            case 4:
                $time += 86400 * 180;
                break;
            case 5:
                $time += 86400 * 365;
                break;
            case 6:
                $time = -1;
                break;
        }
        return $time;
    }

}
