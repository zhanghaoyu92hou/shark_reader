<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
use app\common\model\myValidate;

class mProduct extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键格式不规范',"gt"=>"主键格式不规范"]],
        'name' =>  ["require|max:200",["require"=>"请输入商品名称",'max'=>'商品名称最多支持200个字符']],
        'cover' =>  ['max:255',['max'=>'商品封面图片异常']],
        'summary' =>  ['max:255',['max'=>'商品简介异常']],
    	'content' => ['max:10000',['max'=>'商品详情字数超出限制']],
        'moeny' => ["require|float|gt:0",["require"=>'请输入商品金额',"number"=>"商品金额格式不规范","gt"=>"商品金额格式不规范"]],
        'stock' => ["require|number",["require"=>'请输入商品库存',"number"=>"商品库存格式不规范"]],
        'sort_num' => ["number",["number"=>"排序值必须为数字"]],
        'area' => ["array",["array"=>"发布区域参数异常"]],
        'category' => ["array",["array"=>"商品分类参数异常"]],
        'status' => ["require|in:1,2",["require"=>"请选择活动状态","in"=>"未指定该活动状态"]],
        'is_hot' => ["require|in:1,2",["require"=>"请选择是否推荐","in"=>"未指定该推荐状态"]],
        'hot_num' => ["number",["number"=>"请输入数值类型的人气值"]],
        'share_title' =>  ["max:100",['max'=>'分享标题最多支持100个字符']],
        'share_desc' =>  ["max:500",['max'=>'分享描述最多支持500个字符']],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //获取商品列表
    public static function getProductList($where,$pages){
        $field = 'a.*,count(b.id) as charge_nums';
        $list = Db::name('Product a')
        ->join('sale_order b','a.id=b.pid','left')
        ->where($where)
        ->field($field)
        ->group('a.id')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            $count = Db::name('Product a')->where($where)->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //更新商品
    public static function doneProduct($field){
        $data = myValidate::getData(self::$rules, $field);
        if($data['area']){
            $data['area'] = ','.implode(',', $data['area']).',';
        }
        if($data['category']){
            $data['category'] = ','.implode(',', $data['category']).',';
        }
        $content = $data['content'];
        unset($data['content']);
        if(isset($data['id'])){
        	$product_id = $data['id'];
            $re = parent::saveIdData('Product', $data);
        }else{
            $data['create_time'] = time();
            $re = Db::name('Product')->insertGetId($data);
            $product_id = $re;
        }
        if($re){
        	if($content){
        		saveBlock($content,$product_id,'product');
        	}
        	cache('product_'.$product_id,null);
            res_return();
        }else{
            res_return('更新失败，请重试');
        }
    }
    
    //获取商品属性选项
    public static function getProductRadioList(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [['val'=>1,'text'=>'上架','default'=>0],['val'=>2,'text'=>'下架','default'=>1]]
            ],
            'is_hot' => [
                'name' => 'is_hot',
                'option' => [['val'=>1,'text'=>'是','default'=>1],['val'=>1,'text'=>'否','default'=>0]]
            ],
        ];
        return $option;
    }
    
    //获取商品缓存
    public static function getBlockData(){
        $field = 'id,name,cover,money,summary,buy_num';
        $hot = Db::name('Product')->where('status','=',1)->field($field)->order('hot_num','DESC')->limit(8)->select();
        $foot_hot = Db::name('Product')->where('status','=',1)->where('is_hot','=',1)->field($field)->order('hot_num','DESC')->limit(8)->select();
        $temp = [];
        $key = 'product_area';
        $area = mConfig::getConfig($key);
        if($area){
            foreach ($area as $v){
                $list = Db::name('Product')->where('status','=',1)->where('area','like','%,'.$v.',%')->field($field)->order('sort_num','DESC')->order('id','desc')->limit(8)->select();
                if($list){
                    $temp[] = [
                        'name'=>$v,
                        'url' => '/index/Product/more.html?area='.urlencode($v),
                        'child'=>$list
                    ];
                }
            }
        }
        $res = ['hot'=>$hot,'area'=>$temp,'foot_hot'=>$foot_hot];
        return $res;
    }
    
}