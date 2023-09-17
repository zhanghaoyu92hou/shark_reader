<?php

namespace app\common\model;

use think\Db;

class myCache {

    //获取站点信息缓存
    public static function getWebSiteCache() {
        $key = 'website';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['url']) && $config['url']) {
                $data = $config;
                cache($key, $data, 3600);
            }
        }
        return $data;
    }

    //获取打赏配置缓存
    public static function getRewardCache() {
        $key = 'reward';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (!empty($config)) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }

    //获取配置缓存 发布区域，分类
    public static function getBookConfigCache($key) {
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if ($config) {
                $data = $config;
                cache($key, $data, 3600);
            }
        }
        return $data;
    }

    //获取充值配置缓存
    public static function getChargeCache() {
        $key = 'charge';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if ($config) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }

    //获取微信支付缓存
    public static function getWxPayCache() {
        $key = 'wxpay';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['APPID']) && $config['APPID']) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }

    //获取米花支付缓存
    public static function getmihuaPayCache() {
        $key = 'mihuaPay';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['merAccount']) && $config['merAccount']) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }

    //获取milabao支付缓存
    public static function getmilabaoPayCache() {
        $key = 'milabaoPay';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['appid']) && $config['appid']) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }
    //获取支付猫支付缓存
    public static function getPayCatCache() {
        $key = 'paycat';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['uid']) && $config['uid']) {
                $data = $config;
                cache($key, $data,3600);
            }
        }
        return $data;
    }
    //获取总站微信公众号配置缓存
    public static function getSiteWeixinCache() {
        $key = 'weixin';
        
        $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['appid']) && $config['appid']) {
                $data = $config;
                cache($key, $data);
            }
            
        /*
        $data = cache($key);
        if ($data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['appid']) && $config['appid']) {
                $data = $config;
                cache($key, $data);
            }
        }*/
        return $config;
    }

    //获取阿里云oss缓存配置
    public static function getAliossCache() {
        $key = 'alioss';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
         //   if (isset($config['accessKey']) && $config['accessKey']) {
                $data = $config;
                cache($key, $data, 3600);
        //    }
        }
        return $data;
    }

    //获取发送短信缓存
    public static function getMessageCache() {
        $key = 'message';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $config = json_decode($config, true);
            }
            if (isset($config['appid']) && $config['appkey']) {
                $data = $config;
                cache($key, $data);
            }
        }
        return $data;
    }

    //获取网站功能缓存
    public static function getWebblockCache() {
        $key = 'web_block';
        $data = cache($key);
        if (!$data) {
            $config = Db::name('Config')->where('key', '=', $key)->value('value');
            if ($config) {
                $data = json_decode($config, true);
                cache($key, $data,86400);
            }
        }
        return $data;
    }

    //获取缓存的url信息
    public static function getUrlCache($server_url = null) {
        $server_url = $server_url ?: $_SERVER['HTTP_HOST'];
        $key = md5($server_url);

        $data = cache($key);
        if (!isset($data['wx_id'])) {
            $data = null;
        }
        
        $data = null;
        
        if (!$data) {
            $site = self::getWebSiteCache();
            if (!$site) {
                res_return('尚未配置站点信息');
            }
            $is_site = false;
            $is_wx = 2;
            if ($server_url === $site['url']) {
                // $is_wx = 1;
                if(!empty($channel['appid']) && !empty($channel['appsecret'])){
                    $is_wx = 1;
                }
                $is_site = true;
            } else {
                if ($site['location_url'] && $site['location_url'] === $server_url) {
                    if ($site['is_location'] != 1) {
                        res_return('该域名尚未启用1');
                    }
                    $is_site = true;
                }
            }
            $data = [
                'name' => $site['name'],
                'qrcode' => '',
                'appid' => '',
                'appsecret' => '',
                'apptoken' => '',
                'is_wx' => $is_wx,
                'url' => $site['url'],
                'is_location' => $site['is_location'],
                'location_url' => $site['location_url'],
                'wx_id' => 0,
                'channel_id' => 0,
                'agent_id' => 0
            ];
            
            if ($is_site) {
                $weixin = self::getSiteWeixinCache();
                if (!$weixin) {
                    res_return('尚未配置微信公众号信息');
                }
                $data = array_merge($data, $weixin);
            } else {
                $field = 'id,name,type,is_wx,parent_id,url,is_location,location_url,qrcode,appid,appsecret,apptoken,status';
                $channel = Db::name('Channel')->where('url|location_url', '=', $server_url)->field($field)->find();
                if ($channel['url'] !== $server_url) {
                    if ($channel['is_location'] != 1) {
                        res_return('该域名尚未启用2');
                    }
                }
                if ($channel['type'] == 1) {
                 if (!empty($channel['appid']) && !empty($channel['appsecret'])){
                    $channel['is_wx'] = 1;
                 }else {
                     $channel['is_wx'] = 2;
                 }
                  $data['is_wx'] = 2;
                if ($channel['is_wx'] == 1) {
                    if ($channel['url'] === $server_url) {
                        $data['is_wx'] = 1;          
                    }
                        $data['name'] = $channel['name'];
                        $data['qrcode'] = $channel['qrcode'];
                        $data['appid'] = $channel['appid'];
                        $data['appsecret'] = $channel['appsecret'];
                        $data['apptoken'] = $channel['apptoken'];
                        $data['channel_id'] = $channel['id'];
                        $data['wx_id'] = $channel['id'];
                        $data['url'] = $channel['url'];
                        $data['is_location'] = $channel['is_location'];
                        $data['location_url'] = $channel['location_url'];
                    } else {
                        $weixin = self::getSiteWeixinCache();
                        if (!$weixin) {
                            res_return('尚未配置公众号信息');
                        }
                        $data = array_merge($data, $weixin);
                        if(!empty($channel['qrcode'])){
                            $data['qrcode'] = $channel['qrcode'];
                        }
                        // echo '<pre>';print_r($weixin);exit;
                        $data['is_location'] = 1;
                        $data['location_url'] = $channel['url'];
                        $data['channel_id'] = $channel['id'];
                    }
                } else {
                    $parent = self::getChannelCache($channel['parent_id']);
                    if (!$parent) {
                        res_return('渠道信息异常');
                    }
                    $data['is_location'] = 1;
                    $weixin = self::getSiteWeixinCache();
                    $parent['is_wx'] = 2;
                    if (!empty($parent['appid'])) {
                        $parent['is_wx'] = 1;
                        if (!$parent['appid'] && !$parent['appsecret']) {
                            res_return('未配置公众号信息');
                        }
                        
                        $data['name'] = $parent['name'];
                        $data['qrcode'] = $parent['qrcode'];
                        $data['appid'] = $parent['appid'];
                        $data['appsecret'] = $parent['appsecret'];
                        $data['apptoken'] = $parent['apptoken'];
                        $data['channel_id'] = $parent['id'];
                        $data['agent_id'] = $channel['id'];
                        $data['url'] = $parent['url'];
                        $data['wx_id'] = $parent['id'];
                        $data['location_url'] = $channel['url'];
                    }else {
                        $weixin = self::getSiteWeixinCache();
                        if (!$weixin) {
                            res_return('尚未配置公众号信息');
                        }
                        $data = array_merge($data, $weixin);
                        $data['channel_id'] = $parent['id'];
                        $data['agent_id'] = $channel['id'];
                        $data['location_url'] = $channel['url'];
                         $data['qrcode'] = $parent['qrcode'];
                    }
                }
            }
            cache($key, $data, 86400);
        }
        // $cfg = Db::name('config')->where(['key'=>'weixin'])->find();
        // $val = @json_decode($cfg['value'],true);
        // $data['qrcode'] = $val['qrcode'];
        //print_r($data);exit;
        
        return $data;
    }

    //获取渠道缓存
    public static function getChannelCache($channel_id) {
        $key = 'channel_info_' . $channel_id;
        $data = cache($key);
        if (!$data) {
            $field = 'id,name,type,is_wx,parent_id,url,is_location,location_url,appid,apptoken,appsecret,qrcode,deduct_min,deduct_num,wefare_days,ratio';
            $channel = Db::name('Channel')->where('id', '=', $channel_id)->where('status', '=', 1)->field($field)->find();
            if ($channel) {
                $data = $channel;
                cache($key, $data,3600);
            }
        }
        return $data;
    }

    //获取用户缓存
    public static function getUserCache($uid) {
        $key = 'member_info_' . $uid;
        $data = cache($key);
        if (!isset($data['wx_id'])) {
            $data = null;
        }
        if (!$data) {
            $member = Db::name('Member')->where('id', '=', $uid)->find();
            if ($member) {
                if ($member['status'] != 1) {
                    res_return('用户信息异常，请联系客服');
                }
                $data = $member;
                cache($key, $data, 86400);
            }
        }
        return $data;
    }

    //根据openid获取用户缓存
    public static function getUserByOpenId($openid) {
        $user = '';
        if ($openid) {
            $key = $openid . '_userid';
            $uid = cache($key);
            if ($uid) {
                $user = self::getUserCache($uid);
                if (!$user) {
                    cache($key, null);
                }
            } else {
                $user = Db::name('Member')->where('openid', '=', $openid)->find();
                if ($user) {
                    if ($user['status'] != 1) {
                        res_return('用户信息异常，请联系客服');
                    }
                    cache($key, $user['id'], 86400);
                }
            }
        }
        return $user;
    }

    //获取书籍缓存
    public static function getBookCache($book_id) {
        $key = 'book_' . $book_id;

        $data = cache($key);

        if (!$data) {
            $data = Db::name('Book a')
                    ->join('book_chapter b', 'a.id=b.book_id', 'left')
                    ->where('a.id', '=', $book_id)
                    ->where('a.status', '=', 1)
                    ->field('a.*,IFNULL(max(b.number),0) as total_chapter')
                    ->group('a.id')
                    ->find();
            if ($data) {
                $data['cover'] = $data['cover'] ?: '/static/templet/default/cover.png';
                $data['detail_img'] = $data['detail_img'] ?: '/static/templet/default/detail_img.png';
                $data['summary'] = $data['summary'] ?: '暂无简介';
                cache($key, $data,3600);
            }
        }
        return $data;
    }

    //获取书籍缓存
    public static function getAdCache($name) {
        $key = 'ad_' . $name;

        $data = cache($key);
        if (!$data) {
            $data = Db::name('ad a')
                    ->join('ad_cate c', 'a.id=c.cid', 'left')
                    ->where('a.name', '=', $name)
                    ->where('a.status', '=', 1)
                    ->where('c.status', '=', 1)
                    ->field('c.*')
                    ->order('c.sort DESC')
                    ->find();
            if ($data) {
                $data['img'] = $data['img'] ?: '/static/templet/default/detail_img.png';
                cache($key, $data,3600);
            }
        }
        return $data;
    }

    //获取视频缓存
    public static function getVideoCache($video_id) {
        $key = 'video_' . $video_id;
        $data = cache($key);

        if (!$data) {
            $data = Db::name('video')->where('id', '=', $video_id)->where('status', '=', 1)->find();
            if ($data) {
                $data['cover'] = $data['cover'] ?: '/static/templet/default/cover.png';
                $data['summary'] = $data['summary'] ?: '暂无简介';
                cache($key, $data, 3600);
            }
        }
        return $data;
    }

    //获取活动缓存
    public static function getActivityCache($id) {
        $key = 'activity_' . $id;
        $data = cache($key);
        $time = time();
        if (!$data) {
            $data = Db::name('Activity')->where('id', '=', $id)->where('status', '=', 1)->find();
            if ($data) {
                cache($key, $data, 86400);
            }
        }
        return $data;
    }

    //获取最新一条活动缓存
    public static function getNearActivityCache() {
        $key = 'near_activity';
        $data = cache($key);
        $time = time();
        if ($data) {
            if ($data['start_time'] > $time || $data['end_time'] < $time) {
                cache($key, null);
                $data = null;
            }
        }
        if (!$data) {
            $data = Db::name('Activity')
                    ->where('status', '=', 1)
                    ->where('start_time', '<', $time)
                    ->where('end_time', '>', $time)
                    ->order('id', 'desc')
                    ->find();
            if ($data) {
                cache($key, $data, 86400);
            }
        }
        return $data;
    }

    //获取商品缓存
    public static function getProductCache($id) {
        $key = 'product_' . $id;
        $data = cache($key);
        if (!$data) {
            $data = Db::name('product')
                    ->where('id', '=', $id)
                    ->where('status', '=', 1)
                    ->find();
            if ($data) {
                cache($key, $data,3600);
            }
        }
        return $data;
    }

    //创建登陆code
    public static function createLoginCode() {
        $key = md5('login_' . microtime() . mt_rand(100000, 999999));
        $data = cache($key);
        if ($data) {
            self::createLoginCode();
        } else {
            cache($key, 'wait', 1800);
            return $key;
        }
    }

    //获取当前推广缓存
    public static function getCurSpreadCache() {
        $data = null;
        $spread_id = session('CUR_SPREAD_ID');
        if ($spread_id) {
            $data = self::getSPreadCache($spread_id);
        }
        return $data;
    }

    //获取推广链接缓存
    public static function getSPreadCache($id) {
        $data = null;
        $key = 'spread_' . $id;
        $data = cache($key);
        if ($data) {
            if (!isset($data['id'])) {
                $data['id'] = $id;
                cache($key, $data, 86400);
            }
        } else {
            $data = Db::name('Spread')->where('id', '=', $id)->where('status', '=', 1)->field('id,channel_id,book_id,is_sub,number')->find();
            if ($data) {
                cache($key, $data, 86400);
            }
        }
        return $data;
    }

}
