<?php
namespace app\index\model;
use app\index\model\Common;
use think\Db;
use app\common\model\myCache;
use app\common\model\myRequest;
class iProduct extends Common{
	
	//获取商品分类列表
	public static function getCategoryList($where,$page){
		$field = 'id,money,name,cover,category,summary,buy_num';
		$list = Db::name('Product')
		->where($where)
		->field($field)
		->page($page,10)
		->order('id','DESC')
		->select();
		if($list){
			foreach ($list as &$v){
				$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
				$v['category'] = !empty($category) ? $category : '';
				$v['buy_num'] = $v['buy_num'] > 10000 ? round($v['buy_num']/10000,2).'万' : $v['buy_num'];
				$v['info_url'] = my_url('info',['pid'=>$v['id']]);
			}
		}
		return $list;
	}
	
	//获取商品分类选项
	public static function getCategoryOption(){
		$get = myRequest::get('category');
		$cateConfig = myCache::getBookConfigCache('product_category');
		$temp = [['name'=>'全部','val'=>'','is_check'=>1]];
		if($cateConfig){
			$is_check = false;
			if($get['category'] && in_array($get['category'], $cateConfig)){
				$is_check = true;
				$temp = [['name'=>'全部','val'=>'','is_check'=>0]];
			}
			foreach ($cateConfig as $v){
				if($is_check && $get['category'] === $v){
					$temp[] = ['name'=>$v,'val'=>$v,'is_check'=>1];
				}else{
					$temp[] = ['name'=>$v,'val'=>$v,'is_check'=>0];
				}
			}
		}
		return ['category'=>$temp];
	}
	
	//获取更多商品
	public static function getMoreList($where,$page){
		$field = 'id,name,cover,summary,money,buy_num';
		$list = Db::name('Product')
		->where($where)
		->field($field)
		->page($page,10)
		->order('hot_num','desc')
		->select();
		if($list){
			foreach ($list as &$v){
				$v['info_url'] = my_url('Product/info',['pid'=>$v['id']]);
				$v['buy_url'] = my_url('Product/doOrder',['pid'=>$v['id']]);
			}
		}
		return $list;
	}
}
