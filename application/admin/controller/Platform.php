<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mPlatform;
use app\common\model\myValidate;

class Platform extends Common{
    
    //渠道列表
    public function channel(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[0,3]],['type','=',1],['is_wx','=',1],['parent_id','=',0]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $field = 'id,name,login_name,url,money,ratio,total_charge,status';
            $res = mPlatform::getPageList('Channel',$where, $field, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = mPlatform::getStatusName($v['status']);
                    $v['do_url'] = my_url('doChannel',['id'=>$v['id']]);
                    $v['child_url'] = my_url('child',['id'=>$v['id']]);
                    $v['ratio'] .= '%';
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增渠道
    public function addChannel(){
        if($this->request->isAjax()){
            $field = 'name,login_name,password,status,url,is_location,location_url,appid,appsecret,apptoken,qrcode,deduct_min,deduct_num,wefare_days,ratio,bank_user,bank_name,bank_no';
            mPlatform::doneChannel($field);
        }else{
            $field = 'id,name,login_name,status,url,is_location:2,location_url,appid,appsecret,apptoken,qrcode,deduct_min:0,deduct_num:0,wefare_days:30,ratio:90,bank_user,bank_name,bank_no';
            $cur = mPlatform::buildArr($field);
            $variable = mPlatform::getChannelOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('channel');
            $this->assign($variable);
            return $this->fetch('doChannel');
        }
    }
    
    //编辑渠道
    public function doChannel(){
        if($this->request->isAjax()){
            $field = 'id,name,status,url,is_location,location_url,appid,appsecret,apptoken,qrcode,deduct_min,deduct_num,wefare_days,ratio,bank_user,bank_name,bank_no';
            mPlatform::doneChannel($field);
        }else{
            $id = myRequest::getId('渠道');
            $cur = mPlatform::getById('Channel', $id);
            if(!$cur){
                res_return('渠道不存在');
            }
            $variable = mPlatform::getChannelOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('channel');
            $this->assign($variable);
            return $this->fetch('doChannel');
        }
    }
    
    
    //处理渠道状态
    public function doChannelEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mPlatform::$rules,$field);
        $cur = mPlatform::getById('Channel',$data['id'],'id,url,location_url');
        if(!$cur){
            res_return('渠道信息异常');
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
            case 'pass':
                $value = 1;
                break;
            case 'fail':
                $value = 3;
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
        	$re = mPlatform::save('Channel',[['id','=',$cur['id']]],$saveData);
        }else{
        	$re = mPlatform::setField('Channel', [['id','=',$cur['id']]],$key, $value);
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
    
    //代理列表
    public function agent(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[0,3]],['type','=',1],['is_wx','=',2],['parent_id','=',0]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $field = 'id,name,login_name,money,ratio,status,total_charge';
            $res = mPlatform::getPageList('Channel',$where, $field, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = mPlatform::getStatusName($v['status']);
                    $v['do_url'] = my_url('doAgent',['id'=>$v['id']]);
                    $v['child_url'] = my_url('child',['id'=>$v['id']]);
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
            $field = 'name,login_name,password,status,url,deduct_min,deduct_num,wefare_days,ratio,bank_user,bank_name,bank_no';
            mPlatform::doneAgent($field);
        }else{
            $field = 'id,name,login_name,status,url,deduct_min:0,deduct_num:0,wefare_days:30,ratio:90,bank_user,bank_name,bank_no';
            $cur = mPlatform::buildArr($field);
            $variable = mPlatform::getAgentOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('agent');
            $this->assign($variable);
            return $this->fetch('doAgent');
        }
    }
    
    //编辑代理
    public function doAgent(){
        if($this->request->isAjax()){
            $field = 'id,name,status,url,deduct_min,deduct_num,wefare_days,ratio,bank_user,bank_name,bank_no';
            mPlatform::doneAgent($field);
        }else{
            $id = myRequest::getId('代理');
            $cur = mPlatform::getById('Channel', $id);
            if(!$cur){
                res_return('渠道不存在');
            }
            $variable = mPlatform::getAgentOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('agent');
            $this->assign($variable);
            return $this->fetch('doAgent');
        }
    }
    
    //子代理列表
    public function child(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[0,3]],['type','=',2]],
                'eq' => 'status,id:parent_id',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $field = 'id,name,login_name,money,ratio,total_charge,status';
            $res = mPlatform::getPageList('Channel',$where, $field, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = mPlatform::getStatusName($v['status']);
                    $v['do_url'] = my_url('doChild',['id'=>$v['id']]);
                    $v['ratio'] .= '%';
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            $parent_id = myRequest::getId('代理');
            $cur = mPlatform::getById('Channel',$parent_id,'id,is_wx');
            if(!$cur){
                res_return('渠道不存在');
            }
            $back_url = my_url('channel');
            if($cur['is_wx'] != 1){
                $back_url = my_url('agent');
            }
            $this->assign('back_url',$back_url);
            return $this->fetch();
        }
    }
    
    //编辑子代理
    public function doChild(){
    	if($this->request->isAjax()){
    		$field = 'id,name,status,url,deduct_min,deduct_num,ratio,bank_user,bank_name,bank_no';
    		mPlatform::doneAgent($field);
    	}else{
    		$id = myRequest::getId('代理');
    		$cur = mPlatform::getById('Channel', $id);
    		if(!$cur){
    			res_return('代理不存在');
    		}
    		if($cur['type'] != 2 || !$cur['parent_id']){
    			res_return('代理信息异常');
    		}
    		$variable = mPlatform::getAgentOptions();
    		$variable['cur'] = $cur;
    		$variable['backUrl'] = my_url('child',['id'=>$cur['parent_id']]);
    		$this->assign($variable);
    		return $this->fetch('doChild');
    	}
    }
    
    
    //处理代理状态
    public function doAgentEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mPlatform::$rules,$field);
        $cur = mPlatform::getById('Channel',$data['id'],'id,url,location_url');
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
            case 'pass':
                $value = 1;
                break;
            case 'fail':
                $value = 3;
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
        	$re = mPlatform::save('Channel',[['id','=',$cur['id']]],$saveData);
        }else{
        	$re = mPlatform::setField('Channel', [['id','=',$cur['id']]],$key, $value);
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
    
}