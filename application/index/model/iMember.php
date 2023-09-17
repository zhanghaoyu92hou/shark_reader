<?php
namespace app\index\model;
use app\index\model\Common;
use think\Db;
use app\common\model\myCache;
use weixin\wx;

class iMember extends Common{
	
    //处理用户签到
    public static function doSign($openid,$config){
        $member = myCache::getUserByOpenId($openid);
        if($member){
            $date = date('Ymd');
            $repeat = Db::name('MemberSign')->where('uid','=',$member['id'])->where('date','=',$date)->value('id');
            if($repeat){
            	$str = "签到失败";
            	$str .= "\n\n";
            	$str .= "您今日已签到";
                wx::responseText($str);
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
            	$str .= "\n\n";
            	$str .= "签到参数配置有误,请联系客服";
            	wx::responseText($str);
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
                return $str;
            }else{
                Db::rollback();
                $str = "签到失败";
                $str .= "\n\n";
                $str .= "当前签到人数较多，请稍后再试";
                wx::responseText($str);
            }
        }else{
        	$str = "签到失败";
        	$str .= "\n\n";
        	$str .= "您的账户异常，请联系客服";
        	wx::responseText($str);
        }
    }
    
    //修改用户推广
    public static function changeMemberSpread($member,$spread){
    	if(isset($spread['channel_id']) && ($member['channel_id'] == $spread['channel_id'] || $member['agent_id'] == $spread['channel_id'])){
    		Db::startTrans();
    		$flag = false;
    		$re = Db::name('Member')->where('id','=',$member['id'])->setField('spread_id',$spread['id']);
    		if($re){
    			$data = ['visitor_num'=>Db::raw('visitor_num+1')];
    			if($member['subscribe'] == 1){
    				$data['member_num'] = Db::raw('member_num+1');
    			}
    			$res = Db::name('Spread')->where('id','=',$spread['id'])->update($data);
    			if($res){
    				$flag = true;
    			}
    		}
    		if($flag){
    			$member['spread_id'] = $spread['id'];
    			cache('member_info_'.$member['id'],$member,86400);
    			Db::commit();
    		}else{
    			Db::rollback();
    		}
    	}
    }
    
    /**
     * 创建游客
     * @param string $openid 微信openid
     * @param array $get get参数
     */
    public static function createVisitor($openid,$config,$spread_id=0){
        $data = array(
        	'channel_id' => $config['channel_id'],
        	'agent_id' => $config['agent_id'],
        	'wx_id' => $config['wx_id'],
            'headimgurl' => '/static/templet/default/headimg.jpeg',
        	'spread_id' => $spread_id,
            'nickname' => '游客',
            'sex' => 0,
            'openid' => $openid,
            'create_time' => time()
        );
        $re = Db::name('Member')->insertGetId($data);
        if($re){
        	if($data['spread_id']){
                Db::name('Spread')->where('id','=',$data['spread_id'])->setInc('visitor_num');
            }
            return $re;
        }else{
            res_return('用户创建失败');
        }
    }
    
    /**
     * 用户关注更新用户信息
     * @param array $info 微信用户公开信息
     */
    public static function subscribeMember($info){
        $member = Db::name('Member')->where('openid','=',$info['openid'])->field('id,subscribe_time,spread_id')->find();
        $data = [
            'openid' => $info['openid'],
        	'nickname' => $info['nickname'] ? self::removeEmoji($info['nickname']) : '',
            'sex' => $info['sex'],
            'city' => $info['city'],
            'province' => $info['province'],
            'country' => $info['country'],
            'headimgurl' => $info['headimgurl'],
            'subscribe' => 1
        ];
        $is_first = 2;
        if($member){
        	if(!$member['subscribe_time']){
        		$is_first = 1;
        		$data['subscribe_time'] = time();
        	}
        	$re = parent::save('Member', [['id','=',$member['id']]], $data);
        	if($re){
        		cache('member_info_'.$member['id'],null);
        	}
        	if($member['spread_id'] > 0){
        		Db::name('Spread')->where('id','=',$member['spread_id'])->setInc('member_num');
        	}
        }else{
        	$time = time();
        	$data['wx_id'] = wx::$config['wx_id'];
            $data['channel_id'] = wx::$config['channel_id'];
            $data['agent_id'] = wx::$config['agent_id'];
            $data['subscribe_time'] = $time;
            $data['create_time'] = $time;
            $re = parent::add('Member', $data);
            if($re){
                $is_first = 1;
            }
        }
        return $is_first;
    }
    
