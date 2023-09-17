<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\mySearch;
use app\common\model\myRequest;
use app\common\model\myValidate;
use app\admin\model\mNode;
use app\admin\model\mRole;
use app\admin\model\mUser;
use app\admin\model\mLogin;

class Rbac extends Common{
    
    //节点管理
    public function node(){
        if($this->request->isAjax()){
            $list = mNode::getNodeList(1);
            if($list){
                foreach ($list as &$v){
                    $v['menu_name'] = $v['is_menu'] == 1 ? '菜单' : '方法';
                    $v['add_url'] = my_url('addNode',['parent_id'=>$v['id']]);
                    $v['do_url'] = my_url('doNode',['id'=>$v['id']]);
                }
            }
            res_return($list);
        }else{
            return $this->fetch();
        }
    }
    
    //添加节点
    public function addNode(){
        if($this->request->isAjax()){
            $field = 'pid,name,is_menu,icon,url,child_nodes';
            mNode::doneNodes($field);
        }else{
            $field = 'id,pid,name,is_menu,icon,url,child_nodes';
            $cur = mNode::buildArr($field);
            $cur['parent_name'] = '作为一级节点';
            $get = myRequest::get('parent_id');
            $parent_id = $get['parent_id'] ? : 0;
            if($parent_id){
                $parent = mNode::getById('Nodes',$parent_id,'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $cur['pid'] = $parent_id;
            $variable = mNode::getOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('node');
            $this->assign($variable);
            return $this->fetch('doNode');
        }
    }
    
    //编辑节点
    public function doNode(){
        if($this->request->isAjax()){
            $field = 'id,pid,name,is_menu,icon,url,child_nodes';
            mNode::doneNodes($field);
        }else{
            $id = myRequest::getId('节点');
            $cur = mNode::getById('Nodes',$id);
            if(!$cur){
                res_return('节点不存在');
            }
            $cur['parent_name'] = '作为一级节点';
            if($cur['pid'] > 0){
                $parent = mNode::getById('Nodes',$cur['pid'],'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $variable = mNode::getOptions();
            $variable['cur'] = $cur;
            $variable['backUrl'] = my_url('node');
            $this->assign($variable);
            return $this->fetch('doNode');
        }
    }
    
    //处理节点排序及删除节点
    public function doNodeEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mNode::$rules,$field);
        if($data['event'] === 'delete'){
            $re = mNode::deleteNodes($data['id']);
        }else{
            $re = mNode::doNodeSort($data);
        }
        if($re){
        	mLogin::clearNode();
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //角色管理
    public function role(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[1,2]]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mRole::getPageList('Role',$where, '*', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['status_name'] = $v['status'] == 1 ? '启用' : '禁用'; 
                    $v['do_url'] = my_url('doRole',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增角色
    public function addRole(){
        if($this->request->isAjax()){
            $field = 'name,status,content,summary';
            mRole::doneRole($field);
        }else{
            $field = 'id,name,status,summary';
            $cur = mRole::buildArr($field);
            $option = mRole::getOptions();
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doRole');
        }
    }
    
    //编辑角色
    public function doRole(){
        if($this->request->isAjax()){
            $field = 'id,name,status,content,summary';
            mRole::doneRole($field);
        }else{
            $id = myRequest::getId('角色');
            $cur = mRole::getById('Role',$id);
            if(!$cur){
                res_return('角色不存在');
            }
            $option = mRole::getOptions();
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doRole');
        }
    }
    
    //处理角色事件
    public function doRoleEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mRole::$rules,$field);
        switch ($data['event']){
            case 'on':
                $status = 1;
                break;
            case 'off':
                $status = 2;
                break;
            case 'delete':
                $status = 3;
                break;
        }
        $re = mRole::setField('Role', [['id','=',$data['id']]], 'status', $status);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //用户管理
    public function user(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[1,2]]],
                'eq' => 'status',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mUser::getUserList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增用户
    public function addUser(){
        if($this->request->isAjax()){
            $field = 'name,role_id,login_name,password,status';
            mUser::doneUser($field);
        }else{
            $field = 'id,name,role_id,login_name,password,status';
            $option = mUser::getOptions();
            $option['cur'] = mUser::buildArr($field);
            $this->assign($option);
            return $this->fetch('doUser');
        }
    }
    
    //编辑用户
    public function doUser(){
        if($this->request->isAjax()){
            $field = 'id,name,role_id,status';
            mUser::doneUser($field);
        }else{
            $id = myRequest::getId('用户');
            $cur = mUser::getById('Manage',$id);
            if(!$cur){
                res_return('用户不存在');
            }
            $option = mUser::getOptions();
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doUser');
        }
    }
    
    //处理用户事件
    public function doUserEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mUser::$rules,$field);
        $key = 'status';
        if($data['event'] === 'delete'){
        	$re = mUser::delById('Manage', $data['id']);
        }else{
        	switch ($data['event']){
        		case 'on':
        			$value = 1;
        			break;
        		case 'off':
        			$value = 2;
        			break;
        		case 'resetpwd':
        			$key = 'password';
        			$value = createPwd(123456);
        			break;
        	}
        	$re = mUser::setField('Manage', [['id','=',$data['id']]],$key, $value);
        }
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    
}
