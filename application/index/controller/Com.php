<?php
namespace app\index\controller;
use app\index\controller\Common;
use app\common\model\myRequest;
use app\common\model\myCache;
use app\common\model\myValidate;
use app\index\model\iMessage;
use think\Db;

class Com extends Common{
	
	//书籍收藏
	public function doCollect(){
		global $loginId;
		if(!$loginId){
			res_return('您尚未登陆');
		}
		$book_id = myRequest::postId('书籍','book_id');
		$repeat = Db::name('MemberCollect')
		->where('book_id','=',$book_id)
		->where('uid','=',$loginId)
		->value('id');
		if($repeat){
			res_return('您已收藏过该书籍');
		}
		$data = [
				'book_id' => $book_id,
				'uid' => $loginId,
				'create_time' => time()
		];
		$re = Db::name('MemberCollect')->insert($data);
		if($re){
			res_return();
		}else{
			res_return('收藏失败');
		}
	}

    //视频1收藏
    public function dovideoCollect(){
        global $loginId;
        if(!$loginId){
            res_return('您尚未登陆');
        }
        $video_id = myRequest::postId('书籍','video_id');
        $repeat = Db::name('video_collection')
            ->where('video_id','=',$video_id)
            ->where('uid','=',$loginId)
            ->value('id');
        if($repeat){
            res_return('您已收藏过该视频');
        }

        $data = [
            'video_id' => $video_id,
            'uid' => $loginId,
            'create_time' => time()
        ];
        $re = Db::name('video_collection')->insert($data);
        if($re){
            res_return();
        }else{
            res_return('收藏失败');
        }
    }
	//检查书籍是否收藏
	public function doCheckCollect(){
		global $loginId;
		if(!$loginId){
			res_return(['flag'=>2]);
		}
		$book_id = myRequest::postId('书籍','book_id');
		$repeat = Db::name('MemberCollect')
		->where('book_id','=',$book_id)
		->where('uid','=',$loginId)
		->value('id');
		if($repeat){
			res_return(['flag'=>1]);
		}else{
			res_return(['flag'=>2]);
		}
	}
	
	//商品评论
	public function comments(){
		$rules = [
			'pid' => ['require|number|gt:0',['require'=>'评论对象参数错误','number'=>'评论对象参数不规范','gt'=>'评论参数对象不规范']],
			'type' => ['require|in:1,2,3,4',['require'=>'评论对象参数错误','in'=>'评论对象参数不规范']],
			'page' => ['require|number|gt:0',['require'=>'分页参数错误','number'=>'分页参数不规范','gt'=>'分页参数不规范']],
		];
		if($this->request->isAjax()){
			$post = myValidate::getData($rules,'pid,type,page');
			$where = [['a.pid','=',$post['pid']],['a.type','=',$post['type']],['a.status','=',1]];
			$list = Db::name('Comments a')
			->join('member b','a.uid=b.id')
			->where($where)
			->field('a.id,a.content,a.create_time,b.nickname,b.headimgurl')
			->page($post['page'],10)
			->order('a.id','desc')
			->select();
			$count = 0;
			if($list){
				foreach ($list as &$v){
					$v['create_time'] = date('Y-m-d',$v['create_time']);
				}
				if($post['page'] == 1){
					$count = Db::name('Comments a')
					->join('member b','a.uid=b.id')
					->where($where)
					->count();
				}
			}else{
				$list = '';
			}
			res_return(['list'=>$list,'count'=>$count]);
		}else{
			global $loginId;
			if(!$loginId){
				$this->redirect('Login/index');
			}
			$memeber = myCache::getUserCache($loginId);
			if(!$memeber){
				$this->redirect('Login/index');
			}
			$get = myValidate::getData($rules, 'pid,type','get');
			$variable = [
				'cur' => $get,
				'user' => $memeber,
				'site_title' => $this->site_title
			];
			$this->assign($variable);
			return $this->fetch();
		}
	}
	
	//发表评论
	public function doComments(){
		global $loginId;
		if(!$loginId){
			res_return('您尚未登陆');
		}
		$rules = [
			'pid' => ['require|number|gt:0',['require'=>'评论对象参数错误','number'=>'评论对象参数不规范','gt'=>'评论参数对象不规范']],
			'type' => ['require|in:1,2,3,4',['require'=>'评论对象参数错误','in'=>'评论对象参数不规范']],
			'content' => ['require|max:200',['require'=>'请输入评论内容','max'=>'评论字数超出限制']],
		];
		$data = myValidate::getData($rules,'pid,type,content');
		$data['content'] = htmlspecialchars($data['content']);
		$data['uid'] = $loginId;
		$data['status'] = 2;
		$data['create_time'] = time();
		$data['pname'] = '未知';
		if($data['type'] == 4){
			$video = myCache::getVideoCache($data['pid']);
			if($video){
				$data['pname'] = $video['name'];
			}
		}else{
			$book = myCache::getBookCache($data['pid']);
			if($book){
				$data['pname'] = $book['name'];
			}
		}
		$re = Db::name('Comments')->insert($data);
		if($re){
			res_return();
		}else{
			res_return('发表失败，请重试');
		}
	}
	
