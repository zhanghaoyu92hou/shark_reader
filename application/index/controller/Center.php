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

class Center extends Common{
	
	//构造函数
	public function __construct(){
		parent::__construct();
	}
    
    //个人中心
    public function index(){
        global $loginId;
        $url = my_url('User/index');
        if($loginId){
            echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
            exit;
        }
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

}