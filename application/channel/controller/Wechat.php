<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\channel\model\cLogin;
use app\channel\model\cWx;
use app\common\model\myLinks;
use app\channel\model\cMaterial;
use weixin\wx;

class Wechat extends Common{
    
    //菜单列表
    public function menu(){
        if($this->request->isAjax()){
            global $loginId;
            $list = cWx::getMenuList($loginId);
            if($list){
                foreach ($list as &$v){
                    $v['type_name'] = cWx::getMenuTypeName($v['type']);
                    $v['value'] = '';
                    if($v['content']){
                        $content = json_decode($v['content'],true);
                        $v['value'] = $content['value'];
                    }
                    $v['do_url'] = my_url('doMenu',['id'=>$v['id']]);
                    $v['add_url'] = my_url('addMenu',['parent_id'=>$v['id']]);
                }
            }
            $res =  [
                'errno' => 0,
                'msg' => 'ok',
                'data' => $list
            ];
            echo json_encode($res,JSON_UNESCAPED_UNICODE);
            exit;
        }else{
            
            return $this->fetch('menu');
        }
    }
    
    //添加菜单
    public function addMenu(){
        if($this->request->isAjax()){
            $field = 'pid,name,type,value,program_id';
            cWx::doneMenu($field);
        }else{
            $field = 'id,pid,name,type:0,content';
            $cur = cWx::buildArr($field);
            $cur['parent_name'] = '作为顶级菜单';
            $get = myRequest::get('parent_id');
            $parent_id = $get['parent_id'] ? : 0;
            $count = cWx::getMenuCount($parent_id);
            if($parent_id){
                $parent = cWx::getById('WxMenu',$parent_id,'id,name');
                if(!$parent){
                    res_return('顶级菜单不存在');
                }
                if($count >= 5){
                    res_return('最多允许添加5个子菜单');
                }
                $cur['parent_name'] = $parent['name'];
            }else{
                if($count >= 3){
                    res_return('最多允许添加3个顶级菜单');
                }
            }
            $option = cWx::getMenuOption();
            $cur['pid'] = $parent_id;
            $cur['content'] = ['value'=>'','appid'=>''];
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doMenu');
        }
    }
    
