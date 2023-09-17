<?php
namespace app\admin\model;
use app\admin\model\Common;
use app\common\model\myValidate;
use think\Db;

class mMaterial extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'title' =>  ["require|max:200",["require"=>"请输入文案标题",'max'=>'文案标题最多支持200个字符']],
        'cover' =>  ['require|max:255',["require"=>"请上传文案封面",'max'=>'文案封面异常']],
    ];
    
    //处理更新文案
    public static function doneMaterial($field){
        $data = myValidate::getData(self::$rules,$field);
        if(array_key_exists('id', $data)){
            $re = parent::saveIdData('Material',$data);
        }else{
            $re = parent::add('Material', $data);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //获取文案分组信息
    public static function getMaterialGroup(){
        $material = Db::name('Material')->field('title,cover')->select();
        $title = $cover = $res = [];
        if($material){
            $max = count($material);
            foreach ($material as $v){
                $title[] = $v['title'];
                $cover[] = $v['cover'];
            }
            $res = [
                'count' => $max,
                'title' => $title,
                'cover' => $cover
            ];
        }
        return $res;
    }
}