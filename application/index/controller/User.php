<?php
namespace app\index\controller;
use app\index\controller\Common;
use app\common\model\myRequest;
use app\index\model\iBook;
use app\index\model\iVideo;
use app\common\model\myCache;
use app\index\model\iMember;
use app\common\model\myValidate;
use app\index\model\iMessage;
use think\Db;

class User extends Common{
	
	//构造函数
	public function __construct(){
		parent::__construct();
		parent::checkLogin();
	}
    
    //个人中心
    public function index(){
        global $loginId;
        $cur = myCache::getUserCache($loginId);
        $h = date('H');
        if($h <= 9){
        	$str = '早上好';
        }elseif ($h > 9 && $h <= 11){
        	$str = '上午好';
        }elseif ($h > 11 && $h <= 13){
        	$str = '中午好';
        }elseif ($h > 13 && $h <= 18){
        	$str = '下午好';
        }else{
        	$str = '晚上好';
        }
        $near_act = [];
        $activity = myCache::getNearActivityCache();
        if($activity && $activity['cover']){
        	$near_act['url'] = '/index/Activity/index.html?activity_id='.$activity['id'];
        	$near_act['cover'] = $activity['cover'];
        }
        $site = myCache::getWebSiteCache();
        if(!$site){
        	res_return('尚未配置站点信息');
        }
        $signInfo = '';
        if($site['is_sign'] == 1){
        	$sign_config = $site['sign_config'];
        	$signInfo = iMember::checkSign($sign_config, $loginId);
        }
        $contact = [
        	'qq' => $site['contactQQ'],
        	'wx' => $site['contactWx'],
        	'tel' => $site['contactTel']
        ];
        $variable = [
        	'cur' => $cur,
        	'good_str' => $str,
        	'contact' => $contact,
        	'sign_info' => $signInfo,
        	'activity' => $near_act,
        	'site_title' => $this->site_title
        ];
        $this->assign($variable);
    	return $this->fetch();
    }
    
    //设置自动订阅
    public function setAuto(){
    	global $loginId;
    	$post = myRequest::post('is_on');
    	if(!in_array($post['is_on'], ['yes','no'])){
    		res_return('参数有误');
    	}
    	$is_auto = $post['is_on'] === 'yes' ? 1 : 2;
    	$res = iMember::setAuto($loginId, $is_auto);
    	$str = $res ? 'ok' : '设置失败';
    	res_return($str);
    }
    
    //绑定手机号
    public function bindPhone(){
    	global $loginId;
        if($this->request->isAjax()){
        	$rules = [
        		'phone' =>  ["require|mobile",['require'=>'请输入手机号','mobile'=>'请输入正确格式的手机号']],
        		'code' => ['require|number|length:6',['require'=>'请输入验证码','number'=>'验证码格式错误','length'=>'验证码为6位数字']]
        	];
        	$data = myValidate::getData($rules, 'phone,code');
        	iMessage::check($data['phone'], $data['code']);
        	$res = iMember::bindPhone($loginId, $data['phone']);
        	res_return($res);
        }else{
        	$user = myCache::getUserCache($loginId);
        	if(!$user){
        		res_return('用户信息异常');
        	}
        	$variable = [
        		'cur' => $user,
        		'site_title' => $this->site_title
        	];
        	$this->assign($variable);
        	return $this->fetch('bindPhone');
        }
    }
    
    //立即反馈
    public function feedback(){
        if($this->request->isAjax()){
        	global $loginId;
        	$page = myRequest::postId('分页','page');
        	$list = iMember::getMyFeedback($loginId, $page);
        	res_return('ok',$list);
        }else{
        	
        	$this->assign('site_title',$this->site_title);
        	return $this->fetch();
        }
    }
    
    //提交反馈
    public function doFeedback(){
    	if($this->request->isAjax()){
    		$rules = [
    			'content' =>  ["max:500",['max'=>'反馈内容最多支持200个字符']],
    			'phone' =>  ["mobile",['mobile'=>'请输入正确格式的手机号']]
    		];
    		$data = myValidate::getData($rules, 'content,phone');
    		global $loginId;
    		$user = myCache::getUserCache($loginId);
    		if(!$user){
    			res_return('用户数据异常');
    		}
    		$data['content'] = htmlspecialchars($data['content']);
    		$data['channel_id'] = $user['channel_id'];
    		$data['agent_id'] = $user['agent_id'];
    		$data['uid'] = $loginId;
    		$data['create_time'] = time();
    		$re = iMember::doFeedback($data);
    		res_return($re);
    	}else{
    		
    		$this->assign('site_title',$this->site_title);
    		return $this->fetch('doFeedback');
    	}
    }
    
    //商品订单列表
    public function myOrder(){
    	global $loginId;
    	if($this->request->isAjax()){
    		$page = myRequest::postId('分页','page');
    		$list = iMember::getMyOrder($loginId,$page);
    		res_return('ok',$list);
    	}else{
    		$this->assign('site_title',$this->site_title);
    		return $this->fetch('myOrder');
    	}
    }
    
