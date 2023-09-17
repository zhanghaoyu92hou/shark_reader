<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
use app\common\model\myValidate;

class mFeedback extends Common{
	
    public static $rules = [
    	'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数不规范',"gt"=>"主键参数不规范"]],
    	'reply' => ["require|max:255",["require"=>'请输入回复内容',"max"=>'回复字数超出限制']],
    ];
    
    //获取反馈列表
    public static function getFeedbackList($where,$pages){
        $list = Db::name('Feedback a')
        ->join('Member b','a.uid=b.id','left')
        ->where($where)
        ->field('a.id,a.phone,a.content,a.reply,a.create_time,b.id as uid,b.nickname')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
            	if(!$v['uid']){
            		$v['nickname'] = '<font class="text-red">用户异常</font>';
            	}
            	$v['phone'] = $v['phone'] ? : '/';
            	$v['is_reply'] = $v['reply'] ? 1 : 2;
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Feedback a')->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //处理反馈回复
    public static function doFeedback(){
    	$field = 'id,reply';
    	$data = myValidate::getData(self::$rules, $field);
    	$cur = Db::name('Feedback')->where('id','=',$data['id'])->field('id,reply')->find();
    	if(!$cur){
    		res_return('该反馈信息不存在');
    	}
    	if($cur['reply']){
    		res_return('该反馈信息已回复');
    	}
    	$reply = htmlspecialchars($data['reply']);
    	$re = Db::name('Feedback')->where('id','=',$data['id'])->setField('reply',$reply);
    	if($re){
    		res_return();
    	}else{
    		res_return('回复失败');
    	}
    }
}