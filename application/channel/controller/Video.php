<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\channel\model\cVideo;
use app\channel\model\cLogin;

class Video extends Common{
    
    //视频列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','=',1]],
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cVideo::getPageList('Video',$where, '*', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '已下架';
                    $v['customer_url'] = my_url('Message/addTask',['video_id'=>$v['id']]);
                    $v['play_url'] = my_url('doPlay',['id'=>$v['id']]);
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['free_str'] = '免费';
                    if($v['free_type'] == 2){
                        $v['free_str'] = '收费:'.$v['money'].'书币';
                    }
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('视频');
        $cur = cVideo::getById('Video',$id,'id,name');
        if(!$cur){
            res_return('视频参数错误');
        }
        $config = cLogin::getCache();
        if(!array_key_exists('url', $config) || !$config['url']){
            res_return('您尚未配置站点url');
        }
        $url = 'http://';
        if($config['is_location'] == 1 && $config['location_url']){
            $url .= $config['location_url'];
        }else{
            $url .= $config['url'];
        }
        
        $short_url = '/Index/Video/info.html?video_id='.$id;
        $data = [
            'notice' => '温馨提示 : 相对链接只能应用到页面跳转链接中，如轮播图链接等，渠道用户点击后不会跳转到总站',
            'links' => [
                ['title'=>'视频链接','val'=>$url.$short_url]
            ]
        ];
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
    //处理视频播放
    public function doPlay(){
        $id = myRequest::getId('视频');
        $cur = cVideo::getById('Video',$id,'id,url');
        if(!$cur){
            res_return('视频不存在');
        }
        if(!$cur['url']){
            res_return('视频链接未配置');
        }
        $this->assign('cur',$cur);
        return $this->fetch('doPlay');
        
    }
}