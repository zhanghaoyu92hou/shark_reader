<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;
class aMember extends Common{
    
    //获取用户累计信息
    public static function getMemberCountMsg($uid){
        global $loginId;
        $type = aLogin::getCache('type');
        $key = $type == 1 ? 'channel_id' : 'agent_id';
        $chargeMoney = Db::name('Order')->where('uid','=',$uid)->where($key,'=',$loginId)->where('is_count','=',1)->where('status','=',2)->sum('money');
        $consumeMoney = Db::name('MemberConsume')->where('uid','=',$uid)->sum('money');
        return ['charge'=>$chargeMoney,'consume'=>$consumeMoney];
    }
    
    //获取用户订单列表
    public static function getOrderList($where,$pages){
        $field = 'id,type,order_no,is_count,money,status,relation_type,relation_name,create_time';
        $list = Db::name('Order')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['status_name'] = $v['status'] == 1 ? '待支付' : '已支付';
                if(in_array($v['type'],[1,3])){
                    $v['from_name'] = self::getFromName($v['relation_type']);
                    if($v['relation_name']){
                        $v['from_name'] .= '：'.$v['relation_name'];
                    }
                }
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Order')
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //获取用户签到列表
    public static function getSignList($where,$pages){
        $list = Db::name('MemberSign')
        ->where($where)
        ->page($pages['page'],$pages['limit'])
        ->order('id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('MemberSign')
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //获取用户消费记录
    public static function getConsumeList($where,$pages){
        $list = Db::name('MemberConsume')
        ->where($where)
        ->page($pages['page'],$pages['limit'])
        ->order('id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('MemberConsume')
            ->where($where)
            ->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //更新用户书币余额
    public static function setMemberMoney($id,$money){
        if($money > 0){
            $data = [
                'money' => Db::raw('money+'.$money),
                'total_money' => Db::raw('total_money+'.$money)
            ];
        }else{
            $data = [
                'money' => Db::raw('money'.$money)
            ];
        }
        $re = Db::name('Member')->where('id','=',$id)->update($data);
        if($re){
            return true;
        }else{
            return false;
        }
    }
    
    private static function getFromName($value){
        $name = '未知';
        switch ($value){
            case 0:$name='直接充值';break;
            case 1:$name='漫画';break;
            case 2:$name='小说';break;
            case 3:$name='听书';break;
            case 4:$name='视频';break;
        }
        return $name;
    }
}