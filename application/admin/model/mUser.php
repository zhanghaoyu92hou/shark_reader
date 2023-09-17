<?php
namespace app\admin\model;
use app\admin\model\Common;
use app\common\model\myValidate;
class mUser extends Common{
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'role_id' =>  ["require|number|gt:0",["require"=>"角色参数错误",'number'=>'角色ID必须是数值类型',"gt"=>"角色ID必须大于0"]],
        'name' =>  ["require|max:20",["require"=>"请输入用户名称",'max'=>'用户名称最多支持20个字符']],
    	'login_name' => ["require|alphaDash|length:5,12",["require"=>"请输入登陆账户名","alphaDash"=>'登陆账户名必须是英文、数字、下划线和破折号',"length"=>"请输入5至12位符合规范的登陆账户名"]],
        'password' => ["require|length:6,16",["require"=>"请输入登陆密码","length"=>"请输入6-16位登陆密码"]],
        'status' => ["require|in:1,2",["require"=>"请选择用户状态","in"=>"未指定该用户状态"]],
        'event' => ["require|in:on,off,delete,resetpwd",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']],
    ];
    
    //获取用户列表
    public static function getUserList($where,$pages){
        $res = parent::getPageList('Manage',$where, '*', $pages);
        if($res['data']){
            foreach ($res['data'] as &$v){
                $last_msg = mLogin::getLastLoginMsg($v['login_name']);
                $v['last_login_time'] =  $last_msg['login_time'] > 0 ? date('Y-m-d H:i',$last_msg['login_time']) : '--';
                $v['last_login_ip'] =  $last_msg['login_ip'] ? $last_msg['login_ip'] : '--';
                $v['status_name'] = $v['status'] == 1 ? '启用' : '禁用';
                $v['do_url'] = my_url('doUser',['id'=>$v['id']]);
            }
        }
        return $res;
    }
    
    
    //处理更新用户
    public static function doneUser($field){
        $data = myValidate::getData(mUser::$rules,$field);
        if(array_key_exists('id', $data)){
            if($data['id'] == 1){
                res_return('非法操作');
            }
            $re = parent::saveIdData('Manage',$data);
        }else{
            $data['password'] = createPwd($data['password']);
            $data['create_time'] = time();
            $re = parent::add('Manage', $data);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //获取更新用户选项
    public static function getOptions(){
        $role_list = parent::getList('Role', [['status','=',1]],'id,name');
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [
                    ['val'=>1,'text'=>'启用','default'=>1],
                    ['val'=>2,'text'=>'禁用','default'=>0]
                ]
            ],
            'role_list' => $role_list,
            'backUrl' => my_url('user')
        ];
        return $option;
    }
}