    //编辑菜单
    public function doMenu(){
        if($this->request->isAjax()){
            $field = 'id,pid,name,type,value,program_id';
            cWx::doneMenu($field);
        }else{
            $id = myRequest::getId('微信菜单');
            $cur = cWx::getById('WxMenu', $id);
            if(!$cur){
                res_return('菜单不存在');
            }
            $cur['parent_name'] = '作为顶级菜单';
            if($cur['pid'] > 0){
                $parent = cWx::getById('WxMenu',$cur['pid'],'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $cur['content'] = $cur['content'] ? json_decode($cur['content'],true) : ['value'=>'','appid'=>''];
            $option = cWx::getMenuOption();
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doMenu');
        }
    }
    
    
    //处理菜单排序及删除节点
    public function doMenuEvent(){
        $field = 'id,event';
        $data = myValidate::getData(cWx::$rules,$field);
        if($data['event'] === 'delete'){
            $re = cWx::deleteMenu($data['id']);
        }else{
            $re = cWx::doMenuSort($data);
        }
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //发布菜单
    public function pushMenu(){
        global $loginId;
        $data = cWx::getMenuList($loginId);
        $treeData = list_to_tree($data,'id','pid','child');
        $config = cLogin::getCache();
        wx::$config = $config;
        $res = wx::createMenu($treeData);
        if($res){
            res_return();
        }else{
            res_return('菜单发布失败');
        }
    }
    
    //创建默认菜单
    public function createDefaultMenu(){
    	
    	cWx::createDefaultMenu();
    }
    
    
    //自动回复
    public function reply(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['status','between',[1,2]],['channel_id','=',$loginId]],
                'eq' => 'status',
                'like' => 'keyword'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cWx::getPageList('WxReply',$where, 'id,keyword,type,status', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '启用' : '禁用';
                    $v['type_name'] = $v['type'] == 1 ? '文本' : '图文';
                    $v['do_url'] = my_url('doReply',['id'=>$v['id']]);
                }
            }
            $this->success('ok','',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //添加关键字
    public function addReply(){
        if($this->request->isAjax()){
            $field = 'type,status,keyword,content';
            cWx::doneReply($field);
        }else{
            $field = 'id,type:0,status,keyword,content';
            $option = cWx::getReplyOption();
            $cur = cWx::buildArr($field);
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
            $cur['material'] = [
                'title' => $material['title'][$random],
                'picurl' => $material['cover'][$random],
                'url' => '',
                'description' => ''
            ];
            $option['cur'] = $cur;
            $option['material'] = $material;
            $option['backUrl'] = my_url('reply');
            $this->assign($option);
            return $this->fetch('doReply');
        }
    }
    
    //编辑关键字
    public function doReply(){
        if($this->request->isAjax()){
            $field = 'id,type,status,keyword,content';
            cWx::doneReply($field);
        }else{
            $id = myRequest::getId('关键字');
            $cur = cWx::getById('WxReply',$id,'id,type,status,keyword,content,material');
            if(!$cur){
                res_return('关键字不存在');
            }
            $option = cWx::getReplyOption();
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $cur['material'] = json_decode($cur['material'],true);
            if($cur['material']){
                $cur['material'] = $cur['material'][0];
            }else{
                $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
                $cur['material'] = [
                    'title' => $material['title'][$random],
                    'picurl' => $material['cover'][$random],
                    'url' => '',
                    'description' => ''
                ];
            }
            $option['cur'] = $cur;
            $option['material'] = $material;
            $option['backUrl'] = my_url('reply');
            $this->assign($option);
            return $this->fetch('doReply');
        }
    }
    
    //处理关键字事件
    public function doReplyEvent(){
        $field = 'id,event';
        $data = myValidate::getData(cWx::$reply,$field);
        switch ($data['event']){
            case 'on':
                $status = 1;
                break;
            case 'off':
                $status = 2;
                break;
            case 'delete':
                $re = \think\Db::name('WxReply')->where('id', $data['id'])->delete();
                if ($re) {
                    res_return();
                } else {
                    res_return('操作失败,请重试');
                }
                exit;
                break;
        }
        $re = cWx::setField('WxReply', [['id','=',$data['id']]], 'status', $status);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //特殊回复
    public function special(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['status','between',[1,2]],['channel_id','=',$loginId]],
                'eq' => 'status',
                'like' => 'keyword'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cWx::getPageList('WxSpecial',$where, 'id,keyword,type,status', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '启用' : '禁用';
                    $v['type_name'] = $v['type'] == 1 ? '文本' : '图文';
                    $v['do_url'] = my_url('doSpecial',['id'=>$v['id']]);
                }
            }
            $this->success('ok','',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //添加特殊回复
    public function addSpecial(){
        if($this->request->isAjax()){
            $field = 'type,status,keyword,content';
            cWx::doneSpecial($field);
        }else{
            $field = 'id,type,status,keyword,content';
            $option = cWx::getReplyOption();
            $cur = cWx::buildArr($field);
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
            $cur['material'] = [
                'main' => [
                    'title' => $material['title'][$random],
                    'picurl' => $material['cover'][$random],
                    'url' => '',
                    'description' => ''
                ]
            ];
            $option['cur'] = $cur;
            $option['material'] = $material;
            $option['backUrl'] = my_url('special');
            $this->assign($option);
            return $this->fetch('doSpecial');
        }
    }
    
    //编辑特殊回复
    public function doSpecial(){
        if($this->request->isAjax()){
            $field = 'id,type,status,keyword,content';
            cWx::doneSpecial($field);
        }else{
            $id = myRequest::getId('回复');
            $cur = cWx::getById('WxSpecial',$id,'id,type,status,keyword,content,material');
            if(!$cur){
                res_return('关键字不存在');
            }
            $option = cWx::getReplyOption();
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $cur['material'] = json_decode($cur['material'],true);
            if($cur['material']){
                $main = $child = [];
                $key = 1;
                foreach ($cur['material'] as $v){
                    if($key == 1){
                        $main = $v;
                        $key++;
                    }else{
                        $child[] = $v;
                    }
                }
                $cur['material'] = ['main' => $main,'child' => $child];
            }else{
                $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
                $cur['material'] = [
                    'main' => [
                        'title' => $material['title'][$random],
                        'picurl' => $material['cover'][$random],
                        'url' => '',
                        'description' => ''
                    ]
                ];
            }
            $option['cur'] = $cur;
            $option['material'] = $material;
            $option['backUrl'] = my_url('special');
            $this->assign($option);
            return $this->fetch('doSpecial');
        }
    }
    
    //处理关键字事件
    public function doSpecialEvent(){
        $field = 'id,event';
        $data = myValidate::getData(cWx::$reply,$field);
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
        $re = cWx::setField('WxSpecial', [['id','=',$data['id']]], 'status', $status);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //公众号参数配置
    public function param(){
        global $loginId;
        $field = 'appid,appsecret,apptoken,qrcode';
        if($this->request->isAjax()){
        	$cur = cWx::getById('Channel', $loginId,'id,url,location_url');
            $data = myValidate::getData(cWx::$rules, $field);
            $re = cWx::save('Channel',[['id','=',$loginId]], $data);
            if($re){
                $key = 'channel_info_'.$cur['id'];
                cache($key,null);
                if($cur['url']){
                    cache(md5($cur['url']),null);
                }
                if($cur['location_url']){
                    cache(md5($cur['location_url']),null);
                }
                cache('CHANNEL_USER_'.$loginId,null);
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $field .= ',url';
            $cur = cWx::getById('Channel',$loginId,$field);
            $url = $callback = '';
            if($cur && $cur['url']){
                $url = $cur['url'];
                $callback = 'http://'.$url.'/Index/Open/callback.html';
            }
            $variable = [
                'cur' => $cur,
                'url' => $url,
                'callback' => $callback
            ];
            $this->assign($variable);
            return $this->fetch();
        }
    }
    
    //获取链接
    public function links(){
        $list = myLinks::getAll();
        $config = cLogin::getCache();
        if(!$config || !$config['url']){
            res_return('您尚未配置站点域名');
        }
        $url = $config['is_location'] == 1 ? $config['location_url'] : $config['url'];
        foreach ($list as &$v){
            foreach ($v['links'] as &$val){
                $val['long_url'] = 'http://'.$url.$val['url'];
            }
        }
        $this->assign('list',$list);
        return $this->fetch();
    }
}