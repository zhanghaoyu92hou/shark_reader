<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use app\common\model\myValidate;
use app\admin\model\mConfig;
use app\admin\model\mPlatform;

class Website extends Common {

    //网址基础信息
    public function info() {
        $key = 'website';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $field = 'name,url,is_location,location_url,is_sign,pay_type,share_money,contactQQ,contactWx,contactTel';
            $field .= ',sign_day1,sign_day2,sign_day3,sign_day4,sign_day5,sign_day6,sign_day7';
            $data = myValidate::getData(mConfig::$rules, $field);
            if ($data['url'] == $data['location_url']) {
                res_return('绑定域名不能和跳转域名一致');
            }
            mPlatform::checkUrlRepeat($data['url'], 0, true);
            if ($data['location_url']) {
                mPlatform::checkUrlRepeat($data['location_url'], 0, true);
            }
            $data['sign_config'] = [];

            for ($i = 1; $i <= 7; $i++) {
                $dkey = 'sign_day' . $i;
                $skey = 'day' . $i;
                $data['sign_config'][$skey] = $data[$dkey];
                unset($data[$dkey]);
            }
            $res = mConfig::saveConfig($key, $data);
            if ($res) {
                cache($key, $data);
                if ($cur['url']) {
                    cache(md5($cur['url']), null);
                }
                if ($cur['location_url']) {
                    cache(md5($cur['location_url']), null);
                }
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $field = 'name,url,is_location:2,location_url,is_sign:2,pay_type:1,weixin,qq,sign_money,share_money,contactQQ,contactWx,contactTel';
                $cur = mConfig::buildArr($field);
                $cur['sign_config'] = ['day1' => 0, 'day2' => 0, 'day3' => 0, 'day4' => 0, 'day5' => 0, 'day6' => 0, 'day7' => 0];
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $sign = [
                'name' => 'is_sign',
                'option' => [['val' => 1, 'text' => '开启', 'default' => 1], ['val' => 2, 'text' => '关闭', 'default' => 0]]
            ];
            $is_location = [
                'name' => 'is_location',
                'option' => [['val' => 1, 'text' => '开启', 'default' => 1], ['val' => 2, 'text' => '关闭', 'default' => 0]]
            ];
            $pay_type = [
                'name' => 'pay_type',
                'option' => [['val' => 1, 'text' => '微信支付(公众号)', 'default' => 1], ['val' => 5, 'text' => '易支付', 'default' => 0]]
            ];
            $variable = [
                'cur' => $cur,
                'sign' => $sign,
                'pay_type' => $pay_type,
                'is_location' => $is_location
            ];
            $this->assign($variable);
            return $this->fetch();
        }
    }

    //打赏金额配置
    public function reward() {
        $key = 'reward';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$rules, 'reward_money');
            foreach ($data as $v) {
                if (!is_numeric($v) || $v <= 0) {
                    res_return('打赏金额必须数值类型');
                }
            }
            $res = mConfig::saveConfig($key, $data);
            if ($res) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            $cur = mConfig::getConfig($key);
            if ($cur === false) {
                $cur = [];
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }

