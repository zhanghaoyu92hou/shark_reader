<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;
class cIndex extends Common{
    
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
        $today = Db::name('Order')
                ->where('create_time','>=',$time)
                ->where('channel_id','=',$loginId)
                ->where('is_count','=',1)
                ->where('status','=',2)
                ->sum('money');
        $total = Db::name('Order')
                ->where('status','=',2)
                ->where('channel_id','=',$loginId)
                ->where('is_count','=',1)
                ->sum('money');
        $wait = $pay = 0;
        $withdraw = Db::name('Withdraw')->where('to_channel_id','=',$loginId)->field('status,sum(money) as money')->group('status')->select();
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
        $agent = Db::name('Channel')->where('parent_id','=',$loginId)->sum('money');
        
        $sub = Db::name('Member')->where('channel_id','=',$loginId)->where('subscribe','=',1)->count();
        $all = Db::name('Member')->where('channel_id','=',$loginId)->count();
        $res = [
            'order' => ['today'=>$today,'total'=>$total],
            'withdraw' => ['wait'=>$wait,'pay'=>$pay],
            'platform' => ['channel'=>$channel,'agent'=>$agent],
            'member' => ['sub'=>$sub,'all'=>$all]
        ];
        return $res;
    }
    
    //获取充值排名
    public static function getChangeRank(){
        global $loginId;
        $list = Db::name('Order a')
        ->join('member b','a.uid=b.id')
        ->field('sum(a.money) as money,b.nickname,b.create_time')
        ->where('a.channel_id','=',$loginId)
        ->where('a.status','=',2)
        ->where('a.is_count','=',1)
        ->group('a.uid')
        ->limit(10)
        ->order('money','desc')
        ->select();
        if($list){
            foreach ($list as &$v){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
        }
        return $list;
    }
    
    //获取投诉排名
    public static function getComplaintRank(){
        global $loginId;
        $list = Db::name('Complaint a')
        ->join('book b','a.book_id=b.id')
        ->field('count(a.id) as count,b.name as book_name')
        ->where('a.channel_id','=',$loginId)
        ->group('a.book_id')
        ->order('count','desc')
        ->select();
        return $list;
    }
    
    //获取反馈列表
    public static function getFeedBackList(){
        global $loginId;
        $list = Db::name('Feedback a')
        ->join('member b','a.uid=b.id')
        ->where('a.channel_id','=',$loginId)
        ->field('a.content,a.create_time,b.nickname')
        ->order('a.id','desc')
        ->limit(5)
        ->select();
        if($list){
            foreach ($list as &$v){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
        }
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