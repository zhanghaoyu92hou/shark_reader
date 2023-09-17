<?php
namespace app\admin\model;
use app\admin\model\Common;
use app\common\model\myValidate;
class mRole extends Common{
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:20",["require"=>"请输入角色名称",'max'=>'角色名称最多支持20个字符']],
        'status' => ["require|in:1,2",["require"=>"请选择角色状态","in"=>"未指定该角色状态"]],
        'summary' => ["max:500",["max"=>"角色描述字数超出限制"]],
        'content' => ["require|array",["require"=>"请选择该角色权限",'array'=>'权限集格式错误']],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //处理更新角色
    public static function doneRole($field){
        $data = myValidate::getData(self::$rules,$field);
        $data['content'] = json_encode($data['content']);
        if(array_key_exists('id', $data)){
            $re = parent::saveIdData('Role',$data);
        }else{
            $data['create_time'] = time();
            $re = parent::add('Role', $data);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //获取更新角色选项
    public static function getOptions(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [
                    ['val'=>1,'text'=>'启用','default'=>1],
                    ['val'=>2,'text'=>'禁用','default'=>0]
                ]
            ],
            'backUrl' => my_url('role')
        ];
        return $option;
    }
}