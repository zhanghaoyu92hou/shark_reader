<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\admin\model\mProduct;
use app\admin\model\mConfig;
use think\Db;
use app\common\model\myCache;

class Product extends Common {

    //商品列表
    public function index() {
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['a.status', 'between', [1, 2]]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mProduct::getProductList($where, $pages);
            $time = time();
            if ($res['data']) {
                foreach ($res['data'] as &$v) {
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '已下架';
                    $v['do_url'] = my_url('doProduct', ['id' => $v['id']]);
                    $v['copy_url'] = my_url('copyLink', ['id' => $v['id']]);
                }
            }
            res_return('ok', $res['data'], $res['count']);
        } else {

            return $this->fetch();
        }
    }

    //复制链接
    public function copyLink() {
        $id = myRequest::getId('商品');
        $book = mProduct::getById('Product', $id, 'id,name');
        if (!$book) {
            res_return('商品不存在');
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

        $short_url = '/Index/Product/info.html?pid=' . $id;
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

    //新增商品
    public function addProduct() {
        if ($this->request->isAjax()) {
            $field = 'name,money,cover,summary,content,status,area,category,sort_num,stock,is_hot,hot_num,share_title,share_desc,buy_num';
            mProduct::doneProduct($field);
        } else {
            $field = 'id,name,money,cover,summary,content,status,area,category,sort_num,stock,is_hot,hot_num,share_title,share_desc,buy_num';
            $option = mProduct::getProductRadioList();
            $option['cur'] = mProduct::buildArr($field);
            $option['category'] = mConfig::getConfig('product_category');
            $option['area'] = mConfig::getConfig('product_area');
            $option['backUrl'] = my_url('index');
            $cur['buy_num']=0;
        	$this->assign($cur);
            $this->assign($option);
            return $this->fetch('doProduct');
        }
    }

    //编辑商品
    public function doProduct() {
        if ($this->request->isAjax()) {
            $field = 'id,name,money,cover,summary,content,status,area,category,sort_num,stock,is_hot,hot_num,share_title,share_desc,buy_num';
            mProduct::doneProduct($field);
        } else {
            $id = myRequest::getId('商品');
            $cur = mProduct::getById('Product', $id);
            if (!$cur) {
                res_return('商品不存在');
            }
            $cur['content'] = getBlockContent($id, 'product');
            $option = mProduct::getProductRadioList();
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('index');
            $option['category'] = mConfig::getConfig('product_category');
            $option['area'] = mConfig::getConfig('product_area');

            $this->assign($option);
            return $this->fetch('doProduct');
        }
    }

    //处理活动事件
    public function doProductEvent() {
        $field = 'id,event';
        $data = myValidate::getData(mProduct::$rules, $field);
        switch ($data['event']) {
            case 'on':
                $status = 1;
                break;
            case 'off':
                $status = 2;
                break;
            case 'delete':
                $status = 3;
                break;
            default:
                res_return('未指定该事件');
                break;
        }
        $re = mProduct::setField('Product', [['id', '=', $data['id']]], 'status', $status);
        if ($re) {
            res_return();
        } else {
            res_return('操作失败');
        }
    }

    //发布区域配置
    public function area() {
        $key = 'product_area';
        if ($this->request->isAjax()) {
            $data = myValidate::getData(mConfig::$icon_rules, 'area');
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
                'title' => '商品发布区域配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/area');
        }
    }

    //轮播图片配置
    public function banners() {
        $key = 'product_banner';
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
                'title' => '商品轮播图配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/banners');
        }
    }

    //类型配置
    public function category() {
        $key = 'product_category';
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
                'title' => '商品类型配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/category');
        }
    }

    //底部导航
    public function footer() {
        $key = 'product_footer';
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
                    res_return('最多可配置5个底部导航');
                }
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                $this->assign('list', $config);
                $html = $this->fetch('block/footer');
                saveBlock($html, 'product_footer', 'other');
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
                'title' => '商品底部导航配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }

    //菜单导航
    public function nav() {
        $key = 'product_nav';
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
                    res_return('最多可配置5个菜单导航图标');
                }
            }
            $res = mConfig::saveConfig($key, $config);
            if ($res) {
                $this->assign('list', $config);
                $html = $this->fetch('block/circleIcon');
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
                'title' => '商品导航菜单配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }

    //刷新缓存
    public function refreshCache() {
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
        res_return();
    }

}
