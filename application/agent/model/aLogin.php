<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;

class aLogin extends Common{
    
    public static $rules = [
        "old_pwd" => ["require|length:6,16",["require"=>"请输入原密码","length"=>"请输入6到16位原密码"]],
        "new_pwd" => ["require|length:6,16",["require"=>"请输入新密码","length"=>"请输入6到16位新密码"]],
        "re_pwd"  => ["require|confirm:new_pwd",["require"=>"请再次输入新密码","confirm"=>"两次输入密码不一致"]],
    	'login_name' => ["require|alphaDash|length:5,12",["require"=>"请输入登陆账户名","alphaDash"=>'登陆账户名必须是英文、数字、下划线和破折号',"length"=>"请输入5至12位符合规范的登陆账户名"]],
        'password' => ["require|length:6,16",["require"=>"请输入登陆密码","length"=>"请输入6-16位登陆密码"]],
    	'verify_code' => ['require|alphaNum|length:4',['require'=>'请输入验证码','alphaNum'=>'验证码必须是字母或者数字','length'=>'验证码为4位字母或数字']]
    ];
    
    //验证登录信息
    public static function checkLogin($login_name,$password){
        self::checkErrorTimes($login_name);
        $cur = Db::name('Channel')->where('login_name','=',$login_name)->find();
        if(empty($cur)){
            $error = '代理不存在';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        if($cur['is_wx'] != 2){
            $error = '后台地址错误';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        if($cur['status'] != 1){
            $error = '渠道未启用';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        $inp_password = createPwd($password);
        if($inp_password !== $cur['password']){
            $error = '登录密码输入错误';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        self::saveLog($login_name);
        self::saveCache($cur);
    }
    
    //获取当前代理外链
    public static function getUrlMsg($path,$param=[]){
        $url = 'http://';
        global $loginId;
        $cur = parent::getById('Channel', $loginId,'id,type,is_wx,parent_id,url,is_location,location_url');
        if($cur){
            if($cur['is_location'] == 1 && $cur['location_url']){
                $param_str = $path.'?'.http_build_query($param);
                $url .= $cur['location_url'].$param_str;
            }else{
                if($cur['url']){
                    $param_str = $path.'?'.http_build_query($param);
                    $url .= $cur['url'].$param_str;
                }else{
                    $param['agent_id'] = encodeStr($loginId);
                    $param_str = $path.'?'.http_build_query($param);
                    if($cur['parent_id']){
                        $parent = parent::getById('Channel',$cur['parent_id'],'id,type,url,is_location,location_url');
                        if($parent){
                            if($parent['type'] == 1){
                                if($parent['is_location'] == 1 && $parent['location_url']){
                                    $url .= $parent['location_url'].$param_str;
                                }else{
                                    if($parent['url']){
                                        $url .= $parent['url'].$param_str;
                                    }
                                }
                            }
                        }
                    }
                    if($url === 'http://'){
                        $website = Db::name('Config')->where('key','=','website')->value('value');
                        if($website){
                            $website = json_decode($website,true);
                            if($website['is_location'] == 1 && $website['location_url']){
                                $url .= $website['location_url'].$param_str;
                            }else{
                                if($website['url']){
                                    $url .= $website['url'].$param_str;
                                }
                            }
                        }
                    }
                }
            }
        }
        if($url === 'http://'){
            res_return('获取链接配置失败');
        }
        return $url;
    }
    
    
    //创建菜单
    public static function createMenu($is_back = false){
        $data = Db::name('Nodes')->where('type','=',3)->where('status','=',1)->field('id,pid,name,url,icon')->order('sort_num','desc')->select();
        $menu = [];
        if($data){
            foreach ($data as $val){
                $menu[] = ['id'=>$val['id'],'pid'=>$val['pid'],'name'=>$val['name'],'icon'=>$val['icon'],'url'=>$val['url']];
            }
        }
        $tree = list_to_tree($data,'id','pid','child');
        $cur_type = self::getCache('type');
        if($cur_type == 1){
            $other = [
                'name' => '代理管理',
                'url' => '',
                'icon' => 'layui-icon-app',
                'child' => [
                    ['name'=>'代理列表','url'=>'Agent/index'],
                    ['name'=>'代理结算','url'=>'Agent/withdraw'],
                ]
            ];
            $tree[] = $other;
        }
        cache('AGENT_LOGIN_MENU',$tree);
        if($is_back){
        	return $tree;
        }
    }
    
    //获取菜单
    public static function getMenu(){
    	$key = 'AGENT_LOGIN_MENU';
    	$data = cache($key);
    	if(!$data){
    		$data = self::createMenu(true);
    	}
        return $data;
    }
    
    //获取最后一次登录信息
    public static function getLastLoginMsg($login_name){
        $field = 'login_ip,login_time';
        $cur = Db::name('LoginLog')->where('login_name','=',$login_name)->where('type','=',3)->where('status','=',1)->field($field)->order('id','DESC')->find();
        return $cur;
    }
    
    //检测该账号错误登录次数
    private static function checkErrorTimes($login_name){
        $limit = 8;
        $hour = 1;
        $start_time = time() - $hour*3600;
        $list = Db::name('LoginLog')->where('login_name','=',$login_name)->where('type','=',3)->where('login_time','>',$start_time)->field('id,status')->limit($limit)->select();
        $error = true;
        if($list && count($list) >= 8){
            foreach ($list as $v){
                if($v['status'] == 1){
                    $error = false;
                    break;
                }
            }
        }else{
            $error = false;
        }
        if($error){
            res_return('您已连续登录失败'.$limit.'次，'.$hour.'小时内禁止登录');
        }
    }
    
    //保存登录日志
    private static function saveLog($login_name,$error=''){
        $status = $error ? 2 : 1;
        $data = [
            'login_name' => $login_name,
            'type' => 3,
            'status' => $status,
            'remark' => $error,
            'login_ip' => request()->ip(),
            'login_time' => time()
        ];
        Db::name('LoginLog')->insert($data);
    }
    
    //获取登录信息
    public static function getCache($field=''){
        $res = '';
        $loginId = session('AGENT_LOGIN_ID');
        if($loginId){
        	$key = 'AGENT_USER_'.$loginId;
        	$cur = cache($key);
        	if(!$cur){
        		$cur = Db::name('Channel')->where('id','=',$loginId)->where('status','=',1)->find();
        		if(!$cur){
        			session('AGENT_LOGIN_ID',null);
        			res_return('用户信息异常，请重新登录');
        		}
        	}
        	if($cur){
        		if($field){
        			if(isset($cur[$field])){
        				$res = $cur[$field];
        			}
        		}else{
        			$res = $cur;
        		}
        	}
        }
        return $res;
    }
    
    //保存登录信息
    public static function saveCache($data){
        session('AGENT_LOGIN_ID',$data['id']);
        $key = 'AGENT_USER_'.$data['id'];
        cache($key,$data,43200);
    }
    
    //清除登录信息
    public static function clearCache(){
    	$loginId = session('AGENT_LOGIN_ID');
    	if($loginId){
    		session('AGENT_LOGIN_ID',null);
    		$key = 'AGENT_USER_'.$loginId;
    		cache($key,null);
    	}
    }
}