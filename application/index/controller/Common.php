<?php
namespace app\index\controller;

use think\Controller;
use app\common\model\myRequest;
use other\myHttp;
use app\index\model\iMember;
use app\common\model\myCache;
use app\index\model\iClient;

class Common extends Controller{
    
    //当前访问环境，1微信，2移动端，3pc
    protected $device_type;
    //站点名称
    protected $site_title;
    
    //初始化程序
    public function __construct(){
        parent::__construct();
        checkSiteOwner();
        $this->device_type = iClient::getDeviceType();
        $server_url = $_SERVER['HTTP_HOST'];
        $urlData = myCache::getUrlCache($server_url);
        
        $get = myRequest::get();
        if(isset($get['location_key']) && $get['location_key']){
        	$config = cache($get['location_key']);
        	if(!$config){
        		res_return('访问该链接已超时');
        	}
        }else{
        	$config = self::createLocationCache($urlData,false);
        }
        
        //微信环境下如果没有用户信息
        if($this->device_type == 1 && ($config['appid'] || $config['appsecret'])){
        	if(!session('?INDEX_LOGIN_ID')){
        		if($server_url == $urlData['url']){
        			self::autoLogin($urlData);
        		}else{
        			$get = myRequest::get();
        			if(isset($get['location_user_id']) && $get['location_user_id']){
        				self::doUrlSign(2);
        				session('INDEX_LOGIN_ID',$get['location_user_id']);
        			}else{
        				$get['location_key'] = self::createLocationCache($urlData);
        				$url = self::getLocationUrl($urlData['url'],$get);
        				$this->redirect($url);
        			}
        		}
        	}else{
        		if($urlData['is_location'] == 1 && $server_url === $urlData['url']){
        			$param = self::doUrlSign(1,session('INDEX_LOGIN_ID'));
        			iMember::clearLogin();
        			$location_url = self::getLocationUrl($urlData['location_url'],$param);
        			$this->redirect($location_url);
        		}
        	}
            $get = myRequest::get('spread,share_user');
            if($urlData['is_wx'] === 1){
            	if($get['share_user'] && is_numeric($get['share_user']) && $get['share_user'] > 0){
            		$cur_user_id = session('INDEX_LOGIN_ID');
            		if($cur_user_id && $cur_user_id != $get['share_user']){
            			$member = myCache::getUserCache($get['share_user']);
            			if($member){
            				$webCache = myCache::getWebSiteCache();
            				iMember::addMoney($member, $webCache['share_money']);
            			}
            		}
            	}
            }
            if($get['spread'] && is_numeric($get['spread']) && $get['spread'] > 0){
            	session('CUR_SPREAD_ID',$get['spread']);
            }
        }
        global $loginId;
        $loginId = session('INDEX_LOGIN_ID');
        $this->site_title = $urlData['name'];
    }
    
    //跳转时缓存关键信息
    private function createLocationCache($urlData,$is_cache=true){
    	$get = myRequest::get('agent_id');
    	if($get['agent_id']){
    		$agent_id = decodeStr($get['agent_id']);
    		$channel = myCache::getChannelCache($agent_id);
    		if($channel['type'] == 1){
    			//一级代理
    			if($urlData['channel_id'] && $urlData['channel_id'] != $channel['id']){
    				res_return('非法访问');
    			}
    			$urlData['channel_id'] = $channel['id'];
    		}else{
    			//二级代理
    			if($urlData['agent_id'] && $urlData['agent_id'] != $channel['id']){
    				res_return('非法访问');
    			}
    			if(!$channel['parent_id']){
    				res_return('非法访问');
    			}
    			if($urlData['channel_id'] && $urlData['channel_id'] != $channel['parent_id']){
    				res_return('非法访问');
    			}
    			$urlData['channel_id'] = $channel['parent_id'];
    			$urlData['agent_id'] = $channel['id'];
    		}
    	}
    	if($is_cache){
    		$key = md5($_SERVER['HTTP_HOST'].'_location_cache');
    		cache($key,$urlData,30);
    		return $key;
    	}else{
    		return $urlData;
    	}
    }
    
