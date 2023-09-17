<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\mySearch;
use app\common\model\myRequest;
use app\channel\model\cChart;

class Chart extends Common{
    
    //投诉统计
    public function complaint(){
       if($this->request->isAjax()){
           global $loginId;
           $config = ['default'=>[['a.channel_id','=',$loginId]],'like'=>'keyword:b.name'];
           $where = mySearch::getWhere($config);
           $list = cChart::getComplaintList($where);
           res_return('ok',$list);
       }else{
           
           return $this->fetch();
       }
    }
    
    //用户统计
    public function member(){
        $data = cChart::getMemberCount();
        $this->assign($data);
        return $this->fetch();
    }
    
    //推广统计
    public function spread(){
        if($this->request->isAjax()){
            global $loginId;
            $config = ['default'=>[['a.channel_id','=',$loginId]],'like'=>'keyword:a.name|b.name','eq'=>'type:a.type'];
            $where = mySearch::getWhere($config);
            $pages = myRequest::getPageParams();
            $res = cChart::getSpreadCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //订单统计
    public function order(){
        $data = cChart::getOrderCount();
        $this->assign($data);
        return $this->fetch();
    }
    
    //分成统计
    public function bonus(){
        if($this->request->isAjax()){
            global $loginId;
            $config = ['default'=>[['a.channel_id','=',$loginId],['a.status','=',2],['a.is_count','=',1]],'like'=>'keyword:a.order_no','between'=>'between_time:a.create_time'];
            $where = mySearch::getWhere($config);
            $pages = myRequest::getPageParams();
            $res = cChart::getOrderCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
}