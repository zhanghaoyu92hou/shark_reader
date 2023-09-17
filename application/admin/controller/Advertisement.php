<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use app\admin\model\mAd;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\admin\model\mConfig;
use app\admin\model\mAdCate;

class Advertisement extends Common {

    //广告列表
    public function index() {
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['a.status', 'between', [1, 2]]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name',
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mAd::getAdPageList($where, $pages);

            res_return('ok', $res['data'], $res['count']);
        } else {
            return $this->fetch();
        }
    }

    //新增小说
    public function addBook() {
        if ($this->request->isAjax()) {
            $field = 'name,cover,detail_img,author,summary,money,status,long_type,free_type,new_type,gender_type,over_type,is_hot,free_chapter,lead';
            $field .= ',hot_num,share_title,share_desc,sort_num,area,category';
            $data = myValidate::getData(mBook::$rules, $field);
            $data['type'] = 2;
            mBook::doneBook($data);
        } else {
            $field = 'id,name,cover,detail_img,author,summary,money:28,status,long_type,free_type:2,is_hot,new_type,gender_type,over_type,free_chapter:15,lead';
            $field .= ',hot_num:0,share_title,share_desc,sort_num:0,area,category';
            $option = mBook::getBookRadioList();
            $option['cur'] = mBook::buildArr($field);
            $option['category'] = mConfig::getConfig('novel_category');
            $option['area'] = mConfig::getConfig('novel_area');
            $option['title'] = '更新小说信息';
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('public/doBook');
        }
    }

    //编辑广告位
    public function adedit() {
        if ($this->request->isAjax()) {
            $field = 'id,name,sort,status';
            $data = myValidate::getData(mAd::$rules, $field);
            mAd::updateAd($data);
        } else {
            $id = myRequest::getId('id');
            $cur = mAd::getById('ad', $id);
            if (!$cur) {
                res_return('广告位不存在');
            }
            $option = [
                'status' => [
                    'name' => 'status',
                    'option' => [['val' => 1, 'text' => '启用', 'default' => 0], ['val' => 2, 'text' => '禁用', 'default' => 1]]
            ]];
            $option['cur'] = $cur;
            $option['title'] = '更新广告位信息';
            $option['backUrl'] = my_url('index');

            $this->assign($option);
            return $this->fetch('adedit');
        }
    }

    //添加广告位
    public function adadd() {
        if ($this->request->isAjax()) {
            $field = 'id,name,sort,status';
            $data = myValidate::getData(mAd::$add_rules, $field);
            mAd::updateAd($data);
        } else {
            $option = [
                'status' => [
                    'name' => 'status',
                    'option' => [['val' => 1, 'text' => '启用', 'default' => 0], ['val' => 2, 'text' => '禁用', 'default' => 1]]
            ]];

            $option['title'] = '添加广告位信息';
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('adadd');
        }
    }

    //广告位状态修改
    public function dostatus() {
        $field = 'id,event';
        $data = myValidate::getData(mAd::$rules, $field);
        if (in_array($data['event'], ['on', 'off'])) {
            switch ($data['event']) {
                case 'on':
                    $status = 1;
                    break;
                case 'off':
                    $status = 2;
                    break;
            }
            $re = mAd::setField('ad', [['id', '=', $data['id']]], 'status', $status);
            if ($re) {
                //清除缓存
                $this->clearCache($data['id']);
                   $video = new Video();
                $video->refreshCache();
                res_return();
            } else {
                res_return('操作失败');
            }
        } else {
            if ($data['event'] === 'delete') {
                $re = mAd::delete($data['id']);
                if ($re) {
                    res_return();
                } else {
                    res_return('操作失败,请重试');
                }
            }
        }
    }

    //清除缓存
    private function clearCache($id) {
        $name = \think\Db::name('ad')->where('id', $id)->value('name');
        $key = 'ad_' . $name;
        cache($key, null);
    }

    //广告状态修改
    public function dostatu() {
        $field = 'id,event';
        $data = myValidate::getData(mAdCate::$rules, $field);

        if (in_array($data['event'], ['on', 'off'])) {
            switch ($data['event']) {
                case 'on':
                    $status = 1;
                    break;
                case 'off':
                    $status = 2;
                    break;
            }
            $re = mAdCate::setField('ad_cate', [['id', '=', $data['id']]], 'status', $status);
            if ($re) {
                //清除缓存
                $this->clearCache($data['id']);
                $video = new Video();
                $video->refreshCache();
                res_return();
            } else {
                res_return('操作失败');
            }
        } else {

            if ($data['event'] === 'delete') {
                $re = mAdCate::delete($data['id']);
                if ($re) {
                    res_return();
                } else {
                    res_return('操作失败,请重试');
                }
            }
        }
    }

    //广告列表
    public function details() {
        $id = myRequest::getId('id');
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['cid', '=', $id]],
                'eq' => 'number',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mAdCate::getAdCatePageList($where, $pages);
            res_return('ok', $res['data'], $res['count']);
        } else {
            $this->assign('id', $id);
            return $this->fetch();
        }
    }

    //新增广告
    public function addCate() {
        if ($this->request->isAjax()) {
            $field = 'book_id,name,number,content';
            $data = myValidate::getData(mChapter::$chapter, $field);
            mChapter::doneChapter($data);
        } else {
            $book_id = myValidate::getData(mChapter::$chapter, 'book_id', 'get');
            $field = 'id,name,number,content';
            $cur = mChapter::buildArr($field);
            $cur['book_id'] = $book_id;
            $this->assign('cur', $cur);
            return $this->fetch('doChapter');
        }
    }

    //编辑广告
    public function doedit() {
        if ($this->request->isAjax()) {
            $field = 'id,url,img,sort,status';
            $data = myValidate::getData(mAdCate::$rules, $field);
            mAdCate::doneChapter($data);
        } else {
            $id = myRequest::getId('id');
            $cur = mAdCate::getById('ad_cate', $id, 'id,status,url,img,sort,cid');
            if (!$cur) {
                res_return('广告不存在');
            }
            $option = [
                'status' => [
                    'name' => 'status',
                    'option' => [['val' => 1, 'text' => '启用', 'default' => 0], ['val' => 2, 'text' => '禁用', 'default' => 1]]
            ]];
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('details');
            $this->assign('cur', $cur);
            $this->assign($option);
            return $this->fetch('');
        }
    }

    //新增广告页
    public function doadd() {
        if ($this->request->isAjax()) {
            $field = 'url,img,sort,status,cid';
            $data = myValidate::getData(mAdCate::$rules, $field);
            mAdCate::doneChapter($data);
        } else {
            $option = [
                'status' => [
                    'name' => 'status',
                    'option' => [['val' => 1, 'text' => '启用', 'default' => 0], ['val' => 2, 'text' => '禁用', 'default' => 1]]
            ]];

            $option['backUrl'] = my_url('details');
            $option['cid'] = myRequest::get('cid');

            $this->assign($option);
            return $this->fetch('');
        }
    }

    //查看章节
    public function showInfo() {
        $id = myRequest::getId('章节');
        $cur = mChapter::getById('BookChapter', $id);
        if (!$cur) {
            res_return('章节不存在');
        }
        $cur['content'] = getBlockContent($cur['number'], 'book/' . $cur['book_id']);
        $this->assign('cur', $cur);
        return $this->fetch('showInfo');
    }

    //清空所有广告
    public function delAll() {
        $cid = myRequest::postId('广告位id', 'cid');
        $re = mAdCate::delAll($cid);
        if ($re) {
            res_return();
        } else {
            res_return('删除失败');
        }
    }

    //刷新缓存
    public function refreshCache() {
        $variable = mBook::getBlockData(2);
        $variable['title'] = '小说';
        $this->assign($variable);
        $html = $this->fetch('block/bookContent');
        saveBlock($html, 'novel_content', 'other');
        $html = $this->fetch('block/ranks');
        saveBlock($html, 'novel_ranks', 'other');
        res_return();
    }

}