    //通过扫描二维码登陆
    public static function subscribeByQrcode($info,$code){
    	$flag = '';
    	$member = Db::name('Member')->where('openid','=',$info['openid'])->field('id,subscribe_time')->find();
    	$data = [
    			'openid' => $info['openid'],
    			'nickname' => $info['nickname'] ? self::removeEmoji($info['nickname']) : '',
    			'sex' => $info['sex'],
    			'city' => $info['city'],
    			'province' => $info['province'],
    			'country' => $info['country'],
    			'headimgurl' => $info['headimgurl'],
    			'subscribe' => 1
    	];
    	if(!$member){
    		$time = time();
    		$data['channel_id'] = wx::$config['channel_id'];
    		$data['agent_id'] = wx::$config['agent_id'];
    		$data['wx_id'] = wx::$config['wx_id'];
    		$data['subscribe_time'] = $time;
    		$data['create_time'] = $time;
    		$member_id = Db::name('Member')->insertGetId($data);
    	}else{
    		$member_id = $member['id'];
    		if(!$member['subscribe_time']){
    			Db::name('Member')->where('id','=',$member_id)->setField('subscribe_time',time());
    		}
    	}
    	if($member_id){
    		if($code){
    			$key = cache($code);
    			if($key === 'wait'){
    				cache($code,$member_id,1800);
    				$flag = true;
    			}else{
    				$flag = '二维码已过期';
    			}
    		}else{
    			$flag = '扫码登陆失败';
    		}
    	}else{
    		$flag = '注册失败';
    	}
    	return $flag;
    }
    
    //过滤特殊字符
    private static function removeEmoji($clean_text) {
    	
    	// Match Emoticons
    	$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
    	$clean_text = preg_replace($regexEmoticons, '', $clean_text);
    	
    	// Match Miscellaneous Symbols and Pictographs
    	$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
    	$clean_text = preg_replace($regexSymbols, '', $clean_text);
    	
    	// Match Transport And Map Symbols
    	$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
    	$clean_text = preg_replace($regexTransport, '', $clean_text);
    	
    	// Match Miscellaneous Symbols
    	$regexMisc = '/[\x{2600}-\x{26FF}]/u';
    	$clean_text = preg_replace($regexMisc, '', $clean_text);
    	
    	// Match Dingbats
    	$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
    	$clean_text = preg_replace($regexDingbats, '', $clean_text);
    	
    	return $clean_text;
    }
    
    /**
     * 手机号登陆
     * @param unknown $phone
     */
    public static function phoneLogin($phone){
    	$member = Db::name('Member')->where('phone','=',$phone)->field('id,status')->find();
    	if($member){
    		if($member['status'] != 1){
    			res_return('账号异常，请联系客服');
    		}
    		self::saveLogin($member['id']);
    	}else{
    		res_return(['flag'=>1]);
    	}
    	self::saveLogin($member['id']);
    	res_return(['flag'=>0]);
    }
    
    /**
     * 获取登陆ID
     * @return mixed|void|boolean
     */
    public static function getLoginId(){
    	$loginId = session('INDEX_LOGIN_ID');
    	return $loginId;
    }
    
    /**
     * 保存登陆状态
     * @param unknown $loginId
     */
    public static function saveLogin($loginId){
    	if($loginId){
    		session('INDEX_LOGIN_ID',$loginId);
    	}
    }
    
    public static function clearLogin(){
    	session('INDEX_LOGIN_ID',null);
    }
    
    /**
     * 取消关注
     * @param string $openid
     * @return number
     */
    public static function unsubscribeMember($openid){
    	$re = true;
    	$member_id = Db::name('Member')->where('openid','=',$openid)->value('id');
    	if($member_id){
    		$re = Db::name('Member')->where('id','=',$member_id)->setField('subscribe',0);
    		if($re){
    			cache('member_info_'.$member_id,null);
    		}
    	}
        return $re;
    }
    
    //签到情况
    public static function checkSign($signConfig,$uid){
    	$temp = [];
    	$total = 0;
    	foreach ($signConfig as $k=>$v){
    		$total += $v;
    		$day = ltrim($k,'day');
    		$temp[$k] = ['money'=>$v,'day'=>$day,'is_sign'=>'no'];
    	}
    	$cur_sign = 'no';
    	$cur_date = date('Ymd');
    	$cur = Db::name('MemberSign')->where('uid','=',$uid)->where('date','=',$cur_date)->field('id,days')->find();
    	if($cur){
    		$cur_sign = 'yes';
    		for($i=0;$i<$cur['days'];$i++){
    			$key = 'day'.($i+1);
    			$temp[$key]['is_sign'] = 'yes';
    		}
    	}else{
    		$yesterday = date('Ymd',strtotime('yesterday'));
    		$yesterday_msg = Db::name('MemberSign')->where('uid','=',$uid)->where('date','=',$yesterday)->field('id,days')->find();
    		if($yesterday_msg){
    			for($i=0;$i<$yesterday_msg['days'];$i++){
    				$key = 'day'.($i+1);
    				$temp[$key]['is_sign'] = 'yes';
    			}
    		}
    	}
    	$res = [
    		'cur_sign' => $cur_sign,
    		'total' => $total,
    		'list' => $temp
    	];
    	return $res;
    }
    