    //充值金额配置
    public function charge() {
        $key = 'charge';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$charge, 'money,reward,coin,is_hot,package,is_on,is_checked');
            if (!$data['money']) {
                res_return('未检测到上传数据');
            }
            $config = [];
            $is_check = 0;
            foreach ($data['money'] as $k => $v) {
                $type = 0;
                $one = ['money' => $v];
                $one['package'] = $data['package'][$k];
                if (!in_array($one['package'], [0, 1, 2, 3, 4, 5, 6])) {
                    res_return('套餐数据选择错误');
                }
                $one['reward'] = $data['reward'][$k];
                if ($one['reward'] && (!is_numeric($one['reward']) || $one['reward'] < 0)) {
                    res_return('赠送书币必须大于等于0的数字');
                }
                $one['is_hot'] = $data['is_hot'][$k];
                if (!in_array($one['is_hot'], [0, 1, 2])) {
                    res_return('是否热门参数错误');
                }
                if ($one['package'] > 0) {
                    $one['coin'] = 0;
                } else {
                    $one['coin'] = floor($data['coin'][$k]);
                    if (!is_numeric($one['coin']) || $one['coin'] < 0) {
                        res_return('充值书币必须为数值且必须大于0');
                    }
                }
                $one['is_on'] = $data['is_on'][$k];
                if (!in_array($one['is_on'], [0, 1, 2])) {
                    res_return('是否启用参数错误');
                }
                $one['is_checked'] = $data['is_checked'][$k];
                if ($one['is_checked'] == 1) {
                    if ($is_check == 0) {
                        $is_check = 1;
                    } else {
                        res_return('是否选中只能选择一行');
                    }
                }
                $config[] = $one;
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                cache($key, $config);
                $this->assign('list', $config);
                $html = $this->fetch('block/charge');
                saveBlock($html, 'charge_config', 'other');
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            $cur = mConfig::getConfig($key);
            if ($cur === false) {
                $cur = [];
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }

    //支付配置
    public function pay() {
        $key = 'wxpay';
        $field = 'APPID,MCHID,APIKEY';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$pay, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }

    //支付配置
    public function milabaoPay() {
        $key = 'milabaoPay';
        $field = 'appid,key,gateway';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $rules = [
            ];
            $data = myValidate::getData($rules, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch('milabaoPay');
        }
    }

    //支付配置
    public function mihuaPay() {
        $key = 'mihuaPay';
        $field = 'merAccount,merNo,privateKey,publicKey';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $rules = [
                'merAccount' => ['require', ['require' => '请输入米花支付商户标识']],
                'merNo' => ['require', ['require' => '请输入米花支付商户编号']],
                'privateKey' => ['require', ['require' => '请输入米花支付私钥']],
                'publicKey' => ['require', ['require' => '请输入米花支付公钥']],
            ];
            $data = myValidate::getData($rules, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch('mihuaPay');
        }
    }

    //支付猫配置
    public function payCat() {
        $key = 'paycat';
        $field = 'uid,token,gateway';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $rules = [
                'uid' => ['require', ['require' => '请输入支付猫商户uid']],
                'token' => ['require', ['require' => '请输入支付猫商户Token']],
                  'gateway' => ['require', ['require' => '请输入支付猫网关']],
            ];
            $data = myValidate::getData($rules, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch('paycat');
        }
    }
    
    //易支付配置
    public function epay() {
        $key = 'epay';
        $field = 'epayid,epaykey,epayurl';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $rules = [
                'epayid' => ['require', ['require' => '请输入易支付商户id']],
                'epaykey' => ['require', ['require' => '请输入易支付商户Key']],
                  'epayurl' => ['require', ['require' => '请输入易支付网关']],
            ];
            $data = myValidate::getData($rules, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch('epay');
        }
    }

    //短信接口配置
    public function message() {
        $key = 'message';
        $field = 'appid,appkey,sign,content';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$message, $field);
            if (strpos($data['content'], 'CODE') === false) {
                res_return('短信内容CODE变量不存在');
            }
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }

    //阿里云oss配置
    public function alioss() {
        $key = 'alioss';
        $field = 'accessKey,secretKey,bucket,url,type';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $type = input('post.type');
            $rule = mConfig::$alioss;
            if ($type == 1) {
                $rule = [];
            }
            $data = myValidate::getData($rule, $field);
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data, 3600);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);

                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            } else {
                if (!isset($cur['type'])) {
                    $cur['type'] = 0;
                }
            }
            $option = [
                'type' => [
                    'name' => 'type',
                    'option' => [['val' => 0, 'text' => '阿里OSS存储', 'default' => 0], ['val' => 1, 'text' => '本地存储', 'default' => 1]]
            ]];
            $option['cur'] = $cur;

            $option['backUrl'] = my_url('index');

            $this->assign($option);
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }

    //小说采集设置
    public function xsplus() {
        $key = 'message';
        $field = 'appid,appkey,sign,content';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$message, $field);
            if (strpos($data['content'], 'CODE') === false) {
                res_return('短信内容CODE变量不存在');
            }
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }
    
    //漫画采集设置
    public function mhplus() {
        $key = 'message';
        $field = 'appid,appkey,sign,content';
        $cur = mConfig::getConfig($key);
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$message, $field);
            if (strpos($data['content'], 'CODE') === false) {
                res_return('短信内容CODE变量不存在');
            }
            $re = mConfig::saveConfig($key, $data);
            if ($re) {
                cache($key, $data);
                res_return();
            } else {
                res_return('保存失败');
            }
        } else {
            if (!$cur) {
                $cur = mConfig::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if (!$re) {
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur', $cur);
            return $this->fetch();
        }
    }
}
