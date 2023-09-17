<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;

class aOrder extends Common{
    
    //获取充值订单列表
    public static function getOrderPageList($where,$pages){
        $field = 'a.*,b.nickname,b.create_time as user_time';
        $list = Db::name('Order a')
        ->join('member b','a.uid=b.id','left')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                if($v['uid']){
                    $v['user_info'] = $v['nickname'].'<br />'.date('Y-m-d H:i',$v['user_time']);
                }else{
                    $v['user_info'] = '<font class="text-red">用户异常</font>';
                }
                $v['status_name'] = $v['status'] == 1 ? '待支付' : '已支付';
                if(in_array($v['type'],[1,3])){
                    $v['from_name'] = self::getFromName($v['relation_type']);
                    if($v['relation_name']){
                        $v['from_name'] .= '：'.$v['relation_name']; 
                    }
                }
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Order a')
            ->join('member b','a.uid=b.id','left')
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    private static function getFromName($value){
        $name = '未知';
        switch ($value){
            case 0:$name='直接充值';break;
            case 1:$name='漫画';break;
            case 2:$name='小说';break;
            case 3:$name='听书';break;
            case 4:$name='视频';break;
            case 5:$name='推广';break;
        }
        return $name;
    }
}