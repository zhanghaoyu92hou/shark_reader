<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\channel\model\cActivity;
use app\channel\model\cLogin;

class Activity extends Common{
    
    //活动列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','=',1]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cActivity::getActivityList($where,$pages);
            $time = time();
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '禁用';
                    $v['customer_url'] = '';
                    if($time > $v['end_time']){
                        $v['between_time'] = '<font class="text-red">活动已结束</font>';
                    }elseif ($time < $v['start_time']){
                        $v['between_time'] = '<font class="text-red">活动未开始</font>';
                    }else{
                        $v['between_time'] = date('Y-m-d H:i',$v['start_time']).'~'.date('Y-m-d H:i',$v['end_time']);
                        $v['customer_url'] = my_url('Message/addTask',['activity_id'=>$v['id']]);
                    }
                    $v['content'] = '充'.$v['money'].'元送'.$v['send_money'].'书币';
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['first_str'] = $v['is_first'] == 1 ? '仅限一次' : '不限次数'; 
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('活动');
        $cur = cActivity::getById('Activity',$id,'id,name');
        if(!$cur){
            res_return('活动参数错误');
        }
        $channel = cLogin::getCache();
        if(!array_key_exists('url', $channel) || !$channel['url']){
            res_return('您尚未配置站点url');
        }
        $url = 'http://';
        if($channel['is_location'] == 1 && $channel['location_url']){
            $url .= $channel['location_url'];
        }else{
            $url .= $channel['url'];
        }
        $short_url = '/Index/Activity/index.html?activity_id='.$id;
        $data = [
            'links' => [
                ['title'=>'活动链接','val'=>$url.$short_url]
            ]
        ];
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
}