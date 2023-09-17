<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;
class aIndex extends Common{
    
    //检查是否有未读公告
    public static function checkMessage(){
        global $loginId;
        $res = ['id'=>0];
        $cur = Db::name('Message a')
        ->join('message_read b','a.id=b.message_id and b.channel_id='.$loginId,'left')
        ->where('a.type','=',1)
        ->where('a.status','=',1)
        ->field('a.id,a.title,IFNULL(b.id,0) as read_id')
        ->having('read_id=0')
        ->order('a.id','ASC')
        ->find();
        if($cur){
            $res = ['id'=>$cur['id'],'title'=>$cur['title']];
        }
        return $res;
    }
    
    //获取概览数据
    public static function getNumbersData(){
        $time = strtotime('today');
        global $loginId;
        $type = aLogin::getCache('type');
        $key = ($type == 1) ? 'channel_id' : 'agent_id';
        $today = Db::name('Order')
                ->where('create_time','>=',$time)
                ->where($key,'=',$loginId)
                ->where('is_count','=',1)
                ->where('status','=',2)
                ->sum('money');
        $total = Db::name('Order')
                ->where('status','=',2)
                ->where($key,'=',$loginId)
                ->where('is_count','=',1)
                ->sum('money');
        $wait = $pay = 0;
        $withdraw = Db::name('Withdraw')->where('channel_id','=',$loginId)->field('status,sum(money) as money')->group('status')->select();
        if($withdraw){
            foreach ($withdraw as $v){
                switch ($v['status']){
                    case 0:
                        $wait += $v['money'];
                        break;
                    case 1:
                        $pay += $v['money'];
                        break;
                }
            }
        }
        $channel = $agent = 0;
        $channel = Db::name('Channel')->where('id','=',$loginId)->value('money');
        $cur_type = aLogin::getCache('type');
        if($cur_type == 1){
            $agent = Db::name('Channel')->where('parent_id','=',$loginId)->sum('money');
        }
        $sub = Db::name('Member')->where($key,'=',$loginId)->where('subscribe','=',1)->count();
        $all = Db::name('Member')->where($key,'=',$loginId)->count();
        $res = [
            'order' => ['today'=>$today,'total'=>$total],
            'withdraw' => ['wait'=>$wait,'pay'=>$pay],
            'platform' => ['channel'=>$channel,'agent'=>$agent],
            'member' => ['sub'=>$sub,'all'=>$all],
            'cur_type' => $cur_type
        ];
        return $res;
    }
    
    //获取充值排名
    public static function getChangeRank(){
        global $loginId;
        $type = aLogin::getCache('type');
        $key = ($type == 1) ? 'a.channel_id' : 'a.agent_id';
        $list = Db::name('Order a')
        ->join('member b','a.uid=b.id')
        ->field('sum(a.money) as money,b.nickname')
        ->where($key,'=',$loginId)
        ->where('a.status','=',2)
        ->where('a.is_count','=',1)
        ->group('a.uid')
        ->limit(10)
        ->order('money','desc')
        ->select();
        return $list;
    }
    
    //获取近30日用户增长趋势图
    public static function getUserChartData(){
        global $loginId;
        $cur_time = strtotime('today');
        $start_time = $cur_time - 30*86400;
        $start_date = date('Y-m-d',$start_time);
        $list = Db::name('TaskMember')->where('channel_id','=',$loginId)->where('create_date','>=',$start_date)->field('create_date,add_num,sub_num')->select();
        $temp = [];
        foreach ($list as $v){
            $temp[$v['create_date']] = $v;
        }
        unset($list);
        $add = $sub = $key = [];
        while ($start_time < $cur_time){
            $cur_date = date('Y-m-d',$start_time);
            $key[] = date('m/d',$start_time);
            $add_num = $sub_num = 0;
            if(isset($temp[$cur_date])){
                $add_num = $temp[$cur_date]['add_num'];
                $sub_num = $temp[$cur_date]['sub_num'];
            }
            $sub[] = $sub_num;
            $add[] = $add_num;
            $start_time += 86400;
        }
        return ['key'=>$key,'sub'=>$sub,'add'=>$add];
    }
}