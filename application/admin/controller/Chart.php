<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mChart;
use app\common\model\mySearch;
use app\common\model\myRequest;

class Chart extends Common{
    
    //充值统计
    public function charge(){
        if($this->request->isAjax()){
            $type = myRequest::getListOne('type',['cartoon','novel','music','video']);
            $data = mChart::getChargeList($type);
            $data = $data ? : [];
            res_return($data);
        }else{
            mChart::getChargeList('cartoon');
            $data = mChart::getChargeData();
            $this->assign('data',$data);
            return $this->fetch();
        }
    }
    
    //投诉统计
    public function complaint(){
       if($this->request->isAjax()){
           $config = ['like'=>'keyword:b.name'];
           $where = mySearch::getWhere($config);
           $list = mChart::getComplaintList($where);
           res_return('ok',$list);
       }else{
           
           return $this->fetch();
       }
    }
    
    //用户统计
    public function member(){
        $data = mChart::getMemberCount();
        $this->assign($data);
        return $this->fetch();
    }
    
    //推广统计
    public function spread(){
        if($this->request->isAjax()){
            $config = ['like'=>'keyword:a.name|b.name','eq'=>'type:a.type'];
            $where = mySearch::getWhere($config);
            $pages = myRequest::getPageParams();
            $res = mChart::getSpreadCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //订单统计
    public function order(){
        $data = mChart::getOrderCount();
        $this->assign($data);
        return $this->fetch();
    }
    
    //分成统计
    public function bonus(){
        if($this->request->isAjax()){
            $config = ['default'=>[['a.status','=',2],['a.is_count','=',1]],'like'=>'keyword:a.order_no','between'=>'between_time:a.create_time'];
            $where = mySearch::getWhere($config);
            $pages = myRequest::getPageParams();
            $res = mChart::getOrderCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
}