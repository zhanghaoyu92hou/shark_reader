<?php
namespace app\admin\model;
use app\admin\model\Common;
use app\common\model\myValidate;
use think\Db;

class mMessage extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'title' =>  ["require|max:100",["require"=>"请输入公告标题",'max'=>'公告标题最多支持100个字符']],
        'type' => ["require|in:1,2",["require"=>"请选择公告类型","in"=>"未指定该公告类型"]],
        'content' => ["require",["require"=>"请输入公告内容"]]
    ];
    
    //处理更新公告消息
    public static function doneMessage($field){
        $data = myValidate::getData(self::$rules,$field);
        $content = $data['content'];
        unset($data['content']);
        $message_id = 0;
        if(array_key_exists('id', $data)){
            $message_id = $data['id'];
            $re = parent::saveIdData('Message',$data);
        }else{
            $data['status'] = 1;
            $data['create_time'] = time();
            $re = Db::name('Message')->insertGetId($data);
            $message_id = $re;
        }
        if($re){
            saveBlock($content,$message_id,'message');
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //获取更新消息选项
    public static function getMessageOption(){
        $option = [
            'type' => [
                'name' => 'type',
                'option' => [
                    ['val'=>1,'text'=>'代理','default'=>1],
                    ['val'=>2,'text'=>'个人','default'=>0]
                ]
            ]
        ];
        return $option;
    }
    
}