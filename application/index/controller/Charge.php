<?php

namespace app\index\controller;

use app\index\controller\Common;
use app\common\model\myRequest;
use app\index\model\iOrder;
use app\common\model\myCache;
use app\common\model\myValidate;
use app\index\model\iMessage;

class Charge extends Common {

    //充值首页
    public function index() {
        if ($this->request->isAjax()) {
            self::doCharge();
        } else {
            parent::checkLogin();
            global $loginId;
            $member = myCache::getUserCache($loginId);

            $get = myRequest::get('book_id,video_id');
            $block = myCache::getWebblockCache();
            $urlData = myCache::getUrlCache();
            $website = myCache::getWebSiteCache();
 
//            echo '<pre>';
//            print_r($this->device_type);
//            exit;
            $variable = [
                'web_block' => $block,
                'site_info' => $urlData,
                'device_type' => 1,
                'member' => $member,
                'get' => $get,
                'paytype' => $website['pay_type']
            ];
            $this->assign($variable);
            return $this->fetch();
        }
    }

//    //AJAX发起检测当前是不是支付猫支付
//    public function isPayCat() {
//        $website = myCache::getWebSiteCache();
//        if (isset($website['pay_type']) && $website['pay_type'] == 4) {
//            return 1;
//        } else {
//            return 0;
//        }
//    }
    //普通充值
    private function doCharge() {
        global $loginId;
        if (!$loginId) {
            res_return('您尚未登录');
        }
        $member = myCache::getUserCache($loginId);
        if (!$member) {
            session('INDEX_LOGIN_ID', null);
            res_return('用户信息异常,请刷新页面重试');
        }
        $data = iOrder::createOrderInfo($member);
        $data['type'] = 1;
        $data['create_time'] = time();
        $data['order_no'] = iOrder::createOrderNo();
        $website = myCache::getWebSiteCache();

        $data['pay_type'] = isset($website['pay_type']) && in_array($website['pay_type'], [1, 2, 3, 4,5]) ? $website['pay_type'] : 1;
        
       // print_r($data['pay_type']);exit;
        if ($data['pay_type'] == 4||$data['pay_type'] == 3 ||$data['pay_type'] == 5) {//4=支付猫支付3=咪啦宝
            $post = myRequest::post('book_id,video_id,charge_key,charge_money,paytype');
        } else {
            $post = myRequest::post('book_id,video_id,charge_key,charge_money');
        }
        $post['cat_paytype'] = isset($post['paytype']) ? $post['paytype'] : 1;
        if ($post['book_id'] && is_numeric($post['book_id'])) {
            $field = 'id as relation_id,type as relation_type,name as relation_name';
            $book = iOrder::getById('Book', $post['book_id'], $field);
            if ($book && in_array($book['relation_type'], [1, 2, 3])) {
                $data = array_merge($data, $book);
                if ($member['spread_id']) {
                    $spread = myCache::getSPreadCache($member['spread_id']);
                    if ($spread && $spread['book_id'] == $post['book_id']) {
                        $data['spread_id'] = $spread['id'];
                    }
                }
            }
        }
        if ($post['video_id'] && is_numeric($post['video_id'])) {
            $field = 'id as relation_id,name as relation_name';
            $video = iOrder::getById('Video', $post['video_id'], $field);
            if ($video) {
                $data['relation_type'] = 4;
                $data = array_merge($data, $video);
            }
        }
        if (strlen($post['charge_key']) > 0 && is_numeric($post['charge_key'])) {
            $charges = myCache::getChargeCache();
            if (!$charges) {
                res_return('充值数据有误');
            }
            if (isset($charges[$post['charge_key']])) {
                $charge = $charges[$post['charge_key']];
                if (!isset($charge['money']) || $charge['money'] != $post['charge_money']) {
                    res_return('充值金额异常');
                }
                $data['money'] = $charge['money'];
                if (floatval($data['money']) <= 0) {
                    res_return('充值金额异常');
                }
                if ($charge['package'] > 0) {
                    $data['package'] = $charge['package'];
                } else {
                    $data['send_money'] = intval($charge['coin']) + intval($charge['reward']);
                }
                $res = iOrder::createOrder($data);
                if ($res) {
                    $back_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/User/index.html';
                    $back_url = urlencode($back_url);
                    $pay_url = iOrder::createPayUrl('order', $data['order_no'], $data['pay_type'], $back_url, $post['cat_paytype']); //cat_paytype支付猫的支付方式1=支付宝2=微信
                    
                    
                    res_return(['url' => $pay_url]);
                } else {
                    res_return('创建订单失败');
                }
            } else {
                res_return('充值数据有误');
            }
        } else {
            res_return('充值数据有误');
        }
    }

