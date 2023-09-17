<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\admin\model\mConfig;
use app\common\model\myRequest;
use app\common\model\myAliyunoss;


class Upload extends Common{
    
    //裁剪图片
    public function crop(){
        if($this->request->isAjax()){
            $post = myRequest::post('img');
            $image = $post['img'];
            if(!empty($image)){
                $config = mConfig::getConfig('alioss');
                if(!$config || !isset($config['accessKey'])){
                    res_return('您尚未配置阿里云oss参数');
                }
                myAliyunoss::$config = $config;
                $content = base64_decode($image);
                $name = md5(microtime().mt_rand(10000,99999)).'.jpg';
                $savename = 'images/'.date('Ymd').'/'.$name;
                $url = myAliyunoss::putObject($savename, $content);
                if($url){
                    res_return(['url'=>$url]);
                }else{
                    res_return('上传失败,请重试');
                }
            }else{
                res_return('未检测到上传文件');
            }
        }else{
            $get = myRequest::get('crop_size');
            $crop_size = $get['crop_size'];
            $reg = '/^[\d]{1,}[x][\d]{1,}$/';
            if(!preg_match($reg, $crop_size)){
                res_return('裁剪尺寸格式错误');
            }
            $arr = explode('x', $crop_size);
            $config = ['width'=>$arr[0],'height'=>$arr[1],'ratio'=>($arr[0]/$arr[1])];
            $this->assign('config',$config);
            return $this->fetch();
        }
    }
    
