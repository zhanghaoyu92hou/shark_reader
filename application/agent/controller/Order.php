<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\agent\model\aOrder;
use app\agent\model\aLogin;

class Order extends Common{
    
    //充值订单
    public function charge(){
        if($this->request->isAjax()){
            global $loginId;
            $type = aLogin::getCache('type');
            $key = ($type == 1) ? 'a.channel_id' : 'a.agent_id';
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',1],['a.is_count','=',1],[$key,'=',$loginId]],
                'eq' => 'status:a.status,from:a.realtion_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aOrder::getOrderPageList($where, $pages);
            $time = time();
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //打赏订单
    public function reward(){
        if($this->request->isAjax()){
            global $loginId;
            $type = aLogin::getCache('type');
            $key = ($type == 1) ? 'a.channel_id' : 'a.agent_id';
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',3],['a.is_count','=',1],[$key,'=',$loginId]],
                'eq' => 'status:a.status,from:a.relation_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aOrder::getOrderPageList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //活动订单
    public function activity(){
        if($this->request->isAjax()){
            global $loginId;
            $type = aLogin::getCache('type');
            $key = ($type == 1) ? 'a.channel_id' : 'a.agent_id';
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',2],['a.is_count','=',1],[$key,'=',$loginId]],
                'eq' => 'status:a.status,from:a.relation_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aOrder::getOrderPageList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
}