    //活动充值
    public function doActivityCharge() {
        global $loginId;
        if (!$loginId) {
            res_return('您尚未登录');
        }
        $member = myCache::getUserCache($loginId);
        if (!$member) {
            session('INDEX_LOGIN_ID', null);
            res_return('用户信息异常,请刷新页面重试');
        }
        if ($member['status'] != 1) {
            res_return('账号异常，请联系客服');
        }
        $activity_id = myRequest::postId('活动', 'activity_id');
        $activity = myCache::getActivityCache($activity_id);
        if (!$activity) {
            res_return('该活动不存在');
        }
        if ($activity['is_first'] == 1) {
            $repeat = iOrder::checkRepeat($activity_id, $loginId);
            if ($repeat) {
                res_return('该活动仅限充值一次');
            }
        }
        $time = time();
        if ($activity['start_time'] > $time) {
            res_return('该活动尚未开始');
        }
        if ($activity['end_time'] < $time) {
            res_return('该活动已结束');
        }
        $data = iOrder::createOrderInfo($member);
        $data['type'] = 2;
        $data['create_time'] = $time;
        $data['order_no'] = iOrder::createOrderNo();
        $data['relation_id'] = $activity_id;
        $data['relation_name'] = $activity['name'];
        $data['money'] = $activity['money'];
        $data['send_money'] = $activity['send_money'];
        if (floatval($data['money']) <= 0) {
            res_return('充值金额异常');
        }
        $website = myCache::getWebSiteCache();
        $data['pay_type'] = isset($website['pay_type']) && in_array($website['pay_type'], [1, 2,3,4,5]) ? $website['pay_type'] : 1;
        $res = iOrder::createOrder($data);
        if ($res) {
            $back_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/User/index.html';
            $back_url = urlencode($back_url);
            $pay_url = iOrder::createPayUrl('order', $data['order_no'], $data['pay_type'], $back_url);
            res_return(['url' => $pay_url]);
        } else {
            res_return('创建订单失败');
        }
    }