    //处理图片上传
    public function doUploadImg(){
        $config = mConfig::getConfig('alioss');
        if(!$config || !isset($config['accessKey'])){
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $file = request()->file('file');
        $validate = array(
            'size' => 1024*1024*1,
            'ext' => 'jpg,jpeg,png'
        );
        $date = date('Ymd');        
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path').'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
        ->validate($validate)
        ->move($path,$name);
        if($info){
            $filename = $path.'/'.$name;
            $savename = 'images/'.$date.'/'.$name;
            $url = myAliyunoss::putLocalFile($savename, $filename);
            @unlink($filename);
            if($url){
                res_return(['url'=>$url]);
            }else{
                res_return('上传失败,请重试');
            }
        }else{
            res_return($file->getError());
        }
    }
    
    
    //处理图标上传
    public function doUploadIcon(){
        $config = mConfig::getConfig('alioss');
        if(!$config || !isset($config['accessKey'])){
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $file = request()->file('file');
        $validate = array(
            'size' => 1024*100,
            'ext' => 'jpg,jpeg,png'
        );
        $date = date('Ymd');        
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path').'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
        ->validate($validate)
        ->move($path,$name);
        if($info){
            $filename = $path.'/'.$name;
            $savename = 'images/'.$date.'/'.$name;
            $url = myAliyunoss::putLocalFile($savename, $filename);
            @unlink($filename);
            if($url){
                res_return(['url'=>$url]);
            }else{
                res_return('上传失败,请重试');
            }
        }else{
            res_return($file->getError());
        }
    }
    
    //上传分集zip
    public function doUploadZip(){
        set_time_limit(0);
        $file = request()->file('file');
        $validate = array(
            'size' => 1024*1024*20,
            'ext' => 'zip'
        );
        $date = date('Ymd');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path').'static/temp/zip';
        $name = self::createOnlyName($path, $ext);
        $info = $file
        ->validate($validate)
        ->move($path,$name);
        if($info){
            res_return(['filename'=>$name]);
        }else{
            res_return($file->getError());
        }
    }
    
    //处理分片上传文件
    public function doUploadFile(){
        $post = myRequest::post('chunk,chunks,name');
        $cur_chunk = $post['chunk']+1;
        $total = $post['chunks'];
        $server_tmp_name = $_FILES['file']['tmp_name'];
        $path = env('root_path').'static/temp/zip';
        if($total == 1){
            $file = request()->file('file');
            $validate = array(
                'size' => 1024*1024,
                'ext' => 'zip'
            );
            $date = date('Ymd');
            $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $name = self::createOnlyName($path, $ext);
            $info = $file
            ->validate($validate)
            ->move($path,$name);
            if($info){
                res_return(['filename'=>$name]);
            }else{
                res_return($file->getError());
            }
        }else{
            $name = $post['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if($ext != 'zip'){
                echo json_encode(['code'=>0,'msg'=>'文件格式不正确']);
                exit;
            }
            $file_name = md5($name).'.zip';
            $path .= '/'.$file_name;
            if($cur_chunk == 1){
                if(@is_file($path)){
                    @unlink($path);
                }
                move_uploaded_file($server_tmp_name, $path);
                res_return('ok');
            }else{
                if(!@is_file($path)){
                    echo json_encode(['code'=>0,'msg'=>'文件格式不正确']);
                    exit;
                }
                $blob = file_get_contents($server_tmp_name);
                @unlink($server_tmp_name);
                file_put_contents($path,$blob,FILE_APPEND);
                if($cur_chunk == $total){
                    res_return(['filename'=>$file_name]);
                }else{
                    res_return('ok');
                }
            }
        }
    }
    
    //处理分片上传视频
    public function doUploadVideo(){
        $config = mConfig::getConfig('alioss');
        if(!$config || !isset($config['accessKey'])){
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $post = myRequest::post('chunk,chunks,name');
        $cur_chunk = $post['chunk']+1;
        $total = $post['chunks'];
        $path = env('root_path').'static/temp/video';
        if($total == 1){
            $file = request()->file('file');
            $validate = array(
                'size' => 1024*500,
                'ext' => 'mp4'
            );
            $date = date('Ymd');
            $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $name = self::createOnlyName($path, $ext);
            $info = $file
            ->validate($validate)
            ->move($path,$name);
            if($info){
                $savename = 'video/'.date('Ymd').'/'.md5(microtime().mt_rand(100000,9999999)).'.'.$ext;
                $filename = $path.'/'.$name;
                $url = myAliyunoss::putLocalFile($savename, $filename);
                @unlink($filename);
                if($url){
                    res_return(['url'=>$url,'file_key'=>$savename]);
                }else{
                    res_return('上传失败，请重试');
                }
            }else{
                res_return($file->getError());
            }
        }else{
            $name = $post['name'];
            $server_tmp_name = $_FILES['file']['tmp_name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if($ext != 'mp4'){
                res_return('文件格式不正确');
            }
            $session_name = md5($name);
            $upsession = session($session_name);
            $savename = 'video/'.date('Ymd').'/'.md5($name).'.'.$ext;
            if($cur_chunk == 1){
                if($upsession){
                    if(isset($upsession['position'])){
                        res_return('上传异常1');
                    }
                }
                $upsession = [
                    'object' => $savename,
                    'is_file' => false
                ];
                $exits = myAliyunoss::fileExits($savename);
                $upsession['is_file'] = $exits;
                $position = 0;
            }else{
                if(!$upsession || $upsession['object'] != $savename){
                    res_return('上传异常2');
                }
                if($upsession['is_file'] === false){
                    $position = $upsession['position'];
                }
            }
            if($upsession['is_file'] === false){
                $content = file_get_contents($server_tmp_name);
                @unlink($server_tmp_name);
                $res = myAliyunoss::appendFiles($savename, $content, $position);
                if($total == $cur_chunk){
                    session($session_name,null);
                    $url = 'https://'.$config['bucket'].'.'.$config['url'].'/'.$savename;
                    res_return(['url'=>$url,'file_key'=>$savename]);
                }else{
                    $upsession['position'] = $res;
                    session($session_name,$upsession);
                    res_return('ok');
                }
            }else{
                @unlink($server_tmp_name);
                session($session_name,$upsession);
                $url = 'https://'.$config['bucket'].'.'.$config['url'].'/'.$savename;
                res_return(['url'=>$url,'file_key'=>$savename]);
            }
        }
    }
    
    //创建唯一文件名
    private function createOnlyName($path,$ext){
        $name = md5(microtime().mt_rand(10000,99999)).'.'.$ext;
        $file = $path.'/'.$name;
        if(@is_file($file)){
            self::createOnlyName($path,$ext);
        }else{
            return $name;
        }
    }
}