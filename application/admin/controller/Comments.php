<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mComments;
use app\common\model\myValidate;

class Comments extends Common{
	
	//评论列表
	public function index(){
		if($this->request->isAjax()){
			$config = [
					'default' => [['a.status','between',[1,2]]],
					'eq' => 'status:a.status',
					'like' => 'keyword:a.content',
					'rules' => ['status'=>'in:1,2','type'=>'in:1,2,3,4,5']
			];
			$pages = myRequest::getPageParams();
			$where = mySearch::getWhere($config);
			$res = mComments::getCommentsList($where,$pages);
			$time = time();
			if($res['data']){
				foreach ($res['data'] as &$v){
					$v['status_name'] = $v['status'] == 1 ? '显示' : '隐藏';
				}
			}
			res_return('ok',$res['data'],$res['count']);
		}else{
			
			return $this->fetch();
		}
	}
	
	//处理评论事件
	public function doState(){
		$field = 'id,event';
		$data = myValidate::getData(mComments::$rules,$field);
		switch ($data['event']){
			case 'on':
				$status = 1;
				break;
			case 'off':
				$status = 2;
				break;
			case 'delete':
				$status = 3;
				break;
			default:
				res_return('未指定该事件');
				break;
		}
		$re = mComments::setField('Comments', [['id','=',$data['id']]], 'status', $status);
		if($re){
			res_return();
		}else{
			res_return('操作失败');
		}
	}
	
}