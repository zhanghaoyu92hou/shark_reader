<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;

class mOrder extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"订单参数错误",'number'=>'订单参数格式不规范',"gt"=>"订单参数格式不规范"]]
    ];
    
    //获取充值订单列表
    public static function getOrderPageList($where,$pages){
        $field = 'a.*,b.name as channel_name,c.name as agent_name,d.id as uid,d.nickname,d.create_time as user_time';
        $list = Db::name('Order a')
        ->join('channel b','a.channel_id=b.id','left')
        ->join('channel c','a.agent_id=c.id','left')
        ->join('member d','a.uid=d.id','left')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                if($v['channel_id']){
                    if(!$v['channel_name']){
                        $v['channel_name'] = '<font class="text-red">未知</font>';
                    }
                    if($v['agent_id']){
                    	if(!$v['agent_name']){
                    		$v['agent_name'] = '<font class="text-red">未知</font>';
                    	}
                    }else{
                    	$v['agent_name'] = '/';
                    }
                }else{
                    $v['channel_name'] = '总站';
                    $v['agent_name'] = '/';
                }
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
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //获取商品订单列表
    public static function getSaleOrderPageList($where,$pages){
        $field = 'a.*,b.name as channel_name,c.nickname';
        $list = Db::name('SaleOrder a')
        ->join('channel b','a.channel_id=b.id','left')
        ->join('member c','a.uid=c.id','left')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                if($v['channel_id']){
                    if(!$v['channel_name']){
                        $v['channel_name'] = '<font class="text-red">未知</font>';
                    }
                }else{
                    $v['channel_name'] = '总站';
                }
                $v['status_name'] = self::getSaleOrderStatusName($v['status']);
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('SaleOrder a')
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //获取商品订单状态
    private static function getSaleOrderStatusName($status){
        $name = '';
        switch ($status){
            case 1:$name='待支付';break;
            case 2:$name='待发货';break;
            case 3:$name='待收货';break;
            case 4:$name='已完成';break;
        }
        return $name;
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