<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;

class mLogin extends Common{
    
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
        $cur = Db::name('Manage')->where('login_name','=',$login_name)->find();
        if(empty($cur)){
            $error = '用户不存在';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        if($cur['status'] != 1){
            $error = '用户未启用';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        $inp_password = createPwd($password);
        //echo $inp_password;exit;
        if($inp_password !== $cur['password']){
            $error = '登录密码输入错误';
            self::saveLog($login_name, $error);
            res_return($error);
        }
        $ids = '';
        if($cur['role_id'] > 0){
            $role = Db::name('Role')->where('id','=',$cur['role_id'])->find();
            if(empty($role)){
                $error = '用户角色不存在';
                self::saveLog($login_name, $error);
                res_return($error);
            }
            if($role['status'] != 1){
                $error = '用户角色未启用';
                self::saveLog($login_name, $error);
                res_return($error);
            }
            if(!$role['content']){
                $error = '该角色尚未授权';
                self::saveLog($login_name, $error);
                res_return($error);
            }
            $ids = json_decode($role['content'],true);
        }
        self::saveLog($login_name);
        self::saveCache($cur);
        self::clearNode();
    }
    
    //创建节点权限
    public static function createNodes($ids){
        $list = Db::name('Nodes')->where('type','=',1)->where('status','=',1)->field('id,pid,name,icon,is_menu,url,all_nodes')->order('sort_num','desc')->select();
        $data = [];
        if($ids){
            foreach ($list as $v){
                if(in_array($v['id'], $ids)){
                    $data[] = $v;
                }
            }
        }else{
            $data = $list;
        }
        $menu = [];
        $access = ['index:index','index:console','index:getuserchartdata','index:userinfo','index:password','index:logout'];
        foreach ($data as $val){
            if($val['all_nodes']){
                $access = array_merge($access,explode(',', $val['all_nodes']));
            }
            if($val['is_menu'] == 1){
                $menu[] = ['id'=>$val['id'],'pid'=>$val['pid'],'name'=>$val['name'],'icon'=>$val['icon'],'url'=>$val['url']];
            }
        }
        $access = array_unique($access);
        self::createMenu($menu);
        self::createAccess($access);
    }
    
    //创建菜单
    private static function createMenu($data){
    	$role_id = self::getRoleId();
    	$key = 'ADMIN_LOGIN_MENU_'.$role_id;
        $data = list_to_tree($data,'id','pid','child');
        cache($key,$data);
    }
    
    //创建权限
    private static function createAccess($data){
    	$role_id = self::getRoleId();
    	$key = 'ADMIN_LOGIN_ACCESS_'.$role_id;
    	cache($key,$data);
    }
    
    //清除权限
    public static function clearNode(){
    	$role_id = self::getRoleId();
    	$key = 'ADMIN_LOGIN_MENU_'.$role_id;
    	cache($key,null);
    	$key = 'ADMIN_LOGIN_ACCESS_'.$role_id;
    	cache($key,null);
    }
    
    //获取菜单
    public static function getMenu(){
    	$role_id = self::getRoleId();
    	$key = 'ADMIN_LOGIN_MENU_'.$role_id;
    	$data = cache($key);
    	if(!$data){
    		$ids = '';
    		if($role_id > 0){
    			$role = Db::name('Role')->where('id','=',$role_id)->find();
    			if(empty($role)){
    				$error = '用户角色不存在';
    				res_return($error);
    			}
    			if($role['status'] != 1){
    				$error = '用户角色未启用';
    				res_return($error);
    			}
    			if(!$role['content']){
    				$error = '该角色尚未授权';
    				res_return($error);
    			}
    			$ids = json_decode($role['content'],true);
    		}
    		self::createNodes($ids);
    		$data = cache($key);
    	}
        return $data;
    }
    
    //获取权限
    public static function getAccess(){
    	$role_id = self::getRoleId();
    	$key = 'ADMIN_LOGIN_ACCESS_'.$role_id;
    	$data = cache($key);
    	if(!$data){
    		$ids = '';
    		if($role_id > 0){
    			$role = Db::name('Role')->where('id','=',$role_id)->find();
    			if(empty($role)){
    				$error = '用户角色不存在';
    				res_return($error);
    			}
    			if($role['status'] != 1){
    				$error = '用户角色未启用';
    				res_return($error);
    			}
    			if(!$role['content']){
    				$error = '该角色尚未授权';
    				res_return($error);
    			}
    			$ids = json_decode($role['content'],true);
    		}
    		self::createNodes($ids);
    		$data = cache($key);
    	}
    	return $data;
    }
    
    //获取角色ID
    private static function getRoleId(){
    	$role_id = self::getCache('role_id');
    	$role_id = $role_id > 0 ? $role_id : 0;
    	return $role_id;
    }
    
    //获取登录信息
    public static function getCache($field=''){
        $res = '';
        $loginId = session('ADMIN_LOGIN_ID');
        if($loginId){
        	$key = 'ADMIN_USER_'.$loginId;
        	$cur = cache($key);
        	if(!$cur){
        		$cur = Db::name('Manage')->where('id','=',$loginId)->where('status','=',1)->find();
        		if(!$cur){
        			self::clearCache();
        			res_return('用户信息异常，请重新登录');
        		}
        		cache($key,$cur,43200);
        	}
        	if($field){
        		if(array_key_exists($field, $cur)){
        			$res = $cur[$field];
        		}
        	}else{
        		$res = $cur;
        	}
        }
        return $res;
    }
    
    //保存登录信息
    public static function saveCache($data){
    	$id = $data['id'];
    	session('ADMIN_LOGIN_ID',$id);
    	cache('ADMIN_USER_'.$id,$data,43200);
    }
    
    //清除登录信息
    public static function clearCache(){
    	$loginId = session('ADMIN_LOGIN_ID');
    	if($loginId){
    		session('ADMIN_LOGIN_ID',null);
    		cache('ADMIN_USER_'.$loginId,null);
    	}
    }
    
    //获取最后一次登录信息
    public static function getLastLoginMsg($login_name){
        $field = 'login_ip,login_time';
        $cur = Db::name('LoginLog')->where('login_name','=',$login_name)->where('type','=',1)->where('status','=',1)->field($field)->order('id','DESC')->find();
        return $cur;
    }
    
    //检测该账号错误登录次数
    private static function checkErrorTimes($login_name){
        $limit = 8;
        $hour = 1;
        $start_time = time() - $hour*3600;
        $list = Db::name('LoginLog')->where('login_name','=',$login_name)->where('type','=',1)->where('login_time','>',$start_time)->field('id,status')->limit($limit)->select();
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
            'type' => 1,
            'status' => $status,
            'remark' => $error,
            'login_ip' => request()->ip(),
            'login_time' => time()
        ];
        Db::name('LoginLog')->insert($data);
    }
}