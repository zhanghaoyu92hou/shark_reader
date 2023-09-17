<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mOrder;
use app\common\model\myValidate;

class Order extends Common{
    
    //充值订单
    public function charge(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',1]],
                'eq' => 'status:a.status,from:a.relation_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mOrder::getOrderPageList($where, $pages);
            $time = time();
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //打赏订单
    public function reward(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',3]],
                'eq' => 'status:a.status,from:a.relation_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mOrder::getOrderPageList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //活动订单
    public function activity(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',2]],
                'eq' => 'status:a.status,from:a.relation_type',
                'like' => 'keyword:a.uid|a.order_no|a.relation_name',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mOrder::getOrderPageList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //商品订单
    public function product(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,4]]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.uid|a.order_no|a.pname',
                'between' => 'between_time:a.create_time'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mOrder::getSaleOrderPageList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //商品订单发货
    public function doSendProduct(){
        $id = myValidate::getData(mOrder::$rules,'id');
        $cur = mOrder::getById('SaleOrder',$id,'id,status');
        if(empty($cur)){
            res_return('订单不存在');
        }
        if($cur['status'] != 2){
            res_return('该单已发货');
        }
        $re = mOrder::setField('SaleOrder',[['id','=',$id]],'status', 3);
        if($re){
            res_return();
        }else{
            res_return('发货失败，请重试');
        }
    }
}
