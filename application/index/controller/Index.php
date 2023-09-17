<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\Common;
use think\Db;
use app\common\model\myCache;
use think\facade\Session;
class Index extends Controller{
    
    
    
     //   public function addhtml(){
     //    $book=267;	
     //    $num=48;	
     //	for($i=1;$i<=$num;$i++){
     //	$html= getBlockContent($i, 'book/'.$book);
     //	$html='<p style="font-size:24px">' .$html.'</p>';
     //	 saveBlock($html, $i,'book/'.$book);
     //	}
     //}
    //首页跳转
    public function index(){
        $web_block = myCache::getWebblockCache();
        $url = '';
        if($web_block){
            foreach ($web_block as $v){
                if($v['is_on'] == 1){
                    $url = $v['url'];
                    break;
                }
            }
        }
        if(!$url){
        	res_return('网站功能未开启');
        }
        $this->redirect($url);
    }
    public function doJson($code,$data){
    	return json(array('code'=>$code,'data'=>$data));
    }
    public function doSign(){
    	header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With');
    	$siteConfig = myCache::getWebSiteCache();
    	$config = $siteConfig['sign_config'];
		$member['id'] = Session::get('INDEX_LOGIN_ID');
		if(empty($member['id']))res_return('未登录');
    	if($member){
            $date = date('Ymd');
            $repeat = Db::name('MemberSign')->where('uid','=',$member['id'])->where('date','=',$date)->value('id');
            if($repeat){
            	$str = "签到失败";
            	$str .= "您今日已签到";
                // wx::responseText($str);
                return $this->doJson('1',$str);
            }
            $data = [
                'uid' => $member['id'],
                'date' => $date,
                'create_time' => time()
            ];
            $yesterday = date('Ymd',strtotime('yesterday'));
            $prev = Db::name('MemberSign')->where('uid','=',$member['id'])->where('date','=',$yesterday)->field('id,days')->find();
            $cur_days = 1;
            if($prev){
                $cur_days = $prev['days'] + 1;
                $cur_days = $cur_days >= 7 ? 1 : $cur_days;
            }
            $key = 'day'.$cur_days;
            if(!isset($config[$key])){
            	$str = "签到失败";
            	$str .= "签到参数配置有误,请联系客服";
            	// wx::responseText($str);
            	return $this->doJson('2',$str);
            }
            $data['days'] = $cur_days;
            $data['money'] = $config[$key];
            Db::startTrans();
            $flag = false;
            $re = Db::name('MemberSign')->insert($data);
            if($re){
                $res = Db::name('Member')->where('id','=',$member['id'])->setInc('money',$data['money']);
                if($res){
                    $flag = true;
                }
            }
            if($flag){
                Db::commit();
                $cache_key = 'member_info_'.$member['id'];
                cache($cache_key,null);
                $str = '本日签到成功，赠送'.$data['money'].'书币,您已连续签到'.$cur_days.'天,多签多送,最高赠送'.$config['day7'].'书币';
                return $this->doJson('0',$str);
            }else{
                Db::rollback();
                $str = "签到失败";
                $str .= "当前签到人数较多，请稍后再试";
                // wx::responseText($str);
                return $this->doJson('3',$str);
            }
        }else{
        	$str = "签到失败";
        	$str .= "您的账户异常，请联系客服";
        	// wx::responseText($str);
        	return $this->doJson('4',$str);
        }
    }
    
}