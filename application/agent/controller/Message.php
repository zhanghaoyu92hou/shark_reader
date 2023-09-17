<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\agent\model\aMessage;


class Message extends Common{
    
    //代理公告
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','=',1],['type','=',1]],
                'like' => 'keyword:title'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aMessage::getPageList('Message',$where,'id,type,title,create_time',$pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['show_url'] = my_url('showInfo',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //查看公告详情
    public function showInfo(){
        $id = myRequest::getId('公告');
        $content = getBlockContent($id,'message');
        aMessage::saveRead($id);
        $this->assign('content',$content);
        return $this->fetch('showInfo');
    }
    
    
    
    
}