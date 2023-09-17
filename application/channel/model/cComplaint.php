<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;

class cComplaint extends Common{
    
    
    //获取投诉列表
    public static function getComplaintList($where,$pages){
        $field = 'a.*,b.nickname,c.name as book_name';
        $list = Db::name('Complaint a')
        ->join('member b','a.uid=b.id','left')
        ->join('book c','a.book_id=c.id','left')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['type_name'] = self::getTypeName($v['type']);
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Complaint a')->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //获取类型名称
    private static function getTypeName($type){
        $name = '未知';
        switch ($type){
            case 1: $name = '色情';break;
            case 2: $name = '血腥';break;
            case 3: $name = '暴力';break;
            case 4: $name = '违法';break;
            case 5: $name = '盗版';break;
            case 6: $name = '其他';break;
        }
        return $name;
    }
}