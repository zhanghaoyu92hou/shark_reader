<?php

namespace app\admin\controller;  //后台消息管理控制器

use think\Controller;    //程序控制器
use think\Db;   
use weixin\wx;
use app\common\model\myCache;
use app\common\model\myRequest;
use app\admin\model\mBook;
use app\admin\model\mVideo;
use app\admin\model\mProduct;

class Plan extends Controller {

    public function __construct() {
        parent::__construct();
        $res = false;
        if ('42.51.223.61' === $_SERVER['HTTP_HOST']) {
            $res = true;
        }
        
        if (!$res) {
            echo '';
            exit;
        }
    }

    //统计用户信息，凌晨5点执行
    public function member() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        set_time_limit(1800);
        $start_time = strtotime('yesterday');
        $end_time = $start_time + 86399;
        $create_date = date('Y-m-d', $start_time);
        $repeat = Db::name('TaskMember')->where('create_date', '=', $create_date)->value('id');
        if ($repeat) {
            exit;
        }
        $data = [];
        $list = Db::name('Member')->where('create_time', 'between', [$start_time, $end_time])->field('id,channel_id,agent_id,subscribe,subscribe_time,sex,is_charge')->select();
        if ($list) {
            foreach ($list as $v) {
                $sex = in_array($v['sex'], [1, 2]) ? $v['sex'] : '0';
                $sex_key = 'sex' . $sex;
                $temp = ['create_date' => $create_date, 'channel_id' => 0, 'add_num' => 1, 'sub_num' => 0, 'sex0' => 0, 'sex1' => 0, 'sex2' => 0, 'charge_money' => 0, 'charge_nums' => 0];
                if (!isset($data[0])) {
                    $data[0] = $temp;
                } else {
                    $data[0]['add_num'] += 1;
                }
                $data[0][$sex_key] += 1;
                $channel_id = $v['channel_id'];
                $agent_id = $v['agent_id'];
                if ($channel_id) {
                    if (!isset($data[$channel_id])) {
                        $data[$channel_id] = $temp;
                        $data[$channel_id]['channel_id'] = $channel_id;
                    } else {
                        $data[$channel_id]['add_num'] += 1;
                    }
                    $data[$channel_id][$sex_key] += 1;
                }
                if ($agent_id) {
                    if (!isset($data[$agent_id])) {
                        $data[$agent_id] = $temp;
                        $data[$agent_id]['channel_id'] = $agent_id;
                    } else {
                        $data[$agent_id]['add_num'] += 1;
                    }
                    $data[$agent_id][$sex_key] += 1;
                }
                if ($v['subscribe'] == 1 && ($start_time <= $v['subscribe_time'] && $end_time >= $v['subscribe_time'])) {
                    $data[0]['sub_num'] += 1;
                    if ($channel_id) {
                        $data[$channel_id]['sub_num'] += 1;
                    }
                    if ($agent_id) {
                        $data[$agent_id]['sub_num'] += 1;
                    }
                }
                if ($v['is_charge'] == 1) {
                    $order = Db::name('Order')
                            ->where('uid', '=', $v['id'])
                            ->where('status', '=', 2)
                            ->where('create_time', 'between', [$start_time, $end_time])
                            ->field('id,uid,channel_id,agent_id,money,is_count')
                            ->select();
                    if ($order) {
                        $channel_num = $agent_num = 0;
                        $data[0]['charge_nums'] += 1;
                        foreach ($order as $val) {
                            $data[0]['charge_money'] += $val['money'];
                            if ($channel_id > 0 && $channel_id == $val['channel_id'] && $val['is_count'] == 1) {
                                $channel_num = 1;
                                $data[$channel_id]['charge_money'] += $val['money'];
                            }
                            if ($agent_id > 0 && $agent_id == $val['agent_id'] && $val['is_count'] == 1) {
                                $agent_num = 1;
                                $data[$agent_id]['charge_money'] += $val['money'];
                            }
                        }
                        if ($channel_num) {
                            $data[$channel_id]['charge_nums'] += $channel_num;
                        }
                        if ($agent_num) {
                            $data[$agent_id]['charge_nums'] += $agent_num;
                        }
                    }
                }
            }
        }
        if ($data) {
            Db::name('TaskMember')->insertAll($data);
        }
    }

    //统计订单信息，凌晨5点10分执行
    public function order() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        set_time_limit(1800);
        $start_time = strtotime('yesterday');
        $end_time = $start_time + 86399;
        $create_date = date('Y-m-d', $start_time);
        $repeat = Db::name('TaskOrder')->where('create_date', '=', $create_date)->value('id');
        if ($repeat) {
            exit;
        }
        $data = [];
        $list = Db::name('Order')->where('create_time', 'between', [$start_time, $end_time])->field('id,type,channel_id,agent_id,package,uid,status,money,is_count')->select();
        if ($list) {
            foreach ($list as $v) {
                $temp = ['create_date' => $create_date, 'channel_id' => 0, 'n_pay' => 0, 'n_notpay' => 0, 'n_money' => 0, 'n_user' => 0, 'n_rate' => 0, 'p_pay' => 0, 'p_notpay' => 0, 'p_money' => 0, 'p_user' => 0, 'p_rate' => 0, 'total_money' => 0, 'type1_money' => 0, 'type2_money' => 0, 'type3_money' => 0, 'puids' => [], 'nuids' => []];
                if (!isset($data[0])) {
                    $data[0] = $temp;
                }
                $channel_id = $v['channel_id'];
                if ($channel_id) {
                    if (!isset($data[$channel_id])) {
                        $data[$channel_id] = $temp;
                        $data[$channel_id]['channel_id'] = $channel_id;
                    }
                }
                $agent_id = $v['agent_id'];
                if ($agent_id) {
                    if (!isset($data[$agent_id])) {
                        $data[$agent_id] = $temp;
                        $data[$agent_id]['channel_id'] = $agent_id;
                    }
                }
                if ($v['status'] == 2) {
                    if ($v['package'] > 0) {
                        $data[0]['p_pay'] += 1;
                        $data[0]['p_money'] += $v['money'];
                        if (!in_array($v['uid'], $data[0]['puids'])) {
                            $data[0]['p_user'] += 1;
                            $data[0]['puids'][] = $v['uid'];
                        }
                        if ($channel_id && $v['is_count'] == 1) {
                            $data[$channel_id]['p_pay'] += 1;
                            $data[$channel_id]['p_money'] += $v['money'];
                            if (!in_array($v['uid'], $data[$channel_id]['puids'])) {
                                $data[$channel_id]['p_user'] += 1;
                                $data[$channel_id]['puids'][] = $v['uid'];
                            }
                        }
                        if ($agent_id && $v['is_count'] == 1) {
                            $data[$agent_id]['p_pay'] += 1;
                            $data[$agent_id]['p_money'] += $v['money'];
                            if (!in_array($v['uid'], $data[$agent_id]['puids'])) {
                                $data[$agent_id]['p_user'] += 1;
                                $data[$agent_id]['puids'][] = $v['uid'];
                            }
                        }
                    } else {
                        $data[0]['n_pay'] += 1;
                        $data[0]['n_money'] += $v['money'];
                        if (!in_array($v['uid'], $data[0]['nuids'])) {
                            $data[0]['n_user'] += 1;
                            $data[0]['nuids'][] = $v['uid'];
                        }
                        if ($channel_id && $v['is_count'] == 1) {
                            $data[$channel_id]['n_pay'] += 1;
                            $data[$channel_id]['n_money'] += $v['money'];
                            if (!in_array($v['uid'], $data[$channel_id]['nuids'])) {
                                $data[$channel_id]['n_user'] += 1;
                                $data[$channel_id]['nuids'][] = $v['uid'];
                            }
                        }
                        if ($agent_id && $v['is_count'] == 1) {
                            $data[$agent_id]['n_pay'] += 1;
                            $data[$agent_id]['n_money'] += $v['money'];
                            if (!in_array($v['uid'], $data[$agent_id]['nuids'])) {
                                $data[$agent_id]['n_user'] += 1;
                                $data[$agent_id]['nuids'][] = $v['uid'];
                            }
                        }
                    }
                    $type_key = in_array($v['type'], [1, 2, 3]) ? 'type' . $v['type'] . '_money' : 'type1_money';
                    $data[0][$type_key] += $v['money'];
                    $data[0]['total_money'] += $v['money'];
                    if ($channel_id && $v['is_count'] == 1) {
                        $data[$channel_id]['total_money'] += $v['money'];
                        $data[$channel_id][$type_key] += $v['money'];
                    }
                    if ($agent_id && $v['is_count'] == 1) {
                        $data[$agent_id]['total_money'] += $v['money'];
                        $data[$agent_id][$type_key] += $v['money'];
                    }
                } else {
                    if ($v['package'] > 0) {
                        $data[0]['p_notpay'] += 1;
                        if ($channel_id) {
                            $data[$channel_id]['p_notpay'] += 1;
                        }
                        if ($agent_id) {
                            $data[$agent_id]['p_notpay'] += 1;
                        }
                    } else {
                        $data[0]['n_notpay'] += 1;
                        if ($channel_id) {
                            $data[$channel_id]['n_notpay'] += 1;
                        }
                        if ($agent_id) {
                            $data[$agent_id]['n_notpay'] += 1;
                        }
                    }
                }
            }
        }
        if ($data) {
            foreach ($data as &$val) {
                unset($val['puids']);
                unset($val['nuids']);
                if ($val['n_notpay'] || $val['n_pay']) {
                    $val['n_rate'] = round($val['n_pay'] / ($val['n_pay'] + $val['n_notpay']), 2) * 100;
                }
                if ($val['p_notpay'] || $val['p_pay']) {
                    $val['p_rate'] = round($val['p_pay'] / ($val['p_pay'] + $val['p_notpay']), 2) * 100;
                }
            }
            Db::name('TaskOrder')->insertAll($data);
        }
    }

    //vip超时设置
    public function setVipTime() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        $time = time();
        $over_ids = Db::name('Member')->where('viptime', '>', 1)->where('viptime', '<', $time)->limit(100)->column('id');
        if (!empty($over_ids)) {
            Db::name('Member')->where('id', 'in', $over_ids)->setField('viptime', 0);
            foreach ($over_ids as $v) {
                $key = 'member_info_' . $v;
                cache($key, null);
            }
        }
    }

    //发送客服消息,每分钟执行
    public function sendCustomMessage() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '666';
            exit;
        }
        set_time_limit(1800);
        $start = date('Y-m-d H:i') . ':00';
        $start_time = strtotime($start);
        $end_time = $start_time + 59;
        $list = Db::name('Task')->where('status', '=', 2)->where('send_time', 'between', [$start_time, $end_time])->field('id,channel_id,material,where')->select();
        $temp = [];
        if (!empty($list)) {
            foreach ($list as $v) {
                if ($v['where'] && $v['material']) {
                    $where = json_decode($v['where'], true);
                    $content = json_decode($v['material'], true);
                    if ($where && $content) {
                        $config = null;
                        if (!isset($temp[$v['channel_id']])) {
                            if ($v['channel_id'] > 0) {
                                $channel = myCache::getChannelCache($v['channel_id']);
                                if ($channel && $channel['appid'] && $channel['appsecret']) {
                                    $config = ['appid' => $channel['appid'], 'appsecret' => $channel['appsecret']];
                                }
                            } else {
                                $website = myCache::getSiteWeixinCache();
                                if ($website) {
                                    $config = [
                                        'appid' => $website['appid'],
                                        'appsecret' => $website['appsecret']
                                    ];
                                    $temp[0] = $config;
                                }
                            }
                            $temp[$v['channel_id']] = $config;
                        } else {
                            $config = $temp[$v['channel_id']];
                        }
                        if ($config) {
                            wx::$config = $config;
                            $member = Db::name('Member')->where($where)->field('id,openid')->select();
                            if (!empty($member)) {
                                self::sendToMember($member, $content);
                            }
                            Db::name('Task')->where('id', '=', $v['id'])->setField('status', 1);
                        }
                    }
                }
            }
        }
    }

    //向用户发送消息
    private function sendToMember($member, $content) {
        foreach ($member as $v) {
            wx::sendCustomMessage($v['openid'], $content);
        }
    }

    //首冲提醒
    public function pushFirstCharge() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        set_time_limit(60);
        $task = Db::name('TaskMessage')->where('type', '=', 1)->where('status', '=', 1)->field('id,channel_id,material')->select();
        if (!empty($task)) {
            $temp = $channel_ids = $task_content = [];
            foreach ($task as $tv) {
                $channel_ids[] = $tv['channel_id'];
                $task_content[$tv['channel_id']] = json_decode($tv['material'], true);
            }
            $time = time() - 86400;
            $where = [['a.subscribe', '=', 1], ['a.subscribe_time', '<', $time], ['a.is_charge', '=', 2], ['a.wx_id', 'in', $channel_ids]];
            $list = Db::name('Member a')
                    ->join('push_first_charge b', 'a.id=b.uid', 'left')
                    ->where($where)
                    ->field('a.id,a.wx_id,a.openid,IFNULL(b.id,0) as push_id')
                    ->having('push_id=0')
                    ->limit(100)
                    ->select();
            foreach ($list as $v) {
                $config = null;
                if (!isset($temp[$v['wx_id']])) {
                    if ($v['wx_id'] > 0) {
                        $channel = myCache::getChannelCache($v['wx_id']);
                        if ($channel && $channel['appid'] && $channel['appsecret']) {
                            $config = ['appid' => $channel['appid'], 'appsecret' => $channel['appsecret']];
                        }
                    } else {
                        $website = myCache::getSiteWeixinCache();
                        if ($website) {
                            $config = [
                                'appid' => $website['appid'],
                                'appsecret' => $website['appsecret']
                            ];
                        }
                    }
                    $temp[$v['wx_id']] = $config;
                } else {
                    $config = $temp[$v['wx_id']];
                }
                if ($config) {
                    wx::$config = $config;
                    wx::sendCustomMessage($v['openid'], $task_content[$v['wx_id']]);
                }
                Db::name('PushFirstCharge')->insert(['uid' => $v['id'], 'create_time' => time()]);
            }
        }
    }

    //继续阅读提醒
    public function pushContinueRead() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        set_time_limit(60);
        $task = Db::name('TaskMessage')->where('type', '=', 2)->where('status', '=', 1)->field('id,channel_id')->select();
        if (!empty($task)) {
            $time = time() - 28800;
            $channel_ids = array_column($task, 'channel_id');
            $where = [
                ['a.channel_id', 'in', $channel_ids],
                ['a.is_end', '=', 1],
                ['a.create_time', '<', $time],
                ['a.type', 'between', [1, 2]]
            ];
            $list = Db::name('ReadHistory a')
                    ->join('PushRead b', 'a.id=b.rid', 'left')
                    ->where($where)
                    ->field('a.id,a.uid,IFNULL(b.id,0) as push_id')
                    ->having('push_id=0')
                    ->limit(100)
                    ->select();
            if (!empty($list)) {
                $temp = [];
                foreach ($list as $v) {
                    $cur = Db::name('ReadHistory a')
                            ->join('member b', 'a.uid=b.id and b.subscribe=1')
                            ->join('book c', 'a.book_id=c.id')
                            ->where('a.id', '=', $v['id'])
                            ->field('a.id,a.book_id,a.number,a.channel_id,a.uid,b.openid,c.name,c.cover')
                            ->find();
                    if ($cur) {
                        $config = null;
                        if (!isset($temp[$cur['channel_id']])) {
                            if ($cur['channel_id'] > 0) {
                                $channel = myCache::getChannelCache($cur['channel_id']);
                                if ($channel && $channel['appid'] && $channel['appsecret']) {
                                    $config = ['appid' => $channel['appid'], 'appsecret' => $channel['appsecret'], 'url' => $channel['url']];
                                }
                            } else {
                                $weixin = myCache::getSiteWeixinCache();
                                if ($weixin) {
                                    $website = myCache::getWebSiteCache();
                                    if ($website) {
                                        $config = [
                                            'url' => $website['url'],
                                            'appid' => $weixin['appid'],
                                            'appsecret' => $weixin['appsecret']
                                        ];
                                    }
                                }
                            }
                            $temp[$cur['channel_id']] = $config;
                        } else {
                            $config = $temp[$cur['channel_id']];
                        }
                        if ($config) {
                            $content = [
                                [
                                    'title' => $cur['name'],
                                    'description' => '您已经超过8小时未阅读该书籍了，点我继续阅读吧',
                                    'picurl' => $cur['cover'],
                                    'url' => 'http://' . $config['url'] . '/index/Book/read.html?book_id=' . $cur['book_id'] . '&number=' . $cur['number']
                                ]
                            ];
                            wx::$config = $config;
                            wx::sendCustomMessage($cur['openid'], $content);
                        }
                    }
                    Db::name('PushRead')->insert(['rid' => $v['id'], 'uid' => $v['uid'], 'create_time' => time()]);
                }
            }
        }
    }

    //未支付提醒
    public function pushNotPay() {
        $get = myRequest::get('token');
        
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '666';
            exit;
        }

        set_time_limit(60);
        $task = Db::name('TaskMessage')->where('type', '=', 3)->where('status', '=', 1)->field('id,channel_id')->select();
        if (!empty($task)) {
            $end_time = time() - 1800;
            $start_time = $end_time - 360;
            $channel_ids = array_column($task, 'channel_id');
            $where = [
                ['create_time', 'between', [$start_time, $end_time]],
                ['status', '=', 1],
                ['type', '=', 1],
                ['wx_id', 'in', $channel_ids]
            ];
            $field = 'max(id) as id,uid';
            $list = Db::name('Order')->where($where)->field($field)->group('uid')->limit(100)->order('id', 'desc')->select();
            if ($list) {
            	//print_r($list);
                $temp = [];
                foreach ($list as $v) {
                    $repeat = Db::name('PushNotpay')->where('order_id', '>=', $v['id'])->where('uid', '=', $v['uid'])->value('id');
                    if (!$repeat) {
                        $cur = Db::name('Order a')
                                ->join('member b', 'a.uid=b.id and b.subscribe=1')
                                ->where('a.id', '=', $v['id'])
                                ->field('a.id,a.pay_type,a.pay_url,a.wx_id,a.order_no,b.openid')
                                ->find();
                        if ($cur) {
                            $config = null;
                            if (!isset($temp[$cur['wx_id']])) {
                                if ($cur['wx_id'] > 0) {
                                    $channel = myCache::getChannelCache($cur['wx_id']);
                                    if ($channel && $channel['appid'] && $channel['appsecret']) {
                                        $config = ['appid' => $channel['appid'], 'appsecret' => $channel['appsecret'], 'url' => $channel['url']];
                                    }
                                } else {
                                    $weixin = myCache::getSiteWeixinCache();
                                    if ($weixin) {
                                        $website = myCache::getWebSiteCache();
                                        if ($website) {
                                            $config = [
                                                'url' => $website['url'],
                                                'appid' => $weixin['appid'],
                                                'appsecret' => $weixin['appsecret']
                                            ];
                                        }
                                    }
                                }
                                $temp[$cur['wx_id']] = $config;
                            } else {
                                $config = $temp[$cur['wx_id']];
                            }
                            if ($config) {
                                $html = "书币订单未支付提醒；";
                                $html .= "\n";
                                $html .= "亲，您的充值书币订单还未完成，点我立即支付该笔订单！";
                                $html .= "\n\n";
                                $back_url = 'http://' . $config['url'] . '/index/User/index.html';
                                $back_url = urlencode($back_url);
                                $url = '';
                                if ($cur['pay_type'] == 1) {
                                    $url = "http://" . $config['url'] . "/index/Pay/jsPay.html?table=order&order_no=" . $cur['order_no'] . '&back_url=' . $back_url;
                                } else {
                                    if ($cur['pay_url']) {
                                        $url = $cur['pay_url'];
                                    }
                                }
                                if ($url) {
                                    $a = '<a href="' . $url . '">点击支付></a>';
                                    $html .= $a;
                                    wx::$config = $config;
                                    $re = wx::sendCustomMessage($cur['openid'], $html, 'text');
                                }
                            }
                        }
                        Db::name('PushNotpay')->insert(['order_id' => $v['id'], 'uid' => $v['uid'], 'create_time' => time()]);
                    }
                }
            }
        }
    }

    //猜你喜欢推送
    public function pushLikes() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        set_time_limit(60);
        $task = Db::name('TaskMessage')->where('type', '=', 4)->where('status', '=', 1)->field('id,channel_id')->select();
        if (!empty($task)) {
            $time = time() - 86400;
            $channel_ids = array_column($task, 'channel_id');
            $where = [
                ['a.channel_id', 'in', $channel_ids],
                ['a.is_end', '=', 1],
                ['a.create_time', '<', $time],
            ];
            $list = Db::name('ReadHistory a')
                    ->join('PushLikes b', 'a.id=b.rid', 'left')
                    ->where($where)
                    ->field('a.id,a.uid,IFNULL(b.id,0) as push_id')
                    ->having('push_id=0')
                    ->limit(100)
                    ->select();
            if (!empty($list)) {
                $temp = [];
                foreach ($list as $v) {
                    $cur = Db::name('ReadHistory a')
                            ->join('member b', 'a.uid=b.id and b.subscribe=1')
                            ->join('book c', 'a.book_id=c.id')
                            ->where('a.id', '=', $v['id'])
                            ->field('a.id,a.book_id,a.channel_id,a.uid,b.openid,c.type,c.category')
                            ->find();
                    if ($cur) {
                        $config = null;
                        if (!isset($temp[$cur['channel_id']])) {
                            if ($cur['channel_id'] > 0) {
                                $channel = myCache::getChannelCache($cur['channel_id']);
                                if ($channel && $channel['appid'] && $channel['appsecret']) {
                                    $config = ['appid' => $channel['appid'], 'appsecret' => $channel['appsecret'], 'url' => $channel['url']];
                                }
                            } else {
                                $weixin = myCache::getSiteWeixinCache();
                                if ($weixin) {
                                    $website = myCache::getWebSiteCache();
                                    if ($website) {
                                        $config = [
                                            'url' => $website['url'],
                                            'appid' => $weixin['appid'],
                                            'appsecret' => $weixin['appsecret']
                                        ];
                                    }
                                }
                            }
                            $temp[$cur['channel_id']] = $config;
                        } else {
                            $config = $temp[$cur['channel_id']];
                        }
                        if ($config) {
                            $books = Db::name('Book')
                                    ->where('status', '=', 1)
                                    ->where('category', '=', $cur['category'])
                                    ->where('type', '=', $cur['type'])
                                    ->field('id,name')
                                    ->limit(3)
                                    ->select();
                            if ($books) {
                                $html = "猜您喜欢；";
                                $html .= "\n";
                                $html .= "根据您的喜好，我们为您推荐了您感兴趣的新书！";
                                $html .= "\n\n";
                                foreach ($books as $lv) {
                                    $url = "http://" . $config['url'] . "/index/Book/info.html?book_id=" . $lv['id'];
                                    $a = '➢  <a href="' . $url . '">' . $lv['name'] . '></a>';
                                    $html .= $a;
                                    $html .= "\n\n";
                                }
                                wx::$config = $config;
                                $re = wx::sendCustomMessage($cur['openid'], $html, 'text');
                            }
                        }
                    }
                    Db::name('PushLikes')->insert(['rid' => $v['id'], 'uid' => $v['uid'], 'create_time' => time()]);
                }
            }
        }
    }

    //刷新站点缓存
    public function refreshCache() {
        $get = myRequest::get('token');
        if ($get['token'] !== '62ce842709faac7eb52') {
            echo '';
            exit;
        }
        $block = myCache::getWebblockCache();
        set_time_limit(1800);
        if ($block) {
            foreach ($block as $v) {
                if ($v['is_on'] == 1) {
                    switch ($v['key']) {
                        case 'novel':
                            $variable = mBook::getBlockData(2);
                            $variable['title'] = '小说';
                            $this->assign($variable);
                            $html = $this->fetch('block/bookContent');
                            saveBlock($html, 'novel_content', 'other');
                            $html = $this->fetch('block/ranks');
                            saveBlock($html, 'novel_ranks', 'other');
                            break;
                        case 'cartoon':
                            $variable = mBook::getBlockData(1);
                            $variable['title'] = '漫画';
                            $this->assign($variable);
                            $html = $this->fetch('block/bookContent');
                            saveBlock($html, 'cartoon_content', 'other');
                            $html = $this->fetch('block/ranks');
                            saveBlock($html, 'cartoon_ranks', 'other');
                            break;
                        case 'music':
                            $variable = mBook::getBlockData(3);
                            $variable['title'] = '听书';
                            $this->assign($variable);
                            $html = $this->fetch('block/bookContent');
                            saveBlock($html, 'music_content', 'other');
                            $html = $this->fetch('block/ranks');
                            saveBlock($html, 'music_ranks', 'other');
                            break;
                        case 'video':
                            $variable = mVideo::getBlockData();
                            $this->assign($variable);
                            //此处是视频页广告部分---开始
                            $data1 = myCache::getAdCache('热门视频');
                            $ad1 = '';
                            if (!empty($data1)) {
                                $ad1 .= '<a style="width: 100%;height: 100px" href="' . $data1['url'] . '">';
                                $ad1 .= '<img style="width: 100%;height: 150px" src="' . $data1['img'] . '"> </a>';
                            }
                            $text1 = htmlspecialchars($ad1);
                            $data2 = myCache::getAdCache('热门视频推荐');
                            $ad2 = '';
                            if (!empty($data2)) {
                                $ad2 .= '<a style="width: 100%;height: 100px" href="' . $data2['url'] . '">';
                                $ad2 .= '<img style="width: 100%;height: 150px" src="' . $data2['img'] . '"> </a>';
                            }
                            $text2 = htmlspecialchars($ad2);

                            //此处是视频页广告部分---结束
                            $html = $this->fetch('block/videoContent', [
                                'text1' => $text1,
                                'text2' => $text2,
                            ]);
                            saveBlock($html, 'video_content', 'other');
                            break;
                        case 'product':
                            $variable = mProduct::getBlockData();
                            $this->assign($variable);
                            //此处是视频页广告部分---开始
                            $data1 = myCache::getAdCache('热卖商品');
                            $ad1 = '';
                            if (!empty($data1)) {
                                $ad1 .= '<a style="width: 100%;height: 100px" href="' . $data1['url'] . '">';
                                $ad1 .= '<img style="width: 100%;height: 150px" src="' . $data1['img'] . '"> </a>';
                            }
                            $text1 = htmlspecialchars($ad1);

                            $data2 = myCache::getAdCache('今日推荐商品');
                            $ad2 = '';
                            if (!empty($data2)) {
                                $ad2 .= '<a style="width: 100%;height: 100px" href="' . $data2['url'] . '">';
                                $ad2 .= '<img style="width: 100%;height: 150px" src="' . $data2['img'] . '"> </a>';
                            }
                            $text2 = htmlspecialchars($ad2);

                            $data3 = myCache::getAdCache('热门推荐商品');
                            $ad3 = '';
                            if (!empty($data3)) {
                                $ad3 .= '<a style="width: 100%;height: 100px" href="' . $data3['url'] . '">';
                                $ad3 .= '<img style="width: 100%;height: 150px" src="' . $data3['img'] . '"> </a>';
                            }
                            $text3 = htmlspecialchars($ad3);
                            //此处是商城页广告部分---结束

                            $html = $this->fetch('block/productContent', [
                                'text1' => $text1,
                                'text2' => $text2,
                                'text3' => $text3,
                            ]);
                            saveBlock($html, 'product_content', 'other');
                            break;
                    }
                }
            }
        }
    }

}
