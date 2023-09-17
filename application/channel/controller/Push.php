<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\channel\model\cPush;
use app\common\model\myValidate;
use app\common\model\myMaterial;
use app\channel\model\cMaterial;

class Push extends Common{
    
    
    //智能推送消息
    public function index(){
        
        $variable = cPush::getPushInfo();
        $this->assign($variable);
        return $this->fetch();
    }
    
    //编辑推送消息
    public function doPush(){
        global $loginId;
        if($this->request->isAjax()){
            $type = myValidate::getData(cPush::$rules, 'type');
            if($type != 1){
                res_return('该类型消息禁止编辑');
            }
            $material = myMaterial::getCustomMsg();
            $data = [
                'channel_id' => $loginId,
                'type' => $type,
                'material' => json_encode($material,JSON_UNESCAPED_UNICODE)
            ];
            $cur = cPush::getCur('TaskMessage',[['type','=',$type],['channel_id','=',$loginId]],'id,status');
            if($cur){
                $re = cPush::save('TaskMessage', [['id','=',$cur['id']]], $data);
            }else{
                $data['status'] = 2;
                $re = cPush::add('TaskMessage', $data);
            }
            if($re){
                res_return('ok');
            }else{
                res_return('配置失败，请重试');
            }
        }else{
            $type = myValidate::getData(cPush::$rules,'type','get');
            if($type != 1){
                res_return('该类型消息禁止编辑');
            }
            $data = cPush::getCur('TaskMessage',[['type','=',$type],['channel_id','=',$loginId]]);
            $material = cMaterial::getMaterialGroup();
            if(!$material){
            	res_return('您尚未配置文案图片');
            }
            $cur = ['type'=>$type];
            if($data){
                $content = json_decode($data['material'],true);
                $cur['material'] = $content[0];
            }else{
                $random = $material['count'] > 1 ? mt_rand(1,$material['count'])-1 : 0;
                $cur['material'] = [
                    'title' => $material['title'][$random],
                    'picurl' => $material['cover'][$random],
                    'url' => '',
                    'description' => ''
                ];
            }
            $variable = [
                'cur' => $cur,
                'material' => $material,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('doPush');
        }
    }
    
    //处理推送消息状态
    public function doEvent(){
        $post = myValidate::getData(cPush::$rules, 'type,event');
        $res = cPush::donePushEvent($post);
        if($res){
            res_return('ok');
        }else{
            res_return('操作失败，请重试');
        }
    }
    
}