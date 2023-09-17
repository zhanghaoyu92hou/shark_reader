<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\admin\model\mConfig;
use app\admin\model\mVideo;
use app\common\model\myCache;

class Video extends Common {

    //视频列表
    public function index() {
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['status', 'between', [1, 2]]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mVideo::getPageList('Video', $where, '*', $pages);
            if ($res['data']) {
                foreach ($res['data'] as &$v) {
                    $v['create_time'] = date('Y-m-d H:i', $v['create_time']);
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '已下架';
                    $v['do_url'] = my_url('doVideo', ['id' => $v['id']]);
                    $v['customer_url'] = my_url('Message/addTask', ['video_id' => $v['id']]);
                    $v['play_url'] = my_url('doPlay', ['id' => $v['id']]);
                    $v['copy_url'] = my_url('copyLink', ['id' => $v['id']]);
                    $v['free_str'] = '免费';
                    if ($v['free_type'] == 2) {
                        $v['free_str'] = '收费:' . $v['money'] . '书币';
                    }
                }
            }
            res_return('ok', $res['data'], $res['count']);
        } else {

            return $this->fetch();
        }
    }

    //复制链接
    public function copyLink() {
        $id = myRequest::getId('视频');
        $cur = mVideo::getById('Video', $id, 'id,name');
        if (!$cur) {
            res_return('视频参数错误');
        }
        $config = mConfig::getConfig('website');
        if (!array_key_exists('url', $config) || !$config['url']) {
            res_return('您尚未配置站点url');
        }
        $url = 'http://';
        if ($config['is_location'] == 1 && $config['location_url']) {
            $url .= $config['location_url'];
        } else {
            $url .= $config['url'];
        }

        $short_url = '/Index/Video/info.html?video_id=' . $id;
        $data = [
            'notice' => '温馨提示 : 相对链接只能应用到页面跳转链接中，如轮播图链接等，渠道用户点击后不会跳转到总站',
            'links' => [
                ['title' => '相对链接', 'val' => $short_url],
                ['title' => '绝对链接', 'val' => $url . $short_url]
            ]
        ];
        $this->assign('data', $data);
        return $this->fetch('public/copyLink');
    }

    //新增视频
    public function addVideo() {
        if ($this->request->isAjax()) {
            $field = 'name,cover,summary,money,status,free_type,url,is_hot,zan,cai';
            $field .= ',hot_num,share_title,share_desc,sort_num,area,category';
            $data = myValidate::getData(mVideo::$rules, $field);
            if ($data['category']) {
                $data['category'] = ',' . implode(',', $data['category']) . ',';
            }
            if ($data['area']) {
                $data['area'] = ',' . implode(',', $data['area']) . ',';
            }
            $data['create_time'] = time();
            $re = mVideo::add('Video', $data);
            if ($re) {
                res_return();
            } else {
                res_return('新增失败，请重试');
            }
        } else {
            $field = 'id,name,cover,summary,money:158,status,free_type:2,url,is_hot,zan,cai';
            $field .= ',hot_num:0,share_title,share_desc,sort_num:0,area,category';
            $option = mVideo::getVideoRadioList();
            $option['cur'] = mVideo::buildArr($field);
            $option['category'] = mConfig::getConfig('video_category');
            $option['area'] = mConfig::getConfig('video_area');
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('doVideo');
        }
    }

    //编辑视频
    public function doVideo() {
        if ($this->request->isAjax()) {
            $field = 'id,name,cover,summary,money,status,free_type,url,is_hot,zan,cai';
            $field .= ',hot_num,share_title,share_desc,sort_num,area,category';
            $data = myValidate::getData(mVideo::$rules, $field);
            if ($data['category']) {
                $data['category'] = ',' . implode(',', $data['category']) . ',';
            }
            if ($data['area']) {
                $data['area'] = ',' . implode(',', $data['area']) . ',';
            }
            $re = mVideo::saveIdData('Video', $data);
            if ($re) {
                cache('video_' . $data['id'], null);
                res_return();
            } else {
                res_return('编辑失败，请重试');
            }
        } else {
            $id = myRequest::getId('视频');
            $cur = mVideo::getById('Video', $id);
            if (!$cur) {
                res_return('视频不存在');
            }
            $option = mVideo::getVideoRadioList();
            $option['category'] = mConfig::getConfig('video_category');
            $option['area'] = mConfig::getConfig('video_area');
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('doVideo');
        }
    }

    //处理视频事件
    public function doVideoEvent() {
        $field = 'id,event';
        $data = myValidate::getData(mVideo::$rules, $field);
        if (in_array($data['event'], ['on', 'off'])) {
            switch ($data['event']) {
                case 'on':
                    $status = 1;
                    break;
                case 'off':
                    $status = 2;
                    break;
            }
            $re = mVideo::setField('Video', [['id', '=', $data['id']]], 'status', $status);
            if ($re) {
                res_return();
            } else {
                res_return('操作失败');
            }
        } else {
            if ($data['event'] === 'delete') {
                $re = mVideo::delete($data['id']);
                if ($re) {
                    res_return();
                } else {
                    res_return('操作失败,请重试');
                }
            }
        }
    }

    //处理视频播放
    public function doPlay() {
        $id = myRequest::getId('视频');
        $cur = mVideo::getById('Video', $id, 'id,url');
        if (!$cur) {
            res_return('视频不存在');
        }
        if (!$cur['url']) {
            res_return('视频链接未配置');
        }
        $this->assign('cur', $cur);
        return $this->fetch('doPlay');
    }

    //发布区域配置
    public function area() {
        $key = 'video_area';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'area');
            $res = mConfig::saveConfig($key, $data);
            if ($res) {
                if ($data) {
                    cache($key, $data, 3600);
                } else {
                    cache($key, null);
                }
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
            $variable = [
                'title' => '视频发布区域配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/area');
        }
    }

    //轮播图片配置
    public function banners() {
        $key = 'video_banner';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'src,link');
            $config = [];
            if ($data['src']) {
                $num = 0;
                foreach ($data['src'] as $k => $v) {
                    $num++;
                    $one = ['src' => $v];
                    if (!$one['src']) {
                        res_return('第' . $num . '张轮播图片未上传');
                    }
                    $one['link'] = $data['link'][$k];
                    $config[] = $one;
                }
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                $this->assign('list', $config);
                $html = $this->fetch('block/banners');
                saveBlock($html, $key, 'other');
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
            $variable = [
                'title' => '视频轮播图配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/banners');
        }
    }

    //类型配置
    public function category() {
        $key = 'video_category';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'category');
            $data = $data ?: [];
            $res = mConfig::saveConfig($key, $data);
            if ($res) {
                if ($data) {
                    cache($key, $data, 3600);
                } else {
                    cache($key, null);
                }
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
            $variable = [
                'title' => '视频类型配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/category');
        }
    }

    //底部导航
    public function footer() {
        $key = 'video_footer';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'src,text,link');
            $config = [];
            if ($data['src']) {
                $num = 0;
                foreach ($data['src'] as $k => $v) {
                    $num++;
                    $one = ['src' => $v];
                    if (!$one['src']) {
                        res_return('第' . $num . '张图标未上传');
                    }
                    $one['link'] = $data['link'][$k];
                    $one['text'] = $data['text'][$k];
                    $config[] = $one;
                }
                if ($num > 5) {
                    res_return('最多上传5个底部导航');
                }
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                $this->assign('list', $config);
                $html = $this->fetch('block/footer');
                saveBlock($html, 'video_footer', 'other');
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
            $variable = [
                'title' => '视频底部导航配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }

    //菜单导航
    public function nav() {
        $key = 'video_nav';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'text,link');
            $config = [];
            if ($data['text']) {
                $num = 0;
                foreach ($data['text'] as $k => $v) {
                    $num++;
                    $one = ['text' => $v];
                    if (!$one['text']) {
                        res_return('第' . $num . '条标题未填写');
                    }
                    $one['link'] = $data['link'][$k];
                    $config[] = $one;
                }
                if ($num > 5) {
                    res_return('最多上传5个菜单导航');
                }
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                $this->assign('list', $config);
                $html = $this->fetch('block/videoNav');
                saveBlock($html, $key, 'other');
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
            $variable = [
                'title' => '视频导航菜单配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch();
        }
    }

    //刷新缓存
    public function refreshCache() {
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
        $this->assign($text1);
        $this->assign($text2);
        //此处是视频页广告部分---结束
        $html = $this->fetch('block/videoContent', [
            'text1' => $text1,
            'text2' => $text2,
        ]);


        saveBlock($html, 'video_content', 'other');
        res_return();
    }

}
