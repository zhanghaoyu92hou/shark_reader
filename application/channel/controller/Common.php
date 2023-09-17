<?php
namespace app\channel\controller;

use think\Controller;
use app\channel\model\cLogin;

class Common extends Controller{
    
    //初始化
    public function __construct(){
        parent::__construct();
        checkSiteOwner();
        global $loginId;
        $loginId = cLogin::getCache('id');
        if(!$loginId){
            $url = my_url('Login/index');
            if($this->request->isAjax()){
                res_return('登录已失效');
            }else{
                echo "<script language=\"javascript\">window.open('".$url."','_top');</script>";
                exit;
            }
        }
    }
    
    public function _empty(){
        res_return('页面不存在');
    }
}