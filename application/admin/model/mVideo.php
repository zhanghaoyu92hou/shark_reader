<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
use app\common\model\myAliyunoss;
use app\common\model\myCache;

class mVideo extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:200",["require"=>"请输入视频标题",'max'=>'视频标题最多支持200个字符']],
        'cover' =>  ['max:255',['max'=>'视频封面异常']],
        'url' =>  ['require|max:255',['require'=>'请上传视频','max'=>'视频参数异常']],
        'file_key' =>  ['require|max:255',['require'=>'请上传视频','max'=>'视频参数异常']],
        'summary' =>  ["max:500",['max'=>'视频简介最多支持500个字符']],
        'status' => ["require|in:1,2",["require"=>"请选择视频状态","in"=>"未指定该视频状态"]],
        'is_hot' => ["require|in:1,2",["require"=>"请选择是否推荐","in"=>"未指定该推荐状态"]],
        'sort_num' => ["number",["number"=>"排序值必须为数字"]],
        'area' => ["array",["array"=>"发布区域参数异常"]],
        'cate' => ["array",["array"=>"小说分类参数异常"]],
        'free_type' => ["require|in:1,2",["require"=>"请选择该视频是否免费","in"=>"未指定该视频免费状态"]],
        'money' => ["require|number",["require"=>"请输入视频收费书币数量","in"=>"书币数量必须为正整数"]],
        'hot_num' => ["number",["require"=>"人气值必须为正整数"]],
        'share_title' =>  ["max:100",['max'=>'分享标题最多支持100个字符']],
        'share_desc' =>  ["max:500",['max'=>'分享描述最多支持500个字符']],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //删除视频
    public static function delete($id){
        $video = parent::getById('Video',$id,'id,file_key');
        if(empty($video)){
            res_return('视频信息异常');
        }
        $config = myCache::getAliossCache();
        if(!$config){
        	res_return('您尚未配置阿里云参数');
        }
        myAliyunoss::$config = $config;
        $res = false;
        $re = Db::name('Video')->where('id','=',$id)->delete();
        if($re){
            $res = true;
            if($video['file_key']){
            	myAliyunoss::delFile($video['file_key']);
            }
        }
        return $res;
    }
    
    //获取视频属性选项
    public static function getVideoRadioList(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [['val'=>1,'text'=>'上架','default'=>0],['val'=>2,'text'=>'下架','default'=>1]]
            ],
            'free_type' => [
                'name' => 'free_type',
                'option' => [['val'=>1,'text'=>'免费','default'=>0],['val'=>2,'text'=>'收费','default'=>1]]
            ],
            'is_hot' => [
                'name' => 'is_hot',
                'option' => [['val'=>1,'text'=>'是','default'=>0],['val'=>2,'text'=>'否','default'=>1]]
            ]
        ];
        return $option;
    }
    
    //获取视频缓存
    public static function getBlockData(){
        $field = 'id,name,cover,free_type';
        $hot = Db::name('Video')->where('status','=',1)->field($field)->order('hot_num','DESC')->limit(8)->select();
        $field .= ',summary';
        $foot_hot = Db::name('Video')->where('status','=',1)->where('is_hot','=',1)->field($field)->order('hot_num','DESC')->limit(9)->select();
        $temp = [];
        $key = 'video_area';
        $area = mConfig::getConfig($key);
        if($area){
            foreach ($area as $v){
                $list = Db::name('Video')->where('status','=',1)->where('area','like','%,'.$v.',%')->field($field)->order('sort_num','DESC')->order('id','desc')->limit(8)->select();
                if($list){
                    $temp[] = [
                        'name'=>$v,
                        'url' => '/index/Video/more.html?area='.urlencode($v),
                        'child'=>$list
                    ];
                }
            }
        }
        $res = ['hot'=>$hot,'area'=>$temp,'foot_hot'=>$foot_hot];
        return $res;
    }
    
}