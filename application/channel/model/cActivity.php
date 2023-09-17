<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;

class cActivity extends Common{
    
    //获取活动列表
    public static function getActivityList($where,$pages){
        $field = 'a.*,count(b.id) as charge_nums,IFNULL(sum(b.money),0) as charge_total';
        $list = Db::name('Activity a')
        ->join('order b','a.id=b.relation_id and b.relation_type=0 and b.is_count=1','left')
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
    
}