    //打赏
    public function doReward() {
        global $loginId;
        if (!$loginId) {
            res_return('您尚未登录');
        }
        $member = myCache::getUserCache($loginId);
        if (!$member) {
            session('INDEX_LOGIN_ID', null);
            res_return('用户信息异常,请刷新页面重试');
        }
        $data = iOrder::createOrderInfo($member);
        $data['type'] = 3;
        $data['create_time'] = time();
        $data['order_no'] = iOrder::createOrderNo();
        $rules = [
            'money' => ['require|float|gt:0', ['require' => '打赏金额异常', 'float' => '打赏金额格式不规范', 'gt' => '打赏金额格式不规范']],
            'relation_type' => ['require|in:1,2,3,4', ['require' => '打赏数据有误', 'in' => '打赏数据有误']],
            'relation_id' => ['require|number|gt:0', ['require' => '打赏数据有误', 'number' => '打赏数据有误', 'gt' => '打赏数据有误']],
        ];
        $post = myValidate::getData($rules, 'money,relation_type,relation_id');
        $data['money'] = $post['money'];
        if (floatval($data['money']) <= 0) {
            res_return('打赏金额有误');
        }
        $data['relation_type'] = $post['relation_type'];
        $data['relation_id'] = $post['relation_id'];
        $relation_name = '未知';
        if ($data['relation_type'] == 4) {
            $back_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/Vidoe/info.html?video_id=' . $data['relation_id'];
            $video = myCache::getVideoCache($data['relation_id']);
            if ($video) {
                $relation_name = $video['name'];
            }
        } else {
            $back_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/Book/info.html?book_id=' . $data['relation_id'];
            $book = myCache::getBookCache($data['relation_id']);
            if ($book) {
                $relation_name = $book['name'];
            }
        }
        $data['relation_name'] = $relation_name;
        $website = myCache::getWebSiteCache();
        $data['pay_type'] = isset($website['pay_type']) && in_array($website['pay_type'], [1, 2, 3, 4, 5]) ? $website['pay_type'] : 1;
        $res = iOrder::createOrder($data);
        if ($res) {
            $back_url = urlencode($back_url);
            $pay_url = iOrder::createPayUrl('order', $data['order_no'], $data['pay_type'], $back_url);
            res_return(['url' => $pay_url]);
        } else {
            res_return('创建订单失败');
        }
    }

    //商品订单
    public function saleOrder() {
        global $loginId;
        if (!$loginId) {
            res_return('您尚未登陆');
        }
        $user = myCache::getUserCache($loginId);
        if (!$user) {
            res_return('用户信息异常');
        }
        $rules = [
            'username' => ['require|max:6', ['require' => '请输入收货人姓名', 'max' => '姓名长度不能超过6位']],
            'phone' => ['require|mobile', ['require' => '请输入手机号码', 'mobile' => '手机号格式不正确']],
            'code' => ['require|number|length:6', ['require' => '请输入验证码', 'number' => '验证码为6位数字', 'length' => '验证码为6位数字']],
            'remark' => ['max:200', ['max' => '最多输入200个字符的备注']],
            'address' => ['require|max:200', ['require' => '请输入收货地址', 'max' => '收货地址最多200个字符']],
            'count' => ['require|number|gt:0', ['require' => '请输入购买商品数量', 'number' => '商品数量格式不规范', 'gt' => '商品数量格式不规范']],
            'pid' => ['require|number|gt:0', ['require' => '商品异常', 'number' => '商品格式不规范', 'gt' => '商品格式不规范']],
        ];
        $data = myValidate::getData($rules, 'username,phone,code,remark,address,count,pid');
        iMessage::check($data['phone'], $data['code']);
        unset($data['code']);
        $product = myCache::getProductCache($data['pid']);
        if (!$product) {
            res_return('该商品已下架');
        }
        $time = time();
        $data['status'] = 1;
        $data['uid'] = $user['id'];
        $data['remark'] = htmlspecialchars($data['remark']);
        $data['address'] = htmlspecialchars($data['address']);
        $data['channel_id'] = $user['channel_id'];
        $data['order_no'] = iOrder::createOrderNo('saleOrder');
        $data['money'] = $data['count'] * $product['money'];
        $data['pname'] = $product['name'];
        $data['date'] = date('Ymd', $time);
        $data['create_time'] = $time;
        $website = myCache::getWebSiteCache();
        $data['pay_type'] = isset($website['pay_type']) && in_array($website['pay_type'], [1, 2, 3, 4, 5]) ? $website['pay_type'] : 1;
        $re = iOrder::createSaleOrder($data);
        if ($re) {
            $site_info = myCache::getWebSiteCache();
            $back_url = 'http://' . $_SERVER['HTTP_HOST'] . '/index/User/myOrder.html';
            $back_url = urlencode($back_url);
            $pay_url = iOrder::createPayUrl('sale_order', $data['order_no'], $data['pay_type'], $back_url);
            res_return(['url' => $pay_url]);
        } else {
            res_return('创建订单失败');
        }
    }

}
