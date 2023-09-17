<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;
class aChart extends Common{
    
    //获取用户统计信息
    public static function getMemberCount(){
        global $loginId;
        $type = aLogin::getCache('type');
        $key = ($type == 1) ? 'channel_id' : 'agent_id';
        $cur_time = strtotime('today');
        $today = $yesterday = $month = $total = $temp = [
            'add_num' => 0,
            'sub_num' => 0,
            'sex0' => 0,
            'sex1' => 0,
            'sex2' => 0,
            'charge_money' => 0,
            'charge_nums' => 0
        ];
        $today_list = Db::name('Member')->where($key,'=',$loginId)->where('create_time','>=',$cur_time)->field('id,subscribe,sex,is_charge')->select();
        if($today_list){
            foreach($today_list as $v){
                $today['add_num'] += 1;
                if($v['subscribe'] == 1){
                    $today['sub_num'] += 1;
                }
                $today['sex'.$v['sex']] += 1;
                if($v['is_charge'] == 1){
                    $order_money = Db::name('Order')->where('uid','=',$v['id'])->where('is_count','=',1)->where('status','=',2)->sum('money');
                    if($order_money){
                        $today['charge_money'] += $order_money;
                        $today['charge_nums'] += 1;
                    }
                }
            }
        }
        $start = $cur_time - 86400*30;
        $start_date = date('Y-m-d',$start);
        $select_date = date('Y-m-d',($cur_time-40*86400));
        $field = 'create_date,add_num,sub_num,sex0,sex1,sex2,charge_money,charge_nums';
        $list = Db::name('TaskMember')->where('channel_id','=',$loginId)->field($field)->select();
        $temps = $data = [];
        if($list){
            $yes_day = date('Y-m-d',strtotime('-1 day'));
            $month_day = date('Y-m').'-01';
            foreach ($list as $val){
                $temps[$val['create_date']] = $val;
                if($val['create_date'] === $yes_day){
                    $yesterday = $val;
                }
                if($val['create_date'] >= $month_day){
                    $month['add_num'] += $val['add_num'];
                    $month['sub_num'] += $val['sub_num'];
                    $month['sex0'] += $val['sex0'];
                    $month['sex1'] += $val['sex1'];
                    $month['sex2'] += $val['sex2'];
                    $month['charge_money'] += $val['charge_money'];
                    $month['charge_nums'] += $val['charge_nums'];
                }
                $total['add_num'] += $val['add_num'];
                $total['sub_num'] += $val['sub_num'];
                $total['sex0'] += $val['sex0'];
                $total['sex1'] += $val['sex1'];
                $total['sex2'] += $val['sex2'];
                $total['charge_money'] += $val['charge_money'];
                $total['charge_nums'] += $val['charge_nums'];
            }
        }
        $cur_date = date('Y-m-d');
        while ($start_date < $cur_date){
            if(isset($temps[$start_date])){
                $data[] = $temps[$start_date];
            }else{
                $temp['create_date'] = $start_date;
                $data[] = $temp;
            }
            $start_date = date('Y-m-d',(strtotime($start_date)+86400));
        }
        $data = array_reverse($data);
        $res = ['today'=>$today,'yesterday'=>$yesterday,'month'=>$month,'total'=>$total,'data'=>$data];
        return $res;
    }
    
    
    //获取分成统计信息
    public static function getOrderCountList($where,$pages){
        $list = Db::name('Order a')
        ->join('order_count b','a.id=b.order_id','left')
        ->join('channel c','b.channel_id=c.id','left')
        ->join('channel d','b.agent_id=d.id','left')
        ->where($where)
        ->field('a.order_no,a.create_time,b.channel_id,b.agent_id,b.agent_money,b.channel_money,c.name as channel_name,c.ratio as channel_ratio,d.name as agent_name,d.ratio as agent_ratio')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                if($v['channel_id']){
                    $v['channel_ratio'] .= '%';
                }else{
                    $v['channel_name'] = 'N/A';
                    $v['channel_ratio'] = 'N/A';
                    $v['channel_money'] = 'N/A';
                }
                if($v['agent_id']){
                    $v['agent_ratio'] .= '%';
                }else{
                    $v['agent_name'] = 'N/A';
                    $v['agent_ratio'] = 'N/A';
                    $v['agent_money'] = 'N/A';
                }
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Order a')->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //获取推广统计
    public static function getSpreadCountList($where,$pages){
        $field = 'a.id,a.name,a.member_num,a.visitor_num,a.create_time,b.name as book_name';
        $list = Db::name('Spread a')
        ->join('book b','a.book_id=b.id','left')
        ->where($where)
        ->field($field)
        ->group('a.id')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $order = self::getSpreadCountInfo($v['id']);
                $v['charge_money'] = $order['charge'];
                $v['charge_user'] = $order['count'];
                $sub_rate = $charge_rate = 0;
                if($v['visitor_num'] > 0){
                    $sub_rate = round($v['member_num']/$v['visitor_num'],2)*100;
                    $charge_rate = round($v['charge_user']/$v['visitor_num'],2)*100;
                }
                $v['sub_rate'] = $sub_rate.'%';
                $v['charge_rate'] = $charge_rate.'%';
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Spread a')
            ->join('book b','a.book_id=b.id','left')
            ->where($where)
            ->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //获取推广统计详情
    private static function getSpreadCountInfo($spread_id){
        $order = Db::name('Order')->where('spread_id','=',$spread_id)->where('status','=',2)->where('is_count','=',1)->field('id,uid,money')->select();
        $uids = [];
        $money = 0;
        if($order){
            foreach ($order as $v){
                $uids[] = $v['uid'];
                $money += $v['money'];
            }
        }
        $uids = array_unique($uids);
        $count = count($uids);
        return ['charge'=>$money,'count'=>$count];
    }
    
    //获取订单统计信息
    public static function getOrderCount(){
        global $loginId;
        $type = aLogin::getCache('type');
        $key = ($type == 1) ? 'channel_id' : 'agent_id';
        $cur_time = strtotime('today');
        $today = $yesterday = $month = $total = $temp = [
            'n_pay' => 0,
            'n_notpay' => 0,
            'n_money' => 0,
            'n_user' => 0,
            'n_rate' => 0,
            'p_pay' => 0,
            'p_notpay' => 0,
            'p_money' => 0,
            'p_user' => 0,
            'p_rate' => 0,
            'total_money' => 0,
            'type1_money' => 0,
            'type2_money' => 0,
            'type3_money' => 0
        ];
        $today_list = Db::name('Order')->where($key,'=',$loginId)->where('is_count','=',1)->where('create_time','>=',$cur_time)->field('id,type,package,uid,status,money')->select();
        if($today_list){
            $n_uids = $p_uids = [];
            foreach($today_list as $v){
                if($v['status'] == 2){
                    if($v['package'] > 0){
                        $today['p_pay'] += 1;
                        $today['p_money'] += $v['money'];
                        if(!in_array($v['uid'], $p_uids)){
                            $today['p_user'] += 1;
                            $p_uids[] = $v['uid'];
                        }
                    }else{
                        $today['n_pay'] += 1;
                        $today['n_money'] += $v['money'];
                        if(!in_array($v['uid'], $n_uids)){
                            $today['n_user'] += 1;
                            $n_uids[] = $v['uid'];
                        }
                    }
                    $today['total_money'] += $v['money'];
                    $type_key = in_array($v['type'], [1,2,3]) ? 'type'.$v['type'].'_money' : 'type1_money';
                    $today[$type_key] += $v['money'];
                }else{
                    if($v['package'] > 0){
                        $today['p_notpay'] += 1;
                    }else{
                        $today['n_notpay'] += 1;
                    }
                }
            }
        }
        if($today['n_notpay'] || $today['n_pay']){
            $today['n_rate'] = (round($today['n_pay']/($today['n_pay']+$today['n_notpay']),2)*100);
        }
        if($today['p_notpay'] || $today['p_pay']){
            $today['p_rate'] = (round($today['p_pay']/($today['p_pay']+$today['p_notpay']),2)*100);
        }
        $start = $cur_time - 86400*30;
        $start_date = date('Y-m-d',$start);
        $select_date = date('Y-m-d',($cur_time-40*86400));
        $field = 'create_date,n_pay,n_notpay,n_money,n_user,n_rate,p_pay,p_notpay,p_money,p_user,p_rate,total_money,type1_money,type2_money,type3_money';
        $list = Db::name('TaskOrder')->where('channel_id','=',$loginId)->field($field)->select();
        $temps = $data = [];
        if($list){
            $yes_day = date('Y-m-d',strtotime('-1 day'));
            $month_day = date('Y-m').'-01';
            foreach ($list as $val){
                $temps[$val['create_date']] = $val;
                if($val['create_date'] === $yes_day){
                    $yesterday = $val;
                }
                if($val['create_date'] >= $month_day){
                    $month['n_pay'] += $val['n_pay'];
                    $month['n_notpay'] += $val['n_notpay'];
                    $month['n_user'] += $val['n_user'];
                    $month['n_money'] += $val['n_money'];
                    $month['p_pay'] += $val['p_pay'];
                    $month['p_notpay'] += $val['p_notpay'];
                    $month['p_user'] += $val['p_user'];
                    $month['p_money'] += $val['p_money'];
                    $month['total_money'] += $val['total_money'];
                    $month['type1_money'] += $val['type1_money'];
                    $month['type2_money'] += $val['type2_money'];
                    $month['type3_money'] += $val['type3_money'];
                }
                $total['n_pay'] += $val['n_pay'];
                $total['n_notpay'] += $val['n_notpay'];
                $total['n_user'] += $val['n_user'];
                $total['n_money'] += $val['n_money'];
                $total['p_pay'] += $val['p_pay'];
                $total['p_notpay'] += $val['p_notpay'];
                $total['p_user'] += $val['p_user'];
                $total['p_money'] += $val['p_money'];
                $total['total_money'] += $val['total_money'];
                $total['type1_money'] += $val['type1_money'];
                $total['type2_money'] += $val['type2_money'];
                $total['type3_money'] += $val['type3_money'];
            }
        }
        if($month['n_notpay'] || $month['n_pay']){
            $month['n_rate'] = (round($month['n_pay']/($month['n_pay']+$month['n_notpay']),2)*100);
        }
        if($month['p_notpay'] || $month['p_pay']){
            $month['p_rate'] = (round($month['p_pay']/($month['p_pay']+$month['p_notpay']),2)*100);
        }
        if($total['n_notpay'] || $total['n_pay']){
            $total['n_rate'] = (round($total['n_pay']/($total['n_pay']+$total['n_notpay']),2)*100);
        }
        if($total['p_notpay'] || $total['p_pay']){
            $total['p_rate'] = (round($total['p_pay']/($total['p_pay']+$total['p_notpay']),2)*100);
        }
        
        $cur_date = date('Y-m-d');
        while ($start_date < $cur_date){
            if(isset($temps[$start_date])){
                $data[] = $temps[$start_date];
            }else{
                $temp['create_date'] = $start_date;
                $data[] = $temp;
            }
            $start_date = date('Y-m-d',(strtotime($start_date)+86400));
        }
        foreach ($data as &$value){
            $value['n_svg'] = $value['p_svg'] = 0;
            if($value['n_user']){
                $value['n_svg'] = round($value['n_money']/$value['n_user'],2);
            }
            if($value['p_user']){
                $value['p_svg'] = round($value['p_money']/$value['p_user'],2);
            }
        }
        $data = array_reverse($data);
        $res = ['today'=>$today,'yesterday'=>$yesterday,'month'=>$month,'total'=>$total,'data'=>$data];
        return $res;
    }
    
    
    //获取书籍类型
    private static function getBookTypeName($type){
        $name = '未知';
        switch ($type){
            case 1:$name='漫画';break;
            case 2:$name='小说';break;
            case 3:$name='听书';break;
        }
        return $name;
    }
    
}