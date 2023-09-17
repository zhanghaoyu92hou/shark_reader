<?php

namespace app\index\controller;

use app\index\controller\Common;
use app\common\model\myCache;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\index\model\iVideo;
use app\index\model\iMember;
use weixin\wx;

class Video extends Common {

    //视频首页
    public function index() {
        parent::checkBlock('video', '视频');
        $block = myCache::getWebblockCache();
        $urlData = myCache::getUrlCache();
        $variable = [
            'web_block' => $block,
            'site_info' => $urlData
        ];
        $this->assign($variable);


        /*  $s=	getBlockText('video_content','other');

          preg_match('/<d[^>]*itemprop=\"datePublished\".*?>(.*?)<\/time>/ism',$s,$match);
          echo '<pre>';
          var_dump($match);
          exit; */
        return $this->fetch();
    }

    //视频详情页
    public function info() {
        $video_id = myRequest::getId('视频', 'video_id');
        $video = myCache::getVideoCache($video_id);
//        echo '<pre>';
//        print_r($video);exit;
        if (!$video) {
            res_return('视频不存在');
        }
        $is_read = 'no';
        global $loginId;
        if ($loginId) {
            $read = iVideo::getCur('PlayHistory', [['uid', '=', $loginId], ['video_id', '=', $video_id]]);
            if ($read) {
                $is_read = 'yes';
            }
        }
        //收藏
        $is_collect='no';
        if($loginId){
            $collect = iVideo::getCur('VideoCollection',[['uid','=',$loginId],['video_id','=',$video_id]]);
            if($collect){
                $is_collect = 'yes';
            }
        }
        $urlData = myCache::getUrlCache();
        $jsConfig = $share_data = '';
        if ($urlData['is_wx'] === 1 && $this->device_type === 1) {
            if ($video['cover'] && $video['share_title'] && $video['share_desc']) {
                wx::$config = $urlData;
                $jsConfig = wx::getJsConfig();
                global $loginId;
                $share_data = [
                    'title' => $video['share_title'],
                    'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index/Video/info.html?video_id=' . $video['id'] . '&share_user=' . $loginId,
                    'img' => $video['cover'],
                    'desc' => $video['share_desc']
                ];
            }
        }
        //此处是插入广告
        $data = myCache::getAdCache('视频详情页');
        $ad = '';
        if (!empty($data)) {
            $ad .= '<a style="width: 100%;height: 100px" href="' . $data['url'] . '">';
            $ad .= '<img style="width: 100%;height: 150px" src="' . $data['img'] . '"> </a>';
        }
        $text = htmlspecialchars($ad);
        $localurl='http://' . $_SERVER['HTTP_HOST'] . '/index/Video/info.html?video_id=' . $video['id'];
        $reward = myCache::getRewardCache();
        $variable = [
            'cur' => $video,
            'reward' => $reward,
            'text'=>$text,
            'is_read' => $is_read,
            'is_collect' => $is_collect,
            'site_title' => $this->site_title,
            'jsConfig' => json_encode($jsConfig, JSON_UNESCAPED_UNICODE),
            'share_data' => json_encode($share_data, JSON_UNESCAPED_UNICODE)
        ];
        iVideo::addHot($video);
         //print_r($share_data);
        $this->assign($variable);
       $this->assign('localurl',$localurl);
        return $this->fetch();
    }

