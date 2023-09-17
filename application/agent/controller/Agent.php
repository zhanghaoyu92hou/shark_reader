<?php
namespace app\agent\controller;
use app\agent\controller\Common;
use app\common\model\myValidate;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\agent\model\aAgent;
use app\agent\model\aWithdraw;
use app\agent\model\aLogin;
use app\admin\model\mPlatform;

class Agent extends Common{
    
    //初始化检测是否总站一级代理
    public function __construct(){
        parent::__construct();
        $type = aLogin::getCache('type');
        if($type != 1){
            res_return('您无权访问该页面');
        }
    }
    
    //代理列表
    public function index(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['status','between',[0,3]],['type','=',2],['parent_id','=',$loginId]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $field = 'id,name,login_name,money,ratio,total_charge,status';
            $res = aAgent::getPageList('Channel',$where, $field, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = aAgent::getStatusName($v['status']);
                    $v['do_url'] = my_url('doAgent',['id'=>$v['id']]);
                    $v['ratio'] .= '%';
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增代理
    public function addAgent(){
        if($this->request->isAjax()){
            $field = 'name,login_name,password,url,ratio,bank_user,bank_name,bank_no';
            aAgent::doneAgent($field);
        }else{
            global $loginId;
            $channel = aAgent::getById('Channel', $loginId,'id,ratio');
            $field = 'id,name,login_name,url,ratio:80,bank_user,bank_name,bank_no';
            $cur = aAgent::buildArr($field);
            $variable = ['cur'=>$cur,'backUrl'=>my_url('index'),'ratio'=>$channel['ratio']];
            $this->assign($variable);
            return $this->fetch('doAgent');
        }
    }
    
    //编辑代理
    public function doAgent(){
        if($this->request->isAjax()){
            $field = 'id,name,status,ratio,bank_user,bank_name,bank_no';
            aAgent::doneAgent($field);
        }else{
            global $loginId;
            $channel = aAgent::getById('Channel', $loginId,'id,ratio');
            $id = myRequest::getId('代理');
            $cur = aAgent::getById('Channel', $id);
            if(!$cur){
                res_return('渠道不存在');
            }
            $variable = ['cur'=>$cur,'backUrl'=>my_url('index'),'ratio'=>$channel['ratio']];
            $this->assign($variable);
            return $this->fetch('doAgent');
        }
    }
    
    //处理代理状态
    public function doAgentEvent(){
        $field = 'id,event';
        $data = myValidate::getData(aAgent::$rules,$field);
        $cur = aAgent::getById('Channel',$data['id'],'id,url,location_url');
        if(!$cur){
            res_return('代理信息异常');
        }
        $key = 'status';
        switch ($data['event']){
            case 'on':
                $value = 1;
                break;
            case 'off':
                $value = 2;
                break;
            case 'delete':
                $value = 4;
                break;
            case 'resetpwd':
                $key = 'password';
                $value = createPwd(123456);
                break;
            default:
                res_return('未指定该事件');
                break;
        }
        if($value === 4){
        	$saveData = ['status'=>4,'url'=>'','location_url'=>''];
        	$re = aAgent::save('Channel',[['id','=',$cur['id']]],$saveData);
        }else{
        	$re = aAgent::setField('Channel', [['id','=',$cur['id']]],$key, $value);
        }
        if($re){
            if($key === 'status'){
                $cache_key = 'channel_info_'.$data['id'];
                cache($cache_key,null);
                if($cur['url']){
                    cache(md5($cur['url']),null);
                }
                if($cur['location_url']){
                    cache(md5($cur['location_url']),null);
                }
            }
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //进入代理后台
    public function intoBackstage(){
        $id = myRequest::postId('代理');
        $url = mPlatform::intoBackstage($id);
        res_return(['url'=>$url]);
    }
    
    //提现申请列表
    public function withdraw(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['a.to_channel_id','=',$loginId],['a.status','between',[0,2]]],
                'eq' => 'status:a.status'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = aWithdraw::getAgentList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //处理提现申请
    public function doWithdraw(){
        $field = 'id,event,remark';
        $data = myValidate::getData(aWithdraw::$rules,$field);
        $re = aWithdraw::doWithdraw($data);
        if($re){
            res_return();
        }else{
            res_return('操作失败，请重试');
        }
    }
    
    
}