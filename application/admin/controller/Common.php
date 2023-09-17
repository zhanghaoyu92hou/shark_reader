<?php
namespace app\admin\controller;
use think\Controller;
use app\admin\model\mLogin;

class Common extends Controller{
    
    //初始化
    public function __construct(){
        parent::__construct();
        checkSiteOwner();
        global $loginId;
        $loginId = mLogin::getCache('id');
        if(!$loginId){
            $url = my_url('Login/index');
            if($this->request->isAjax()){
                res_return('登录已失效');
            }else{
                echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
                exit;
            }
        }
        self::checkAccess();
    }
    
    //检验权限
    private function checkAccess(){
        $flag = false;
        $access = mLogin::getAccess();
        if($access && is_array($access)){
            $controller = $this->request->controller();
            $action = $this->request->action();
            $str = strtolower($controller.':'.$action);
            if(in_array($str, $access)){
                $flag = true;
            }
        }
        if(!$flag){
        	var_dump($access);
        	die;
            res_return('您尚未取得权限，请联系管理员');
        }
    }
    
    public function _empty(){
        res_return('页面不存在');
    }
}