    //加密&解密url参数
    private function doUrlSign($flag=2,$user_id=''){
        $value = myRequest::get();
        $key = md5('kaichiweixin');
        if($flag == 1){
            unset($value['code']);
            unset($value['state']);
            $param = array();
            foreach ($value as $k=>$v){
                if(strlen($v) > 0){
                    $param[$k] = $v;
                }
            }
            $param['location_user_id'] = $user_id;
            $param['sign_key'] = $key;
            ksort($param);
            $str = http_build_query($param);
            unset($param['sign_key']);
            $sign = sha1($str);
            $param['sign'] = $sign;
            return http_build_query($param);
        }else{
            if(!isset($value['sign'])){
                res_return('违规操作');
            }
            $get_sign = $value['sign'];
            unset($value['sign']);
            $param = array();
            foreach ($value as $k=>$v){
                if(strlen($v) > 0){
                    $param[$k] = $v;
                }
            }
            $param['sign_key'] = $key;
            ksort($param);
            $str = http_build_query($param);
            $sign = sha1($str);
            if($get_sign !== $sign){
                res_return('签名认证失败');
            }
        }
    }
    
    //拼装跳转url
    private function getLocationUrl($url,$param=null){
        $module = $this->request->module();
        $controller = $this->request->controller();
        $action = $this->request->action();
        $link = 'http://'.$url.'/'.$module.'/'.$controller.'/'.$action.'.html';
        if($param){
            if(is_array($param)){
                $link .= '?'.http_build_query($param);
            }else{
                $link .= '?'.$param;
            }
        }
        return $link;
    }
    
    // 尝试自动登录
    private function autoLogin($urlData){
        $get = myRequest::get();
        if(isset($get['location_key']) && $get['location_key']){
        	$config = cache($get['location_key']);
        	if(!$config){
        		res_return('访问该链接已超时');
        	}
        }else{
        	$config = self::createLocationCache($urlData,false);
        }
        if(isset($get['code']) && isset($get['state']) && $get['state'] === 'getnow'){
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=".$config['appid']."&secret=".$config['appsecret']."&code=".$get['code']."&grant_type=authorization_code";
            $res = myHttp::getData($url);
            if($res && isset($res['openid'])){
                if(isset($res['openid']) && $res['openid']){
                	$member = myCache::getUserByOpenId($res['openid']);
                    if($member){
                        $cur_member_id = $member['id'];
                    }else{
                    	$spread_id = 0;
                    	if(isset($get['spread']) && is_numeric($get['spread']) && $get['spread'] > 0){
                    		$spread_id = $get['spread'];
                    	}
                        $cur_member_id = iMember::createVisitor($res['openid'],$config,$spread_id);
                    }
                    $param = self::doUrlSign(1,$cur_member_id);
                    if($config['is_location'] == 1){
                    	$location_url = self::getLocationUrl($config['location_url'],$param);
                    	$this->redirect($location_url);
                    }else{
                    	session('INDEX_LOGIN_ID',$cur_member_id);
                    }
                }else{
                    $wxerror = isset($res['errmsg']) ? $res['errmsg'] : '';
                    $error = '授权失败';
                    if($wxerror){
                        $error .= ':'.$wxerror;
                    }
                    res_return($error);
                }
            }else{
                res_return('获取openid失败');
            }
        }else{
            $redirect_url = self::getLocationUrl($config['url'],$get);
            $redirect_url = urlencode($redirect_url);
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=".$config['appid']."&redirect_uri=".$redirect_url."&response_type=code&scope=snsapi_base&state=getnow#wechat_redirect";
            $this->redirect($url);
        }
    }
    
    //检查该板块是否有权限
    protected function checkBlock($block,$title=''){
    	$web_block = myCache::getWebblockCache();
    	$view = false;
    	if($web_block){
    		foreach ($web_block as $v){
    			if($v['key'] === $block && $v['is_on'] == 1){
    				$view = true;
    			}
    		}
    	}
    	if(!$view){
    		$error = $title.'板块已关闭';
    		res_return($error);
    	}
    }
    
    //登陆跳转
    protected function checkLogin(){
    	global $loginId;
    	if($this->request->isAjax()){
    		if(!$loginId){
    			res_return('您尚未登陆');
    		}
    	}else{
    		if(!$loginId){
    			$this->redirect('Login/index');
    			exit;
    		}
    		$member = myCache::getUserCache($loginId);
    		if(!$member){
    			iMember::clearLogin();
    			if($this->device_type != 1){
    				$this->redirect('Login/index');
    				exit;
    			}else{
    				$this->redirect('Index/index');
    			}
    		}
    	}
    }
    
    public function _empty(){
        
        res_return('页面不存在');
    }
    
}