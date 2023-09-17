<?php
namespace app\common\model;

use OSS\OssClient;
use OSS\Core\OssException;

class myAliyunoss{
    
    //阿里云上传参数
    public static $config;
    
    /**
     * 上传阿里云对象文件
     * @param string $savename 保存文件名
     * @param string $content 文件内容
     * @return string|boolean
     */
    public static function putObject($savename,$content){
        $res = false;
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $ossClient->putObject(self::$config['bucket'], $savename, $content);
                $res = 'https://'.self::$config['bucket'].'.'.self::$config['url'].'/'.$savename;
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
    
    /**
     * 上传本地文件
     * @param string $savename 保存文件名
     * @param string $filename 本地文件路径
     * @return string|boolean
     */
    public static function putLocalFile($savename,$filename){
        $res = '';
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $ossClient->uploadFile(self::$config['bucket'], $savename, $filename);
                $res = 'https://'.self::$config['bucket'].'.'.self::$config['url'].'/'.$savename;
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
    
    /**
     * 判断文件是否存在
     * @param string $savename 保存的文件名
     * @return boolean
     */
    public static function fileExits($savename){
        $res = false;
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $res = $ossClient->doesObjectExist(self::$config['bucket'],$savename);
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
    
    /**
     * 删除单个文件
     * @param string $filename 保存的文件名
     * @return boolean
     */
    public static function delFile($filename){
        $res = false;
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $res = $ossClient->deleteObject(self::$config['bucket'], $filename);
                $res = true;
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
    
    /**
     * 删除多个文件
     * @param mixed $filenames 保存的文件名数组
     * @return boolean
     */
    public static function delFiles($filenames){
        $filenames = is_array($filenames) ? $filenames : explode(',', $filenames);
        $res = false;
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $ossClient->deleteObjects(self::$config['bucket'], $filenames);
                $res = true;
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
    
    //追加上传文件
    public static function appendFiles($savename,$content,$position){
        $res = false;
        if(isset(self::$config['accessKey'])){
            try {
                $ossClient = new OssClient(self::$config['accessKey'], self::$config['secretKey'], self::$config['url']);
                $position = $ossClient->appendObject(self::$config['bucket'], $savename, $content, $position);
                return $position;
            }catch (OssException $e){
                $res = false;
            }
        }
        return $res;
    }
}