    //我的消息
    public function message(){
    	if($this->request->isAjax()){
    		$page = myRequest::postId('分页','page');
    		$list = iMember::getMessage($page);
    		res_return('ok',$list);
    	}else{
    		
    		$this->assign('site_title',$this->site_title);
    		return $this->fetch();
    	}
    }
    
    //我的收藏
    public function myCollect(){
    	if($this->request->isAjax()){
    		global $loginId;
    		$page = myRequest::postId('分页','page');
    		$list = iBook::getMyCollect($loginId, $page);
    		res_return('ok',$list);
    	}else{
    		$this->assign('site_title',$this->site_title);
    		return $this->fetch('myCollect');
    	}
    }
    
    //删除收藏
    public function delCollect(){
    	global $loginId;
    	$post = myRequest::post('book_id');
    	$book_id = is_array($post['book_id']) ? $post['book_id'] : '';
    	if(!$book_id){
    		res_return('请选择要移除的书籍');
    	}
    	$res = iBook::delCollect($book_id, $loginId);
    	if($res){
    		res_return();
    	}else{
    		res_return('删除失败');
    	}
    }
    
    //阅读历史
    public function myHistory(){
    	if($this->request->isAjax()){
    		global $loginId;
    		$page = myRequest::postId('分页','page');
    		$list = iBook::getMyReadhistory($loginId, $page);
    		res_return('ok',$list);
    	}else{
    		$this->assign('site_title',$this->site_title);
    		return $this->fetch('myHistory');
    	}
    }
    
    //删除阅读历史
    public function delHistory(){
    	global $loginId;
    	$post = myRequest::post('book_id');
    	$book_id = is_array($post['book_id']) ? $post['book_id'] : '';
    	if(!$book_id){
    		res_return('请选择要移除的书籍');
    	}
    	$res = iBook::delReadhistory($book_id, $loginId);
    	if($res){
    		res_return();
    	}else{
    		res_return('删除失败');
    	}
    }

    /*
     * 20190925
     * 视频记录
     * wuxiong
     * */
    //视频记录
    function videoHistory(){
        if($this->request->isAjax()){
            global $loginId;
            $page = myRequest::postId('分页','page');
            $list = iVideo::getMyVideohistory($loginId, $page);
            res_return('ok',$list);
        }else{
            $this->assign('site_title',$this->site_title);
            return $this->fetch('videoHistory');
        }
    }
    //删除视频收藏
    public function delvideoplayCollect(){
        global $loginId;
        $post = myRequest::post('video_id');
        $video_id = is_array($post['video_id']) ? $post['video_id'] : '';
        if(!$video_id){
            res_return('请选择要移除的视频');
        }
        $res = iVideo::delplayCollect($video_id, $loginId);
        if($res){
            res_return();
        }else{
            res_return('删除失败');
        }
    }

    //视频收藏
    public function videoCollect(){
        if($this->request->isAjax()){
            global $loginId;
            $page = myRequest::postId('分页','page');
            $list = iVideo::getMyCollect($loginId, $page);
            res_return('ok',$list);
        }else{
            $this->assign('site_title',$this->site_title);
            return $this->fetch('videoCollect');
        }
    }

    //删除视频收藏
    public function delvideoCollect(){
        global $loginId;
        $post = myRequest::post('video_id');
        $video_id = is_array($post['video_id']) ? $post['video_id'] : '';
        if(!$video_id){
            res_return('请选择要移除的视频');
        }
        $res = iVideo::delCollect($video_id, $loginId);
        if($res){
            res_return();
        }else{
            res_return('删除失败');
        }
    }

    //退出登录
    public function logOut(){
        session('INDEX_LOGIN_ID',null);
        $url = my_url('/');
        echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
        exit;
    }

    /*
     *20190926
     *用户信息
     * */
    public function userInfo(){
        global $loginId;
        $user = myCache::getUserCache($loginId);
        if(!$user){
            res_return('用户信息异常');
        }
        $variable = [
            'site_title' => $this->site_title,
            'userinfo' => $user,
        ];
        $this->assign($variable);
        return $this->fetch();
    }

    //上传头像
    public function imgupload(){
        $file = request()->file('file');
        if($file){
            $info = $file->validate(['size'=>1000000,'ext'=>'jpg,png,gif'])->move($_SERVER['DOCUMENT_ROOT'].'/uploads');
            if($info){
                $imgurl="/uploads/".$info->getSaveName();
                res_return(['url'=>$imgurl]);
            }else{
                // 上传失败获取错误信息
                res_return($file->getError());
            }
        }
    }

    //更新用户信息
    public function upUserinfo(){
        global $loginId;
        $post = myRequest::post();
        $re= Db::name('Member')->where('id','=',$loginId)->update($post);
        if($re){
            //更新缓存
            $key = 'member_info_'.$loginId;
            $member =  Db::name('Member')->where('id','=',$loginId)->find();
            if($member){
                if($member['status'] != 1){
                    res_return('用户信息异常，请联系客服');
                }
                $data = $member;
                cache($key,$data,86400);
            }
        }else{
            res_return("信息修改失败");
        }
        res_return("信息修改成功");
    }
    

}