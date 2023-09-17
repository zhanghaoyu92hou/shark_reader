<?php

use think\facade\Request;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

/**
 * 打印数据
 * @param mixed $data 需要打印的数据
 * @param string $isdump 是否打印数据类型
 */
function my_print($data,$isdump=false){
    echo '<meta charset="utf-8"><pre>';
    if(!$isdump){
        print_r($data);
    }else{
        var_dump($data);
    }
    exit;
}

/**
 * 构建url
 * @param string $url 模块名
 * @param array $param 参数
 * @param array $is_http 是否加入域名
 */
function my_url($url,$param=[],$is_http=false){
    $link = '';
    if($is_http){
        //$link .= 'http://'.HOST_NAME;
    }
    $link .= url($url);
    if($param){
        $str = is_array($param) ? http_build_query($param) : $param;
        $link .= '?'.$str;
    }
    return $link;
}

/**
 * 构建单选html
 * @param array $config 单选配置
 * @param string $val 默认选中值
 * @return string 单选表单
 */
function createRadioHtml($config,$val=''){
    $str = '';
    foreach ($config['option'] as $v){
        $checked = '';
        if(strlen($val) > 0){
            $checked = ($val == $v['val']) ? 'checked="checked"' : '';
        }else{
            $checked = $v['default'] ? 'checked="checked"' : '';
        }
        $str .= '<input type="radio" lay-filter="'.$config['name'].'" name="'.$config['name'].'" value="'.$v['val'].'" title="'.$v['text'].'" '.$checked.' />';
    }
    return $str;
}

/**
 * 构建下拉框选项列表
 * @param array $list 选项值
 * @param string $val 选中值
 * @param string $default_str 没有选中值时选项
 * @return string
 */
function createSelectHtml($list,$val='',$default_str=''){
    $str = '';
    if($default_str){
        $str = '<option value="">'.$default_str.'</option>';
    }
    foreach ($list as $v){
        $selected = '';
        if($val != '' && $val == $v['id']){
            $selected = 'selected="selected"';
        }
        $str .= '<option value="'.$v['id'].'" '.$selected.'>'.$v['name'].'</option>';
    }
    return $str;
}

/**
 * 读取级联菜单
 * @param array $list
 * @param string $pk
 * @param string $pid
 * @param string $child
 * @param number $startPid
 * @return unknown
 */
function list_to_tree($list, $pk='id', $pid = 'pid', $child = '_child', $startPid = 0) {
    $tree = array();
    if(is_array($list)) {
        $refer = array();
        foreach ($list as $key => $data) {
            $refer[$data[$pk]] =& $list[$key];
        }
        foreach ($list as $key => $data) {
            $parentId =  $data[$pid];
            if ($startPid == $parentId) {
                $tree[] =& $list[$key];
            }else{
                if(isset($refer[$parentId])) {
                    $parent =& $refer[$parentId];
                    $parent[$child][] =& $list[$key];
                }
            }
        }
    }
    $tree = getIslast($tree);
    return $tree;
}

/**
 * 判断级联菜单在同一级中是否为最后一个
 * @param unknown $list
 * @param number $level
 * @return unknown
 */
function getIslast($list,$level=0){
    foreach($list as $k=>&$v){
        $v['level'] = $level+1;
        $count = count($list)-1;
        if($k==$count){
            $v['islast'] = "Y";
        }else{
            $v['islast'] = "N";
        }
        if(isset($v['_child'])){
            getIslast($v['_child'],$v['level']);
        }
    }
    return $list;
}

/**
 * 构建后台主菜单
 * @param array $menu 菜单数组
 * @param string $target 指向iframe
 */
