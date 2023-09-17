<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\mySearch;
use app\common\model\myRequest;
use app\agent\model\aChart;
use app\agent\model\aLogin;

class Chart extends Common{
    
    //用户统计
    public function member(){
        $data = aChart::getMemberCount();
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
            $res = aChart::getSpreadCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //订单统计
    public function order(){
        $data = aChart::getOrderCount();
        $this->assign($data);
        return $this->fetch();
    }
    
    //分成统计
    public function bonus(){
        if($this->request->isAjax()){
            global $loginId;
            $type = aLogin::getCache('type');
            $key = ($type == 1) ? 'a.channel_id' : 'a.agent_id';
            $config = ['default'=>[[$key,'=',$loginId],['a.status','=',2],['a.is_count','=',1]],'like'=>'keyword:a.order_no','between'=>'between_time:a.create_time'];
            $where = mySearch::getWhere($config);
            $pages = myRequest::getPageParams();
            $res = aChart::getOrderCountList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
}