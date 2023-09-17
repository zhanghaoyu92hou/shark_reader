<?php
namespace app\index\controller;
use app\index\controller\Common;
use think\Db;
use app\common\model\myRequest;
class Search extends Common{
	
	public function index(){
		global $loginId;
		if($this->request->isAjax()){
			$post = myRequest::post('keyword');
			$keyword = $post['keyword'] ? : '';
			if(!$keyword){
				res_return('未键入关键字');
			}
			if($loginId){
				$repeat = Db::name('SearchRecord')->where('keyword','=',$keyword)->where('uid','=',$loginId)->value('id');
				if(!$repeat){
					Db::name('SearchRecord')->insert(['uid'=>$loginId,'keyword'=>$keyword]);
				}
			}
			$where = [['a.status','=',1],['a.name|a.author|a.lead','like','%'.$keyword.'%']];
			$field = 'a.id,a.name,a.type,a.cover,a.summary,a.category,a.over_type,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
			$list = Db::name('Book a')
				->join('book_chapter b','a.id=b.book_id','left')
				->where($where)
				->field($field)
				->group('a.id')
				->select();
			if($list){
				foreach ($list as &$v){
					$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
					$v['category'] = !empty($category) ? $category : '';
					$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
					$v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
					$v['info_url'] = my_url('Book/info',['book_id'=>$v['id']]);
					$v['type'] = intval($v['type']);
				}
			}else{
				$list = '';
			}
			res_return('ok',$list);
		}else{
			$this->assign('site_title',$this->site_title);
			return $this->fetch();
		}
	}
	
	//获取搜索历史
	public function getHistory(){
		global $loginId;
		$history = '';
		if($loginId){
			$history = Db::name('SearchRecord')->where('uid','=',$loginId)->order('id','desc')->limit(10)->select();
		}
		$history = $history ? : '';
		res_return('ok',$history);
	}
	
	//删除全部
	public function delAll(){
		global $loginId;
		if($loginId){
			Db::name('SearchRecord')->where('uid','=',$loginId)->delete();
		}
		res_return();
	}
	
	//删除单条
	public function delOne(){
		global $loginId;
		if($loginId){
			$id = myRequest::postId('搜索');
			Db::name('SearchRecord')->where('id','=',$loginId)->where('uid','=',$loginId)->delete();
		}
		res_return();
	}
}