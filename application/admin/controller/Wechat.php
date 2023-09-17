<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\admin\model\mWx;
use app\common\model\mySearch;
use app\admin\model\mConfig;
use app\common\model\myValidate;
use app\common\model\myLinks;
use app\common\model\myCache;
use weixin\wx;
use app\admin\model\mMaterial;

class Wechat extends Common{
    
    //菜单列表
    public function menu(){
        if($this->request->isAjax()){
            $list = mWx::getMenuList();
            if($list){
                foreach ($list as &$v){
                    $v['type_name'] = mWx::getMenuTypeName($v['type']);
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
            mWx::doneMenu($field);
        }else{
            $field = 'id,pid,name,type:0,content';
            $cur = mWx::buildArr($field);
            $cur['parent_name'] = '作为顶级菜单';
            $get = myRequest::get('parent_id');
            $parent_id = $get['parent_id'] ? : 0;
            $count = mWx::getMenuCount($parent_id);
            if($parent_id){
                $parent = mWx::getById('WxMenu',$parent_id,'id,name');
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
            $option = mWx::getMenuOption();
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
            mWx::doneMenu($field);
        }else{
            $id = myRequest::getId('菜单');
            $cur = mWx::getById('WxMenu', $id);
            if(!$cur){
                res_return('菜单不存在');
            }
            $cur['parent_name'] = '作为顶级菜单';
            if($cur['pid'] > 0){
                $parent = mWx::getById('WxMenu',$cur['pid'],'id,name');
                $cur['parent_name'] = $parent['name'];
            }
            $cur['content'] = $cur['content'] ? json_decode($cur['content'],true) : ['value'=>'','appid'=>''];
            $option = mWx::getMenuOption();
            $option['cur'] = $cur;
            $this->assign($option);
            return $this->fetch('doMenu');
        }
    }
    
    //处理菜单排序及删除节点
    public function doMenuEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mWx::$rules,$field);
        if($data['event'] === 'delete'){
            $re = mWx::deleteMenu($data['id']);
        }else{
            $re = mWx::doMenuSort($data);
        }
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //发布菜单
    public function pushMenu(){
        $data = mWx::getMenuList();
        $treeData = list_to_tree($data,'id','pid','child');
        $config = myCache::getSiteWeixinCache();
        wx::$config = $config;
        $res = wx::createMenu($treeData);
        if($res){
            res_return();
        }else{
            res_return('菜单发布失败');
        }
    }
    
    //创建默认微信菜单
    public function createDefaultMenu(){
    	
    	mWx::createDefaultMenu();
    }
    
    
    //自动回复
    public function reply(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[1,2]],['channel_id','=',0]],
                'eq' => 'status',
                'like' => 'keyword'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mWx::getPageList('WxReply',$where, 'id,keyword,type,status', $pages);
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
            mWx::doneReply($field);
        }else{
            $field = 'id,type,status,keyword,content';
            $option = mWx::getReplyOption();
            $cur = mWx::buildArr($field);
            $material = mMaterial::getMaterialGroup();
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
            $option['material'] = $material;
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('reply');
            $this->assign($option);
            return $this->fetch('doReply');
        }
    }
    
    //编辑关键字
    public function doReply(){
        if($this->request->isAjax()){
            $field = 'id,type,status,keyword,content';
            mWx::doneReply($field);
        }else{
            $id = myRequest::getId('关键字');
            $cur = mWx::getById('WxReply',$id,'id,type,status,keyword,content,material');
            if(!$cur){
                res_return('关键字不存在');
            }
            $option = mWx::getReplyOption();
            $material = mMaterial::getMaterialGroup();
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
            $option['material'] = $material;
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('reply');
            $this->assign($option);
            return $this->fetch('doReply');
        }
    }
    
    
    
    //处理关键字事件
    public function doReplyEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mWx::$reply,$field);
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
        $re = mWx::setField('WxReply', [['id','=',$data['id']]], 'status', $status);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //特殊回复
    public function special(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','between',[1,2]],['channel_id','=',0]],
                'eq' => 'status',
                'like' => 'keyword'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mWx::getPageList('WxSpecial',$where, 'id,keyword,type,status', $pages);
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
            mWx::doneSpecial($field);
        }else{
            $field = 'id,type,status,keyword,content';
            $option = mWx::getReplyOption();
            $cur = mWx::buildArr($field);
            $material = mMaterial::getMaterialGroup();
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
            mWx::doneSpecial($field);
        }else{
            $id = myRequest::getId('回复');
            $cur = mWx::getById('WxSpecial',$id,'id,type,status,keyword,content,material');
            if(!$cur){
                res_return('关键字不存在');
            }
            $option = mWx::getReplyOption();
            $material = mMaterial::getMaterialGroup();
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
        $data = myValidate::getData(mWx::$reply,$field);
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
        $re = mWx::setField('WxSpecial', [['id','=',$data['id']]], 'status', $status);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
    //公众号参数配置
    public function param(){
        $field = 'appid,appsecret,apptoken,qrcode';
        $key = 'weixin';
        if($this->request->isAjax()){
            $data = myValidate::getData(mWx::$rules, $field);
            $re = mConfig::saveConfig($key, $data);
            if($re){
                cache($key,$data);
                $website = myCache::getWebSiteCache();
                if($website['url']){
                    cache(md5($website['url']),null);
                }
                if($website['location_url']){
                    cache(md5($website['location_url']),null);
                }
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if(!$cur){
                $cur = mWx::buildArr($field);
                $re = mConfig::addConfig($key, $cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $website = mConfig::getConfig('website');
            $url = $callback = '';
            if($website && $website['url']){
                $url = $website['url'];
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
        $config = mConfig::getConfig('website');
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