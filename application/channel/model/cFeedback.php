<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;

class cFeedback extends Common{
    
    
    //获取投诉列表
    public static function getFeedbackList($where,$pages){
        $list = Db::name('Feedback a')
        ->join('Member b','a.uid=b.id','left')
        ->where($where)
        ->field('a.id,a.phone,a.content,a.create_time,b.id as uid,b.nickname')
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
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Feedback a')->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
}