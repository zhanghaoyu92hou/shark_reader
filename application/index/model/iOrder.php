<?php

namespace app\index\model;

use app\index\model\Common;
use think\Db;
use app\common\model\myCache;

class iOrder extends Common {

    //创建订单号
    public static function createOrderNo($table = 'Order') {
        $orderno = date('YmdHis') . mt_rand(100000, 999999);
        $repeat = Db::name($table)->where('order_no', '=', $orderno)->value('id');
        if ($repeat) {
            self::createOrderNo($table);
        }
        return $orderno;
    }

    //获取扣量检测订单列表
    public static function getDeductOrder($where, $limit) {
        $list = Db::name('Order')->where($where)->field('id,is_count')->order('id', 'desc')->limit($limit)->select();
        return $list;
    }

    //获取扣量代理福利时长相关信息
    public static function createOrderInfo($member) {
        $channel_id = $agent_id = 0;
        $is_count_temp = 2;
        if ($member['channel_id'] > 0) {
            $channel_id = $member['channel_id'];
            $channel = myCache::getChannelCache($channel_id);
            if (!$channel) {
                res_return('渠道信息异常');
            }
            if ($member['agent_id'] > 0) {
                $agent_id = $member['agent_id'];
                //开启代理福利时长
                if ($channel['wefare_days'] > 0) {
                    $now_time = time();
                    $end_time = $member['create_time'] + 86400 * $channel['wefare_days'];
                    if ($now_time > $end_time) {
                        $agent_id = 0;
                    }
                }
            }
            if ($agent_id > 0) {
                $agent = myCache::getChannelCache($agent_id);
                if (!$agent) {
                    res_return('代理信息异常');
                }
                $is_count_temp = self::getIsCountTemp($agent, $channel_id, $agent_id);
            } else {
                $is_count_temp = self::getIsCountTemp($channel, $channel_id, $agent_id);
            }
        }
        return ['is_count_temp' => $is_count_temp, 'uid' => $member['id'], 'channel_id' => $channel_id, 'agent_id' => $agent_id, 'wx_id' => $member['wx_id']];
    }

    //检测是否扣量
    private static function getIsCountTemp($cur, $channel_id, $agent_id) {
        $is_count_temp = 2;
        if ($cur['deduct_num'] > 0) {
            if ($cur['deduct_num'] == 1) {
                $is_count_temp = 1;
            } else {
                $where = [['status', '=', 2], ['channel_id', '=', $channel_id], ['agent_id', '=', $agent_id]];
                $count = parent::getCount('Order', $where);
                if ($count >= $cur['deduct_min']) {
                    $near_num = $count - $cur['deduct_min'];
                    $is_deduction = false;
                    $rate = (round(1 / $cur['deduct_num'], 2)) * 1000;
                    $max = $rate * $cur['deduct_num'];
                    if ($near_num) {
                        $last_num = $near_num % $cur['deduct_num'];
                        if ($last_num > 0) {
                            $list = self::getDeductOrder($where, $last_num);
                            $is_done = true;
                            foreach ($list as $v) {
                                if ($v['is_count'] != 1) {
                                    $is_done = false;
                                    break;
                                }
                            }
                            if ($is_done) {
                                if (($last_num + 1) == $cur['deduct_num']) {
                                    $is_count_temp = 1;
                                } else {
                                    $is_deduction = true;
                                    $cur_rate = ($last_num + 1) * $rate;
                                }
                            }
                        } else {
                            $is_deduction = true;
                            $cur_rate = $rate;
                        }
                    } else {
                        $is_deduction = true;
                        $cur_rate = $rate;
                    }
                    if ($is_count_temp == 2 && $is_deduction) {
                        $flag = rand(1, $max);
                        if ($flag <= $cur_rate) {
                            $is_count_temp = 1;
                        }
                    }
                }
            }
        }
        return $is_count_temp;
    }

    //创建订单
    public static function createOrder($data) {
        Db::startTrans();
        $flag = false;
        $re = Db::name('Order')->insertGetId($data);
        if ($re) {
            $info = [
                'channel_id' => $data['channel_id'],
                'agent_id' => $data['agent_id'],
                'order_id' => $re,
                'status' => 2,
                'type' => $data['type']
            ];
            if ($data['channel_id'] > 0) {
                $channel_ratio = Db::name('Channel')->where('id', '=', $data['channel_id'])->value('ratio');
                if ($channel_ratio > 0) {
                    $info['channel_money'] = round($data['money'] * ($channel_ratio / 100), 2);
                }
            }
            if ($data['agent_id'] > 0) {
                $agent_ratio = Db::name('Channel')->where('id', '=', $data['agent_id'])->value('ratio');
                if ($channel_ratio > 0) {
                    $info['agent_money'] = round($data['money'] * ($agent_ratio / 100), 2);
                }
            }
            $res = Db::name('OrderCount')->insert($info);
            if ($res) {
                $flag = true;
            }
        }
        if ($flag) {
            Db::commit();
        } else {
            Db::rollback();
        }
        return $flag;
    }

    //检测活动充值次数
    public static function checkRepeat($activity_id, $uid) {
        $flag = false;
        $repeat = Db::name('Order')
                ->where('uid', '=', $uid)
                ->where('type', '=', 2)
                ->where('relation_id', '=', $activity_id)
                ->where('status', '=', 2)
                ->value('id');
        if ($repeat) {
            $flag = true;
        }
        return $flag;
    }

    //创建商品订单
    public static function createSaleOrder($data) {
        $re = Db::name('SaleOrder')->insert($data);
        $res = $re ? true : false;
        return $res;
    }

    //创建支付url
    public static function createPayUrl($table, $order_no, $pay_type, $back_url = '', $cat_paytype = 1) {
        $pay_url = '';
        $site = myCache::getWebSiteCache();
        if (!$site) {
            res_return('尚未配置站点信息');
        }
        switch ($pay_type) {
            case 1:
                $pay_url = 'http://' . $site['url'] . '/index/Pay/jsPay.html?table=' . $table . '&order_no=' . $order_no . '&back_url=' . $back_url;
                break;
            case 2:
                $pay_url = 'http://' . $site['url'] . '/index/Pay/mihuaPay.html?table=' . $table . '&order_no=' . $order_no . '&back_url=' . $back_url;
                break;
            case 3:
                $pay_url = 'http://' . $site['url'] . '/index/Pay/milabaoPay.html?table=' . $table . '&order_no=' . $order_no . '&back_url=' . $back_url. '&paytype=' . $cat_paytype;
                break;
            case 4:
                $pay_url = 'http://' . $site['url'] . '/index/Pay/paycat.html?table=' . $table . '&order_no=' . $order_no . '&back_url=' . $back_url . '&paytype=' . $cat_paytype;
           case 5:
                $pay_url = 'http://' . $site['url'] . '/index/Pay/chinaxing.html?table=' . $table . '&order_no=' . $order_no . '&back_url=' . $back_url . '&paytype=';    
                break;
        }
        return $pay_url;
    }

}