    //获取我的购物订单
    public static function getMyOrder($uid,$page){
    	$list = Db::name('SaleOrder a')
    	->join('product b','a.pid=b.id and b.status=1')
    	->where('a.uid','=',$uid)
    	->field('a.id,a.status,a.money,a.count,a.pid,b.name,b.summary,b.cover')
    	->order('a.id','desc')
    	->page($page,10)
    	->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['url'] = my_url('Product/info',['pid'=>$v['pid']]);
    			$v['status_name'] = '未知';
    			switch ($v['status']){
    				case 1:
    					$v['status_name'] = '未支付';
    					break;
    				case 2:
    					$v['status_name'] = '待发货';
    					break;
    				case 3:
    					$v['status_name'] = '待收货';
    					break;
    				case 4:
    					$v['status_name'] = '交易成功';
    					break;
    			}
    		}
    	}else{
    		$list = 0;
    	}
    	return $list;
    }
    
    //获取我的反馈
    public static function getMyFeedback($uid,$page){
    	$list = Db::name('Feedback a')
    	->join('member b','a.uid=b.id')
    	->where('a.uid','=',$uid)
    	->field('a.*,b.nickname,b.headimgurl')
    	->page($page,10)
    	->order('id','desc')
    	->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['create_time'] = date('Y-m-d',$v['create_time']);
    		}
    	}else{
    		$list = '';
    	}
    	return $list;
    }
    
    //绑定手机号
    public static function bindPhone($uid,$phone){
    	$re = Db::name('Member')->where('id','=',$uid)->setField('phone',$phone);
    	$res = false;
    	if($re !== false){
    		$res = true;
    		$data = myCache::getUserCache($uid);
    		if($data){
    			$data['phone'] = $phone;
    			cache('member_info_'.$uid,$data,86400);
    		}
    	}
    	$res = $re !== false ? 'ok' : '绑定失败';
    	return $res;
    }
    
    //提交反馈
    public static function doFeedback($data){
    	$re = Db::name('Feedback')->insert($data);
    	$str = $re ? 'ok' : '反馈失败';
    	return $str;
    }
    
    //创建登陆code
    public static function createLoginCode(){
    	$code = md5(microtime().mt_rand(100000,999999));
    	$repeat = Db::name('QrcodeLogin')->where('code','=',$code)->value('id');
    	if($repeat){
    		self::createLoginCode();
    	}else{
    		$re = Db::name('QrcodeLogin')->insert(['code'=>$code]);
    		if($re){
    			return $code;
    		}else{
    			self::createLoginCode();
    		}
    	}
    }
    
    //设置自动扣费
    public static function setAuto($uid,$is_auto){
    	$re = Db::name('Member')->where('id','=',$uid)->setField('is_auto',$is_auto);
    	$res = false;
    	if($re !== false){
    		$res = true;
    		$data = myCache::getUserCache($uid);
    		if($data){
    			$data['is_auto'] = $is_auto;
    			cache('member_info_'.$uid,$data,86400);
    		}
    	}
    	$res = $re !== false ? true : false;
    	return $res;
    }
    
    //分享增加奖励
    public static function addMoney($member,$money){
    	if($money > 0){
    		$date = date('Ymd');
    		$repeat = Db::name('Share')->where('uid','=',$member['id'])->where('create_date','=',$date)->value('id');
    		if(!$repeat){
    			Db::startTrans();
    			$flag = false;
    			$re = Db::name('Member')->where('id','=',$member['id'])->setInc('money',$money);
    			if($re){
    				$data = [
    					'uid' => $member['id'],
    					'money' => $money,
    					'create_date' => $date
    				];
    				$res = Db::name('Share')->insert($data);
    				if($res){
    					$flag = true;
    				}
    			}
    			if($flag){
    				$member['money'] += $money;
    				cache('member_info_'.$member['id'],$member,86400);
    				Db::commit();
    			}else{
    				Db::rollback();
    			}
    		}
    	}
    }
    
    //获取我的消息
    public static function getMessage($page){
    	$list = Db::name('Message')
    	->where('type','=',2)
    	->where('status','=',1)
    	->page($page,10)
    	->order('id','desc')
    	->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['content'] = getBlockContent($v['id'],'message');
    			$v['create_time'] = date('Y-m-d H:i',$v['create_time']);
    		}
    	}else{
    		$list = 0;
    	}
    	return $list;
    }
}