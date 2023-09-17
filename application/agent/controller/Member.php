<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\agent\model\aMember;
use app\agent\model\aLogin;

class Member extends Common{
    
    //用户列表
    public function index(){
        if($this->request->isAjax()){
            global $loginId;
            $type = aLogin::getCache('type');
            $key = ($type == 1) ? 'channel_id' : 'agent_id';
            $config = [
                'default' => [['status','between',[1,2]],[$key,'=',$loginId]],
                'eq' => 'status',
                'like' => 'keyword:nickname'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $field = 'id,nickname,phone,money,subscribe,status,create_time';
            $res = aMember::getPageList('Member', $where, $field, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['info_url'] = my_url('Member/info',['id'=>$v['id']]);
                    $v['status_name'] = ($v['status'] == 1) ? '正常' : '禁用';
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['is_subscribe'] = $v['subscribe'] > 0 ? '已关注' : '未关注';
                    $v['phone'] = $v['phone'] ? $v['phone'] : '未绑定';
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //用户详情
    public function info(){
        $id = myRequest::getId('用户');
        $cur = aMember::getById('Member',$id,'id,nickname,headimgurl,money,total_money,subscribe,viptime,create_time');
        if(empty($cur)){
            res_return('用户信息异常');
        }
        $count = aMember::getMemberCountMsg($id);
        $cur['charge_money'] = $count['charge'];
        $cur['consume_money'] = $count['consume'];
        $variable = [
            'cur' => $cur,
            'url' => [
                'charge' => my_url('getRecordList',['uid'=>$id,'type'=>1]),
                'activity' => my_url('getRecordList',['uid'=>$id,'type'=>2]),
                'reward' => my_url('getRecordList',['uid'=>$id,'type'=>3]),
                'sign' => my_url('getRecordList',['uid'=>$id,'type'=>4]),
                'consume' => my_url('getRecordList',['uid'=>$id,'type'=>5])
            ]
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //获取各种记录列表
    public function getRecordList(){
        $get = myRequest::get('type');
        $pages = myRequest::getPageParams();
        $type = aLogin::getCache('type');
        $key = $type == 1 ? 'channel_id' : 'agent_id';
        global $loginId;
        switch ($get['type']){
            case 1:
                $config = [
                    'default' => [['type','=',1],[$key,'=',$loginId],['is_count','=',1]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = aMember::getOrderList($where,$pages);
                break;
            case 2:
                $config = [
                    'default' => [['type','=',2],[$key,'=',$loginId],['is_count','=',1]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = aMember::getOrderList($where,$pages);
                break;
            case 3:
                $config = [
                    'default' => [['type','=',3],[$key,'=',$loginId],['is_count','=',1]],
                    'eq' => 'uid'
                ];
                $where = mySearch::getWhere($config);
                $res = aMember::getOrderList($where,$pages);
                break;
            case 4:
                $config = ['eq' => 'uid'];
                $where = mySearch::getWhere($config);
                $res = aMember::getSignList($where,$pages);
                break;
            case 5:
                $config = ['eq' => 'uid'];
                $where = mySearch::getWhere($config);
                $res = aMember::getConsumeList($where,$pages);
                break;
            default:
                res_return('请求数据异常');
                break;
        }
        res_return('ok',$res['data'],$res['count']);
    }
    
}