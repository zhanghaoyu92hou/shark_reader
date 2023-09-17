<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
class mIndex extends Common{
    
    //获取概览数据
    public static function getNumbersData(){
        $time = strtotime('today');
        $today = Db::name('Order')->where('create_time','>=',$time)->where('status','=',2)->sum('money');
        $total = Db::name('Order')->where('status','=',2)->sum('money');
        $wait = $pay = 0;
        $withdraw = Db::name('Withdraw')->where('to_channel_id','=',0)->field('status,sum(money) as money')->group('status')->select();
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
        $platform = Db::name('Channel')->where('status','between',[1,2])->field('type,sum(money) as money')->group('type')->select();
        if($platform){
            foreach ($platform as $val){
                switch ($val['type']){
                    case 1:
                        $channel += $val['money'];
                        break;
                    case 2:
                        $agent += $val['money'];
                        break;
                }
            }
        }
        $sub = Db::name('Member')->where('subscribe','=',1)->count();
        $all = Db::name('Member')->count();
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
        $list = Db::name('Order a')
        ->join('member b','a.uid=b.id')
        ->where('a.status','=',2)
        ->field('sum(a.money) as money,b.nickname,b.create_time')
        ->group('a.uid')
        ->order('money','desc')
        ->limit(10)
        ->select();
        if($list){
            foreach ($list as &$v){
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
        }
        return $list;
    }
    
    //获取充值排名
    public static function getComplaintRank(){
        $list = Db::name('Complaint a')
        ->join('book b','a.book_id=b.id')
        ->field('count(a.id) as count,b.name as book_name')
        ->group('a.book_id')
        ->order('count','desc')
        ->select();
        return $list;
    }
    
    //获取反馈列表
    public static function getFeedBackList(){
        $list = Db::name('Feedback a')
        ->join('member b','a.uid=b.id')
        ->field('a.id,a.content,a.reply,a.create_time,b.nickname')
        ->order('a.id','desc')
        ->limit(5)
        ->select();
        if($list){
            foreach ($list as &$v){
            	$v['is_reply'] = $v['reply'] ? 1 : 2;
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                unset($v['reply']);
            }
        }
        return $list;
    }
    
    //获取近30日用户增长趋势图
    public static function getUserChartData(){
        $cur_time = strtotime('today');
        $start_time = $cur_time - 30*86400;
        $start_date = date('Y-m-d',$start_time);
        $list = Db::name('TaskMember')->where('channel_id','=',0)->where('create_date','>=',$start_date)->field('create_date,add_num,sub_num')->select();
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
                $add_num += $temp[$cur_date]['add_num'];
                $sub_num += $temp[$cur_date]['sub_num'];
            }
            $sub[] = $sub_num;
            $add[] = $add_num;
            $start_time += 86400;
        }
        return ['key'=>$key,'sub'=>$sub,'add'=>$add];
    }
    
    //初始化数据
    public static function clearData($pwd){
        if($pwd === 'resetwebsite'){
            $list = [
                ['name'=>'activity'],
                ['name'=>'book'],
                ['name'=>'book_chapter'],
            	['name'=>'channel'],
            	['name'=>'comments'],
                ['name'=>'complaint'],
                ['name'=>'config'],
                ['name'=>'feedback'],
                ['name'=>'login_log'],
                ['name'=>'manage','data'=>[
                    'id' => 1,
                    'role_id' => 0,
                    'name' => '超级管理员',
                    'login_name' => 'manage',
                    'password' => createPwd(123456),
                    'status' => 1,
                    'create_time' => time()
                ]],
                ['name'=>'material'],
                ['name'=>'member'],
                ['name'=>'member_collect'],
                ['name'=>'member_consume'],
                ['name'=>'member_sign'],
                ['name'=>'message'],
                ['name'=>'order'],
                ['name'=>'order_count'],
                ['name'=>'product'],
                ['name'=>'role'],
                ['name'=>'sale_order'],
                ['name'=>'search_record'],
                ['name'=>'spread'],
                ['name'=>'task'],
                ['name'=>'task_message'],
                ['name'=>'task_member'],
                ['name'=>'task_message_record'],
                ['name'=>'task_order'],
                ['name'=>'video'],
                ['name'=>'view_record'],
                ['name'=>'withdraw'],
                ['name'=>'wx_menu'],
                ['name'=>'wx_reply'],
                ['name'=>'wx_special']
            ];
            $prefix = 'sy_';
            foreach ($list as $v){
                $table = $prefix.$v['name'];
                $sql = 'TRUNCATE TABLE '.$table;
                $re = Db::execute($sql);
                if($re !== false){
                    if(isset($v['data'])){
                        Db::name($v['name'])->insert($v['data']);
                    }
                }
            }
            mLogin::clearCache();
        }
    }
}