function createMenu($menu){
    $str = '';
    foreach ($menu as $v){
        $line_str = '';
        $line_str .= 'href="javascript:;" ';
        if(!array_key_exists('child', $v)){
            $line_str .= 'lay-tips="'.$v['name'].'" lay-direction="2" ';
            if($v['url']){
                if(stripos($v['url'],'http')){
                    $line_str .= 'lay-href="'.$v['url'].'"';
                }else{
                    $line_str .= 'lay-href="'.my_url($v['url']).'"';
                }
            }
        }
        $str .= '<li class="layui-nav-item">';
        $str .= '<a '.$line_str.' >';
        $str .= '<i class="layui-icon '.$v['icon'].'"></i>';
        $str .= '<cite>'.$v['name'].'</cite>';
        $str .= '</a>';
        if(array_key_exists('child', $v)){
            $str .= createChildMenu($v['child']);
        }
        $str .= '</li>';
    }
    return $str;
}

/**
 * 构建后台子菜单
 * @param array $menu
 * @param string $target 指向iframe
 * @return string
 */
function createChildMenu($menu){
    $str = '<dl class="layui-nav-child">';
    foreach ($menu as $v){
        if(array_key_exists('child', $v)){
            $str .= '<dd class="layui-nav-item">';
            $str .= '<a href="javascript:;">'.$v['name'].'</a>';
            $str .= createChildMenu($v['child']);
        }else{
            $line_str = '';
            $line_str .= 'href="javascript:;" ';
            if(!array_key_exists('child', $v)){
                $line_str .= 'lay-tips="'.$v['name'].'" lay-direction="2" ';
                if($v['url']){
                    if(stripos($v['url'], 'http')){
                        $line_str .= 'lay-href="'.$v['url'].'"';
                    }else{
                        $line_str .= 'lay-href="'.my_url($v['url']).'"';
                    }
                }
            }
            $str .= '<dd>';
            $str .= '<a '.$line_str.'>'.$v['name'].'</a>';
        }
        $str .= '</dd>';
    }
    $str .= '</dl>';
    return $str;
}

//创建登录密码
function createPwd($str){
    $key = 'kaichiwangluo';
    $str .= $key;
    $res = md5(sha1($str));
    return $res;
}


//写入静态缓存文件
function saveBlock($html,$fileKey,$dirname='book'){
    $res = false;
    $path = env('root_path');
    $path .= 'static/block/'.$dirname.'/';
    if(!is_dir($path)){
        mkdir($path,0777,true);
    }
    if(is_dir($path) && is_writable($path)){
        $path .= $fileKey.'.html';
        $obj = fopen($path,'w');
        fwrite($obj, $html);
        fclose($obj);
        if(is_file($path)){
            $res = true;
        }
    }
    return $res;
}

//获取静态缓存内容
function getBlockContent($filekey,$dirname='book'){
    $path = env('root_path');
    $path .= 'static/block/'.$dirname.'/'.$filekey.'.html';
    $content = '';
	
    if(@is_file($path)){
        $content = file_get_contents($path);
    }
    return $content;
}

//读取缓存静态文件
function getBlock($filekey,$dirname='book'){
    $filename = env('root_path');
    $filename .= 'static/block/'.$dirname.'/'.$filekey.'.html';
 
    if(@is_file($filename)){
        require_once $filename;
    }
}
//读取缓存静态返回文字
function getBlockText($filekey,$dirname='book'){
    $filename = env('root_path');
    $filename .= 'static/block/'.$dirname.'/'.$filekey.'.html';
 
    if(@is_file($filename)){
      return  file_get_contents($filename);
    }
}
//构建复选框
function createCheckBoxHtml($list,$name,$cur){
    $str = '';
    if($list && is_array($list)){
        $check = [];
        if($cur){
            if(!is_array($cur)){
                $check = explode(',', trim($cur,','));
            }else{
                $check = $cur;
            }
        }
        
        foreach ($list as $v){
            $checked = '';
            if(is_array($v)){
                if(in_array($v['id'], $check)){
                    $checked = 'checked';
                }
                $str .= '<input type="checkbox" name="'.$name.'[]" value="'.$v['id'].'" lay-skin="primary" title="'.$v['name'].'" '.$checked.' >';
            }else{
                if(in_array($v,$check)){
                    $checked = 'checked';
                }
                $str .= '<input type="checkbox" name="'.$name.'[]" value="'.$v.'" lay-skin="primary" title="'.$v.'" '.$checked.' >';
            }
        }
    }
    return $str;
}

