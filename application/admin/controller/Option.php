<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\admin\model\mNode;

class Option extends Common{
    
    //获取更新角色页面节点列表
    public function getNodeSelectList(){
        $post = myRequest::post('role_id');
        $list = mNode::getOptionList();
        if($list){
            $role_id = $post['role_id'] ? : 0;
            if($role_id > 0){
                $cur_role = mNode::getById('Role',$role_id,'id,content');
                $ids = $cur_role['content'] ? json_decode($cur_role['content'],true) : [];
                if($ids){
                    foreach ($list as &$v){
                        if(in_array($v['id'], $ids)){
                            $v['checked'] = true;
                        }
                    }
                }
            }
            $lists = list_to_tree($list,'id','pid','children');
            res_return($lists);
        }else{
            res_return('未发现数据');
        }
    }
}