    //检查视频播放
    public function checkPlay() {
        $video_id = myRequest::postId('视频', 'video_id');
        $video = myCache::getVideoCache($video_id);
        if (!$video) {
            res_return('视频不存在');
        }
        if ($video['free_type'] == 2) {
            global $loginId;
            if (!$loginId) {
                res_return(['flag' => 1, 'msg' => '您尚未登陆,是否立即登陆?', 'url' => my_url('Login/index')]);
            }
            $member = myCache::getUserCache($loginId);
            if (!$member) {
                iMember::clearLogin();
                res_return(['flag' => 1, 'msg' => '您尚未登陆,是否立即登陆?', 'url' => my_url('Login/index')]);
            }
            $read = iVideo::getCur('PlayHistory', [['uid', '=', $loginId], ['video_id', '=', $video_id]]);
            if (!$read) {
                $is_money = true;
                if ($member['viptime'] > 0) {
                    $is_money = false;
                    if ($member['viptime'] != 1) {
                        if (time() > $member['viptime']) {
                            $is_money = true;
                        }
                    }
                }
                if ($is_money) {
                    if ($member['money'] < $video['money']) {
                        res_return(['flag' => 2, 'url' => my_url('Charge/index', ['video_id' => $video_id])]);
                    }
                    $re = iVideo::costMoney($video, $member);
                    if (!$re) {
                        res_return('扣除书币失败，请重试');
                    }
                } else {
                    iVideo::addReadhistory($video_id, $loginId);
                }
            }
        }
        res_return(['flag' => 0, 'url' => $video['url']]);
    }

    //视屏播放十秒
    public function tenplay() {
        $video_id = myRequest::postId('视频', 'video_id');
        $video = myCache::getVideoCache($video_id);
        if (!$video) {
            res_return('视频不存在');
        }
        res_return(['flag' => 0, 'url' => $video['url']]);
    }

    //影库
    public function category() {
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['status', '=', 1]],
                'eq' => 'free_type',
                'like' => 'category',
                'rules' => ['free_type' => 'in:1,2']
            ];
            $where = mySearch::getWhere($config, 'post');
            $post = myRequest::post('page');
            $page = (is_numeric($post['page']) && $post['page'] > 0) ? $post['page'] : 1;
            $list = iVideo::getCategoryList($where, $page);
            $list = $list ?: 0;
            res_return('ok', $list);
        } else {
            $option = iVideo::getCategoryOption();
            $option['site_title'] = $this->site_title;
            $this->assign($option);
            return $this->fetch();
        }
    }

    //更多列表
    public function more() {
        if ($this->request->isAjax()) {
            $post = myRequest::post('area,page,is_hot');
            $list = 0;
            $where = [['status', '=', 1]];
            if (in_array($post['is_hot'], [1, 2])) {
                $where[] = ['is_hot', '=', $post['is_hot']];
            } else {
                if ($post['area']) {
                    $where[] = ['area', 'like', '%,' . $post['area'] . ',%'];
                } else {
                    res_return('ok', $list);
                }
            }
            $page = $post['page'] >= 1 ? $post['page'] : 1;
            $list = iVideo::getMoreList($where, $page);
            $list = $list ?: 0;
            res_return('ok', $list);
        } else {
            $get = myRequest::get('area,is_hot');
            if ($get['is_hot'] == 1) {
                $get['page_title'] = '热门推荐';
            } else {
                $title = $get['area'];
                $get['page_title'] = $title;
            }
            $get['site_title'] = $this->site_title;
            $this->assign('cur', $get);
            return $this->fetch();
        }
    }

    //获取猜你喜欢书籍
    public function getSameVideos() {
        $video_id = myRequest::postId('视频', 'video_id');
        $video = myCache::getVideoCache($video_id);
        $data = 0;
        if ($video) {
            $category = $video['category'];
            $where = [['category', '=', $category], ['status', '=', 1], ['id', '<>', $video_id]];
            $list = iVideo::getLimitVideos($where, 6);
            $data = $list ? $list : 0;
        }
        res_return(['list' => $data]);
    }
    //视频点赞
    public function videozan(){
        $video_id = myRequest::postId('视频','video_id');
        $video = myCache::getVideoCache($video_id);
        $video['id']=$video_id;
        iVideo::zan($video);
        res_return(['mess'=>'ok']);
    }

    //视频踩
    public function videocai(){
        $video_id = myRequest::postId('视频','video_id');
        $video = myCache::getVideoCache($video_id);
        $video['id']=$video_id;
        iVideo::cai($video);
        res_return(['mess'=>'ok']);
    }

}
