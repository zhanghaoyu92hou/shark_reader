<?php
namespace app\index\controller;
use app\index\controller\Common;
use app\common\model\myRequest;
use app\common\model\myCache;
use app\index\model\iOrder;

class Activity extends Common{
	
	//充值活动
	public function index(){
		global $loginId;
		parent::checkLogin();
		$id = myRequest::getId('活动','activity_id');
		$activity = myCache::getActivityCache($id);
		if($activity){
			$time = time();
			if($time < $activity['start_time']){
				return self::errPage('亲，您真是未卜先知啊！','活动未开始');
			}
			if($time > $activity['end_time']){
				return self::errPage('对不起，您来晚了！','活动已结束');
			}
			if($activity['is_first'] == 1){
				$repeat = iOrder::checkRepeat($id, $loginId);
				if($repeat){
					return self::errPage('对不起，您已经充值过了！','该活动仅限充值一次');
				}
			}
			$variable = [
				'cur' => $activity,
				'site_title' => $this->site_title
			];
			$this->assign($variable);
			return $this->fetch();
		}else{
			return self::errPage('您好像迷路了！','活动不存在');
		}
	}
	
	//活动错误页
	private function errPage($title,$sub_title){
		$msg = [
				'title' => $title,
				'sub_title' => $sub_title
		];
		$this->assign('msg',$msg);
		return $this->fetch('errPage');
		exit;
	}
}