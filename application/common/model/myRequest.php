<?php
namespace app\common\model;

use think\facade\Request;

class myRequest{
    
    /**
     * 获取post传参
     * @param mixed $field 要接收的参数
     * @return mixed[]
     */
    public static function post($field=''){
        $data = [];
        $post = Request::post();
        if($field){
            $field = is_array($field) ? $field : explode(',', $field);
            foreach ($field as $v){
                if(is_array($post) && array_key_exists($v, $post)){
                    $data[$v] = $post[$v];
                }else{
                    $data[$v] = '';
                }
            }
        }else{
            if($post && is_array($post)){
                $data = $post;
            }
        }
        return $data;
    }
    
    /**
     * 获取get传参
     * @param mixed $field 要接收的参数
     * @return mixed[]
     */
    public static function get($field=''){
        $data = [];
        $get = Request::get();
        if($field){
            $field = is_array($field) ? $field : explode(',', $field);
            foreach ($field as $v){
                if(is_array($get) && array_key_exists($v, $get)){
                    $data[$v] = $get[$v];
                }else{
                    $data[$v] = '';
                }
            }
        }else{
            if($get && is_array($get)){
                foreach ($get as $k=>$v){
                    if(strlen($v) > 0){
                        $data[$k] = $v;
                    }
                }
            }
        }
        return $data;
    }
    
    //获取GET单个主键必传参数
    public static function getId($title,$name='id'){
        $rules = [
            $name =>  ["require|number|gt:0",["require"=>$title."参数错误",'number'=>$title.'参数格式不规范',"gt"=>$title."参数格式不规范"]]
        ];
        $value = myValidate::getData($rules,$name,'get');
        return $value;
    }
    
    //获取POST单个主键必传参数
    public static function postId($title,$name='id'){
        $rules = [
            $name =>  ["require|number|gt:0",["require"=>$title."参数错误",'number'=>$title.'参数格式不规范',"gt"=>$title."参数格式不规范"]]
        ];
        $value = myValidate::getData($rules,$name);
        return $value;
    }
    
    //获取指定值中的一项
    public static function getListOne($title,$values=[]){
        $value = '';
        if($values){
            $values = !is_array($values) ? explode(',', $values) : $values;
            $value = Request::get($title);
            if(!in_array($value, $values)){
               res_return('参数不合法');
            }
        }
        return $value;
    }
    
    /**
     * 获取分页参数
     * @return mixed[]
     */
    public static function getPageParams(){
        $field = 'page,limit';
        $data = self::get($field);
        if(!$data['page']){
            $data['page'] = 1;
        }
        if(!$data['limit']){
            $data['limit'] = 10;
        }
        return $data;
    }
}