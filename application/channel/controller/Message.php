<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\channel\model\cMessage;
use app\channel\model\cTask;
use app\channel\model\cLogin;
use app\channel\model\cMaterial;
use app\common\model\myMaterial;
use weixin\wx;

class Message extends Common{
    
    //渠道公告
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['status','=',1],['type','=',1]],
                'like' => 'keyword:title'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cMessage::getPageList('Message',$where,'id,type,title,create_time',$pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['show_url'] = my_url('showInfo',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //查看公告详情
    public function showInfo(){
        $id = myRequest::getId('公告');
        $content = getBlockContent($id,'message');
        cMessage::saveRead($id);
        $this->assign('content',$content);
        return $this->fetch('showInfo');
    }
    
    
    //客服消息
    public function task(){
        if($this->request->isAjax()){
            global $loginId;
            $config = [
                'default' => [['channel_id','=',$loginId],['status','between','1,2']],
                'eq' => 'type',
                'like' => 'keyword:title'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cMessage::getPageList('Task',$where,'id,name,status,send_time,create_time',$pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['send_time'] = date('Y-m-d H:i',$v['send_time']);
                    $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                    $v['status_name'] = $v['status'] == 1 ? '已发送' : '未发送';
                    $v['do_url'] = my_url('doTask',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增任务消息
    public function addTask(){
        if($this->request->isAjax()){
            $field = 'name,send_time,is_all';
            cTask::doneTask($field);
        }else{
            $get = myValidate::getData(cTask::$rules,'video_id,book_id,activity_id','get');
            $backUrl = my_url('task');
            $url = '';
            if($get['book_id'] || $get['video_id'] || $get['activity_id']){
                $site = cLogin::getCache();
                if(!$site || !isset($site['url'])){
                    res_return('您尚未配置站点域名信息');
                }
                $url .= 'http://';
                if($site['is_location'] == 1 && $site['location_url']){
                    $url .= $site['location_url'];   
                }else{
                    $url .= $site['url'];
                }
                $short_url = '/Index/Book/info.html';
                if($get['book_id']){
                    $url .= '/Index/Book/info.html?book_id='.$get['book_id'];
                    $book = cMessage::getById('Book',$get['book_id'],'id,type');
                    if(!$book){
                        res_return('书籍信息异常');
                    }
                    switch ($book['type']){
                        case 1:
                            $backUrl = my_url('Cartoon/index');
                            break;
                        case 2:
                            $backUrl = my_url('Novel/index');
                            break;
                        case 3:
                            $backUrl = my_url('Music/index');
                            break;
                        default:
                            res_return('书籍信息异常');
                            break;
                    }
                }else{
                    if($get['video_id']){
                        $url .= '/Index/Video/info.html?video_id='.$get['video_id'];
                        $backUrl = my_url('Video/index');
                    }else{
                        if($get['activity_id']){
                            $url .= '/Index/Activity/index.html?activity_id='.$get['activity_id'];
                            $backUrl = my_url('Activity/index');
                        }
                    }
                }
            }
            $field = 'id,name,is_all:1,send_time';
            $cur = cMessage::buildArr($field);
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
            $cur['material'] = [
                'title' => $material['title'][$random],
                'picurl' => $material['cover'][$random],
                'url' => $url,
                'description' => ''
            ];
            $cur['condition'] = cTask::buildArr('sex:-1,is_charge:-1,money:-1,subscribe_time:-1');
            $variable = [
                'cur' => $cur,
                'backUrl' => $backUrl,
                'option' => cTask::getWhereOption(),
                'material' => $material
            ];
            $this->assign($variable);
            $this->assign($variable);
            return $this->fetch('doTask');
        }
    }
    
    //更新客服消息
    public function doTask(){
        if($this->request->isAjax()){
            $field = 'id,name,send_time,is_all';
            cTask::doneTask($field);
        }else{
            $id = myRequest::getId('客服消息');
            $cur = cMessage::getById('Task', $id);
            if(!$cur){
                res_return('该消息不存在');
            }
            switch ($cur['status']){
                case 1:
                    res_return('该消息已发送，禁止编辑');
                break;
                case 3:
                    res_return('该消息已被删除');
                break;
            }
            $material = cMaterial::getMaterialGroup();
            if(!$material){
                res_return('您尚未配置文案信息');
            }
            $content = json_decode($cur['material'],true);
            $cur['material'] = $content[0];
            $cur['condition'] = json_decode($cur['condition'],true);
            $cur['send_time'] = date('Y-m-d H:i:s',$cur['send_time']);
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('task'),
                'option' => cTask::getWhereOption(),
                'material' => $material
            ];
            $this->assign($variable);
            return $this->fetch('doTask');
        }
    }
    
    //测试发送客服消息
    public function testSend(){
        $content = myMaterial::getCustomMsg();
        $uid = myRequest::postId('用户','member_id');
        $member = cMessage::getById('Member', $uid,'id,channel_id,openid');
        if(isset($member['openid']) && $member['openid']){
            $config = cLogin::getCache();
            if(!$config['appid']){
                res_return('尚未配置微信参数');
            }
            if($member['channel_id'] != $config['id']){
                res_return('该用户本微信号用户，拒绝发送');
            }
            wx::$config = $config;
            $res = wx::sendCustomMessage($member['openid'], $content);
            if($res){
                res_return();
            }else{
                res_return('发送失败');
            }
        }else{
            res_return('用户信息异常');
        }
    }
    
    //删除任务消息
    public function delTask(){
        $id = myRequest::postId('客服消息');
        $re = cMessage::setField('Task', [['id','=',$id]], 'status', 3);
        if($re){
            res_return();
        }else{
            res_return('删除失败');
        }
    }
    
    //获取筛选用户数量
    public function getUserCount(){
        global $loginId;
        $info = cTask::getSendWhere($loginId);
        $count = cMessage::getCount('Member', $info['where']);
        res_return(['count'=>$count]);
    }
    
}