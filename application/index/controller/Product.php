<?php
namespace app\index\controller;
use app\index\controller\Common;
use app\common\model\myCache;
use app\common\model\mySearch;
use app\common\model\myRequest;
use app\index\model\iProduct;
use weixin\wx;

class Product extends Common{
    
    //商城首页
    public function index(){
        parent::checkBlock('shop','商城');
        $block = myCache::getWebblockCache();
        $variable = [
            'web_block' => $block,
        	'site_info' => myCache::getUrlCache(),
            'site_title' => $this->site_title
        ];
        $this->assign($variable);
        return $this->fetch();
    }
    
    //商品详情
    public function info(){
    	$id = myRequest::getId('商品','pid');
    	$product = myCache::getProductCache($id);
    	if(!$product){
    		res_return('商品不存在');
    	}
    	$urlData = myCache::getUrlCache();
    	$jsConfig = $share_data = '';
    	if($urlData['is_wx'] === 1 && $this->device_type === 1){
    		if($product['cover'] && $product['share_title'] && $product['share_desc']){
    			wx::$config = $urlData;
    			$jsConfig = wx::getJsConfig();
    			global $loginId;
    			$share_data = [
    				'title' => $product['share_title'],
    				'url' => 'http://'.$_SERVER['HTTP_HOST'].'/index/Video/info.html?pid='.$product['id'].'&share_user='.$loginId,
    				'img' => $product['cover'],
    				'desc' => $product['share_desc']
    			];
    		}
    	}
    	$variable = [
    		'cur' => $product,
    		'site_title' => $this->site_title,
    		'jsConfig' => json_encode($jsConfig,JSON_UNESCAPED_UNICODE),
    		'share_data' => json_encode($share_data,JSON_UNESCAPED_UNICODE)
    	];
    	$this->assign($variable);
    	return $this->fetch();
    }
    
    //商品下单
    public function doOrder(){
    	global $loginId;
    	if(!$loginId){
    		$this->redirect('Login/index');
    	}
    	$id = myRequest::getId('商品','pid');
    	$product = myCache::getProductCache($id);
    	if(!$product){
    		res_return('商品不存在');
    	}
    	$variable = [
    		'cur' => $product,
    		'site_title' => $this->site_title
    	];
    	$this->assign($variable);
    	return $this->fetch('doOrder');
    }
    
    //商品分类筛选
    public function category(){
    	if($this->request->isAjax()){
    		$config = [
    			'default' => [['status','=',1]],
    			'like' => 'category'
    		];
    		$where = mySearch::getWhere($config,'post');
    		$post = myRequest::post('page');
    		$page =  (is_numeric($post['page']) && $post['page'] > 0) ? $post['page'] : 1;
    		$list = iProduct::getCategoryList($where, $page);
    		$list = $list ? : 0;
    		res_return('ok',$list);
    	}else{
    		$option = iProduct::getCategoryOption();
    		$option['site_title'] = $this->site_title;
    		$this->assign($option);
    		return $this->fetch();
    	}
    }
    
    //更多页面
    public function more(){
    	if($this->request->isAjax()){
    		$post = myRequest::post('area,page,is_hot');
    		$list = 0;
    		$where = [['status','=',1]];
    		if(in_array($post['is_hot'],[1,2])){
    			$where[] = ['is_hot','=',$post['is_hot']];
    		}else{
    			if($post['area']){
    				$where[] = ['area','like','%,'.$post['area'].',%'];
    			}else{
    				res_return('ok',$list);
    			}
    		}
    		$page = $post['page']>=1 ? $post['page'] : 1;
    		$list = iProduct::getMoreList($where,$page);
    		$list = $list ? : 0;
    		res_return('ok',$list);
    	}else{
    		$get = myRequest::get('area,is_hot');
    		if($get['is_hot'] == 1){
    			$get['page_title'] = '热门推荐';
    		}else{
    			$title = $get['area'];
    			$get['page_title'] = $title;
    		}
    		$get['site_title'] = $this->site_title;
    		$this->assign('cur',$get);
    		return $this->fetch();
    	}
    }
}