	//获取评论
	public function getComments(){
		$post = myRequest::post('pid,ptype');
		$list = 0;
		if($post['pid'] > 0 && in_array($post['ptype'], [1,2,3,4])){
			$where = [['a.pid','=',$post['pid']],['a.type','=',$post['ptype']],['a.status','=',1]];
			$list = Db::name('Comments a')
			->join('Member b','a.uid=b.id')
			->where($where)
			->field('a.id,a.content,a.create_time,b.nickname,b.headimgurl')
			->order('a.id','desc')
			->limit(4)
			->select();
			$count = 0;
			if($list){
				foreach ($list as &$v){
					$v['create_time'] = date('Y-m-d',$v['create_time']);
				}
				$count = Db::name('Comments a')
				->join('member b','a.uid=b.id')
				->where($where)
				->count();
			}else{
				$list = '';
			}
		}
		res_return(['list'=>$list,'count'=>$count]);
	}
	
	//投诉
	public function complaint(){
		global $loginId;
		if($this->request->isAjax()){
			if(!$loginId){
				res_return('您尚未登陆');
			}
			$user = myCache::getUserCache($loginId);
			if(!$user){
				session('INDEX_LOGIN_ID',null);
				res_return('登陆信息异常');
			}
			$book_id = myRequest::postId('书籍','book_id');
			$book = myCache::getBookCache($book_id);
			if(!$book){
				res_return('投诉书籍异常');
			}
			$type = myRequest::postId('投诉类型','type');
			if(!in_array($type, [1,2,3,4,5,6])){
				res_return('未指定该投诉类型');
			}
			$data = [
				'channel_id' => $user['channel_id'],
				'agent_id' => $user['agent_id'],
				'book_id' => $book['id'],
				'book_name' => $book['name'],
				'uid' => $loginId,
				'type' => $type,
				'create_time' => time()
			];
			$re = Db::name('Complaint')->insert($data);
			if($re){
				res_return();
			}else{
				res_return('投诉失败，请重试');
			}
		}else{
			if(!$loginId){
				$this->redirect('Login/index');
			}
			$book_id = myRequest::getId('书籍','book_id');
			$variable = [
				'book_id' => $book_id,
				'site_title' => $this->site_title
			];
			$this->assign($variable);
			return $this->fetch();
		}
	}
	
	//检查是否是首次访问
	public function checkView(){
		$flag = 'no';
		if($this->device_type == 1){
			global $loginId;
			if($loginId){
				$re = Db::name('ViewRecord')->where('uid','=',$loginId)->value('id');
				if(!$re){
					$res = Db::name('ViewRecord')->insert(['uid'=>$loginId,'create_time'=>time()]);
					if($res){
						$flag = 'yes';
					}
				}
			}
		}
		res_return(['flag'=>$flag]);
	}
	
	//发送短信验证码
	public function sendMessageCode(){
		$rules = ['phone' =>  ["require|mobile",['require'=>'请输入手机号','mobile'=>'请输入正确格式的手机号']]];
		$phone = myValidate::getData($rules, 'phone');
		iMessage::send($phone);
	}
	//发送绑定验证码
	public function sendBindCode(){
		$rules = ['phone' =>  ["require|mobile",['require'=>'请输入手机号','mobile'=>'请输入正确格式的手机号']]];
		$phone = myValidate::getData($rules, 'phone');
		$re = Db::name('Member')->where('phone','=',$phone)->value('id');
		if($re){
			res_return('该手机号已被绑定');
		}
		iMessage::send($phone);
	}
	
	//登陆时发送短信验证码
	public function sendLoginCode(){
		$rules = ['phone' =>  ["require|mobile",['require'=>'请输入手机号','mobile'=>'请输入正确格式的手机号']]];
		$phone = myValidate::getData($rules, 'phone');
//		$re = Db::name('Member')->where('phone','=',$phone)->value('id');
//		if(!$re){
//			res_return('ok',1);
//		}
		iMessage::send($phone);
	}
}