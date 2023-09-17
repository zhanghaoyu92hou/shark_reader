<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;

class mActivity extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:200",["require"=>"请输入活动标题",'max'=>'活动标题最多支持200个字符']],
        'cover' =>  ['max:255',['max'=>'活动封面图片异常']],
        'bg' =>  ['max:255',['max'=>'活动背景图片异常']],
        'start_time' =>  ["require|date",['require'=>'请选择活动开始时间','date'=>'活动开始时间参数错误']],
        'end_time' =>  ["require|date",['require'=>'请选择活动结束时间','date'=>'活动结束时间参数错误']],
        'status' => ["require|in:1,2",["require"=>"请选择活动状态","in"=>"未指定该活动状态"]],
        'is_first' => ["require|in:1,2",["require"=>"请选择充值限制次数","in"=>"未指定该限制次数类型"]],
        'moeny' => ["require|float",["require"=>'请输入充值金额',"float"=>"充值金额格式不规范"]],
        'send_moeny' => ["require|number",["require"=>'请输入赠送书币',"number"=>"赠送书币格式不规范"]],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //获取活动列表
    public static function getActivityList($where,$pages){
        $field = 'a.*,count(b.id) as charge_nums,IFNULL(sum(b.money),0) as charge_total';
        $list = Db::name('Activity a')
        ->join('order b','a.id=b.relation_id and b.relation_type=0 and b.status=2','left')
        ->where($where)
        ->field($field)
        ->group('a.id')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            $count = Db::name('Activity a')->where($where)->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //获取活动属性选项
    public static function getActivityRadioList(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [['val'=>1,'text'=>'启用','default'=>0],['val'=>2,'text'=>'禁用','default'=>1]]
            ],
            'is_first' => [
                'name' => 'is_first',
                'option' => [['val'=>1,'text'=>'仅限一次','default'=>1],['val'=>2,'text'=>'不限次数','default'=>0]]
            ]
        ];
        return $option;
    }
    
}