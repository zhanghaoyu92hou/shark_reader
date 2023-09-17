<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mNode;
use app\common\model\myRequest;
use app\common\model\myValidate;

class System extends Common{
    
    //渠道菜单
    public function channelMenu(){
        if($this->request->isAjax()){
            $list = mNode::getChannelNode(2);
            if($list){
                foreach ($list as &$v){
                    $v['add_url'] = my_url('addChannelMenu',['parent_id'=>$v['id']]);
                    $v['do_url'] = my_url('doChannelMenu',['id'=>$v['id']]);
                }
            }
            res_return($list);
        }else{
            return $this->fetch('channelMenu');
        }
    }
    
    //添加节点
    public function addChannelMenu(){
        if($this->request->isAjax()){
            $field = 'pid,name,icon,url';
            mNode::doneNodes($field,2);
        }else{
            $field = 'id,pid,name,icon,url';
            $cur = mNode::buildArr($field);
            $cur['parent_name'] = '作为一级节点';
            $get = myRequest::get('parent_id');
            $parent_id = $get['parent_id'] ? : 0;
            if($parent_id){
                $parent = mNode::getById('Nodes',$parent_id,'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $cur['pid'] = $parent_id;
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('channelMenu')
            ];
            $this->assign($variable);
            return $this->fetch('doChannelMenu');
        }
    }
    
    //编辑节点
    public function doChannelMenu(){
        if($this->request->isAjax()){
            $field = 'id,pid,name,icon,url';
            mNode::doneNodes($field,2);
        }else{
            $id = myValidate::getData(mNode::$rules,'id','get');
            $cur = mNode::getById('Nodes', $id);
            if(!$cur){
                res_return('节点不存在');
            }
            $cur['parent_name'] = '作为一级节点';
            if($cur['pid'] > 0){
                $parent = mNode::getById('Nodes',$cur['pid'],'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('channelMenu')
            ];
            $this->assign($variable);
            return $this->fetch('doChannelMenu');
        }
    }
    
    //处理节点排序及启用禁用
    public function doChannelMenuEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mNode::$rules,$field);
        if(in_array($data['event'],['sortDown','sortUp'])){
            $re = mNode::doNodeSort($data);
        }else{
            if(in_array($data['event'], ['on','off'])){
                $status = 2;
                if($data['event'] === 'on'){
                    $status = 1;
                }
                $re = mNode::setField('Nodes',[['id','=',$data['id']]], 'status',$status);
            }else{
                res_return('该按钮尚未绑定事件');
            }
        }
        if($re){
        	cache('CHANNEL_LOGIN_MENU',null);
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //代理菜单
    public function agentMenu(){
        if($this->request->isAjax()){
            $list = mNode::getChannelNode(3);
            if($list){
                foreach ($list as &$v){
                    $v['add_url'] = my_url('addAgentMenu',['parent_id'=>$v['id']]);
                    $v['do_url'] = my_url('doAgentMenu',['id'=>$v['id']]);
                }
            }
            res_return($list);
        }else{
            return $this->fetch('agentMenu');
        }
    }
    
    //添加节点
    public function addAgentMenu(){
        if($this->request->isAjax()){
            $field = 'pid,name,icon,url';
            mNode::doneNodes($field,3);
        }else{
            $field = 'id,pid,name,icon,url';
            $cur = mNode::buildArr($field);
            $cur['parent_name'] = '作为一级节点';
            $get = myRequest::get('parent_id');
            $parent_id = $get['parent_id'] ? : 0;
            if($parent_id){
                $parent = mNode::getById('Nodes',$parent_id,'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $cur['pid'] = $parent_id;
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('agentMenu')
            ];
            $this->assign($variable);
            return $this->fetch('doAgentMenu');
        }
    }
    
    //编辑节点
    public function doAgentMenu(){
        if($this->request->isAjax()){
            $field = 'id,pid,name,icon,url';
            mNode::doneNodes($field,3);
        }else{
            $id = myValidate::getData(mNode::$rules,'id','get');
            $cur = mNode::getById('Nodes', $id);
            if(!$cur){
                res_return('节点不存在');
            }
            $cur['parent_name'] = '作为一级节点';
            if($cur['pid'] > 0){
                $parent = mNode::getById('Nodes',$cur['pid'],'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('agentMenu')
            ];
            $this->assign($variable);
            return $this->fetch('doAgentMenu');
        }
    }
    
    //处理节点排序及删除节点
    public function doAgentMenuEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mNode::$rules,$field);
        if(in_array($data['event'],['sortDown','sortUp'])){
            $re = mNode::doNodeSort($data);
        }else{
            if(in_array($data['event'], ['on','off'])){
                $status = 2;
                if($data['event'] === 'on'){
                    $status = 1;
                }
                $re = mNode::setField('Nodes',[['id','=',$data['id']]], 'status',$status);
            }else{
                res_return('该按钮尚未绑定事件');
            }
        }
        if($re){
        	cache('AGENT_LOGIN_MENU',null);
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
}
