<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\channel\model\cBook;
use app\channel\model\cLogin;

class Music extends Common{
    
    //书籍列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','=',1],['a.type','=',3]],
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cBook::getBookPageList($where, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '已下架';
                    $v['over_type'] = $v['long_type'] == 1 ? '连载中' : '已完结';
                    $v['free_type'] = $v['free_type'] == 1 ? '免费' : '收费';
                    $v['gender_type'] = $v['gender_type'] == 1 ? '男频' : '女频';
                    $v['long_type'] = $v['long_type'] == 1 ? '长篇' : '短篇';
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['customer_url'] = my_url('Message/addTask',['book_id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('听书');
        $book = cBook::getById('Book',$id,'id,name');
        if(!$book){
            res_return('书籍参数错误');
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
        
        $short_url = '/Index/Book/info.html?book_id='.$id;
        $data = [
            'notice' => '温馨提示 : 相对链接只能应用到页面跳转链接中，如轮播图链接等，渠道用户点击后不会跳转到总站',
            'links' => [
                ['title'=>'相对链接','val'=>$short_url],
                ['title'=>'绝对链接','val'=>$url.$short_url]
            ]
        ];
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
}