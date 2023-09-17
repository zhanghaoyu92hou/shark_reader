<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;

class mComments extends Common{
	
	public static $rules = [
		'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
		'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
	];
	
	//获取评论列表
	public static function getCommentsList($where,$pages){
		$field = 'a.*,b.nickname';
		$list = Db::name('Comments a')
		->join('member b','a.uid=b.id','left')
		->where($where)
		->field($field)
		->group('a.id')
		->page($pages['page'],$pages['limit'])
		->order('a.id','DESC')
		->select();
		$count = 0;
		if($list){
			$count = Db::name('Comments a')->where($where)->count();
		}
		return ['count'=>$count,'data'=>$list];
	}
}
