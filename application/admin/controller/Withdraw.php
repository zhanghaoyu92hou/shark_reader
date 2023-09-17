<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mWithdraw;
use app\common\model\myValidate;

class Withdraw extends Common{
    
    //提现申请列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[0,2]],['a.to_channel_id','=',0]],
                'eq' => 'status:a.status'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mWithdraw::getWithdrawList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //处理提现申请
    public function doWithdraw(){
        $field = 'id,event,remark';
        $data = myValidate::getData(mWithdraw::$rules,$field);
        $re = mWithdraw::doWithdraw($data);
        if($re){
            res_return();
        }else{
            res_return('操作失败，请重试');
        }
    }
}