//创建支付方式下拉框
function createPayType($cur){
    $list = [
        ['id'=>1,'name'=>'官方微信支付'],
        ['id'=>2,'name'=>'个人微信支付'],
        ['id'=>3,'name'=>'支付宝支付']
    ];
    $html = createSelectHtml($list,$cur);
    return $html;
}

//通用返回信息
function res_return($msg='ok',$data=null,$count=0){
    $code = 1;
    if(is_array($msg)){
        $data = $msg;
        $msg = 'ok';
    }else{
        if($msg !== 'ok'){
            $code = 0;
            if(!Request::isAjax() && !Request::isPost()){
                showError($msg);
            }
        }
    }
    $res = ['code' => $code,'msg' => $msg,'data' => $data,'count' => $count];
    echo json_encode($res,JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 弹出错误信息
 * @param string $error
 */
function showError($error){
    header("Content-type:text/html;charset=utf-8");
    $str = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
    $str .= '<html>';
    $str .= '<head>';
    $str .= '<title>出错了！！！</title>';
    $str .= '<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">';
    $str .= '<style type="text/css">';
    $str .= 'html,body,div,span,a,img,header{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline;}';
    $str .= 'header{display: block;}';
    $str .= 'a{text-decoration:none;}';
    $str .= 'img{max-width:100%;}';
    $str .= 'body{background: url(/static/common/images/errorbg.png);font-family: "Century Gothic",Arial, Helvetica, sans-serif;font-weight:100%;}';
    $str .= '.header{height:80px;}';
    $str .= '.content p{margin: 15px 0px 22px 0px;font-family: "Century Gothic";font-size: 1.5em;color:red;text-align:center;}';
    $str .= '.content  span,.logo h1 a{color:#e54040;}';
    $str .= '.content span{font-size: 1.5em;}';
    $str .= '.content{text-align:center;padding: 25px 0px 0px 0px;margin: 0px 12px;}';
    $str .= '.content a{color:#fff;font-family: "Century Gothic";background: #666666;padding: 8px 17px;}';
    $str .= '.content a:hover{color:#e54040;}';
    $str .= '</style>';
    $str .= '</head>';
    $str .= '<body>';
    $str .= '<div class="wrap">';
    $str .= '<div class="header"></div>';
    $str .= '<div class="content">';
    $str .= '<img src="/static/common/images/errorimg.png" title="error" />';
    $str .= '<p>'.$error.'</p>';
    $str .= '<a href="javascript:void(0);" onclick="javascript:history.go(-1);">Back</a>';
    $str .= '</div>';
    $str .= '</div>';
    $str .= '</body>';
    $str .= '</html>';
    echo $str;
    exit;
}

/**
 * 加密
 */
function encodeStr($value = '') {
	$key = 'dae5747b76492a9ffd4ef78c7f96c0d546d6446e';
	$value = strval($value);
	return str_replace('=', '', base64_encode($value ^ $key));
}

/**
 * 解密
 */
function decodeStr($value = '') {
	$key = 'dae5747b76492a9ffd4ef78c7f96c0d546d6446e';
	$value=base64_decode($value);
	return ($value ^ $key);
}

//验证网站合法性
function checkSiteOwner(){
	return true;
}

/**
 * 调用新浪接口将长链接转为短链接
 * @param  string        $source    申请应用的AppKey
 * @param  array|string  $url_long  长链接，支持多个转换（需要先执行urlencode)
 * @return array
 */
function getSinaShortUrl($url_long){
    $token="f97fbc03cc93a3006e718837ed51df85";
 	$json=file_get_contents("http://yy.gongju.at/?a=addon&m=wxdwz&token={$token}&long=".urlencode($url_long));
 	$arr=json_decode($json,true);
 	return $arr['short'];
    
}
