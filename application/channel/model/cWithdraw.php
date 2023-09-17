<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;
class cWithdraw extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'remark' => ["requireIf:event,fail",["requireIf"=>'请输入不通过原因']],
        'event' => ["require|in:pass,fail",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //获取代理提现结算列表
    public static function getAgentList($where,$pages){
        $list = Db::name('Withdraw a')
        ->join('Channel b','a.channel_id=b.id','left')
        ->where($where)
        ->field('a.*,b.name as agent_name')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['status_name'] = self::getStatusName($v['status']);
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
            }
            $count = Db::name('Withdraw a')
            ->join('Channel b','a.channel_id=b.id','left')
            ->where($where)
            ->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //处理结算
    public static function doWithdraw($data){
        $cur = parent::getById('Withdraw',$data['id'],'id,channel_id,money,status');
        if(!$cur){
            res_return('结算信息异常，请重试');
        }
        if($cur['status'] != 0){
            res_return('结算状态异常');
        }
        switch ($data['event']){
            case 'pass':
                $flag = Db::name('Withdraw')->where('id','=',$data['id'])->setField('status',1);
                break;
            case 'fail':
                Db::startTrans();
                $flag = false;
                $save = [
                    'status' => 2,
                    'remark' => $data['remark']
                ];
                $re = Db::name('Withdraw')->where('id','=',$data['id'])->update($save);
                if($re){
                    $res = Db::name('Channel')->where('id','=',$cur['channel_id'])->setInc('money',$cur['money']);
                    if($res){
                        $flag = true;
                    }
                }
                if($flag){
                    Db::commit();
                }else{
                    Db::rollback();
                }
                break;
        }
        return $flag;
    }
    
    //获取结算概要信息
    public static function getCountData(){
        global $loginId;
        $data = [
            'channel_money' => 0,
            'channel_done' => 0,
            'channel_wait' => 0,
            'agent_money' => 0,
            'agent_done' => 0,
            'agent_wait' => 0
        ];
        $cur = parent::getById('Channel',$loginId,'id,money');
        if(!$cur){
            res_return('当前渠道信息异常');
        }
        $data['channel_money'] += $cur['money'];
        $channel = Db::name('Withdraw')->where('channel_id','=',$loginId)->where('status','between',[0,1])->field('status,sum(money) as money')->group('status')->select();
        foreach ($channel as $v){
            if($v['status'] == 1){
                $data['channel_done'] += $v['money'];
            }else{
                $data['channel_wait'] += $v['money'];
            }
        }
        $agent = Db::name('Withdraw')->where('to_channel_id','=',$loginId)->where('status','between',[0,1])->field('status,sum(money) as money')->group('status')->select();
        foreach ($agent as $val){
            if($val['status'] == 1){
                $data['agent_done'] += $val['money'];
            }else{
                $data['agent_wait'] += $val['money'];
            }
        }
        $agent_money = Db::name('Channel')->where('parent_id','=',$loginId)->sum('money');
        $data['agent_money'] += $agent_money;
        return $data;
    }
    
    //处理一键提现
    public static function doneAll(){
        global $loginId;
        $cur = parent::getById('Channel', $loginId,'id,money,bank_user,bank_name,bank_no');
        if(!$cur){
            res_return('登录信息异常');
        }
        if($cur['money'] <= 0){
            res_return('您暂无可提现余额');
        }
        $data = [
            'channel_id' => $loginId,
            'to_channel_id' => 0,
            'money' => $cur['money'],
            'status' => 0,
            'bank_user' => $cur['bank_user'],
            'bank_name' => $cur['bank_name'],
            'bank_no' => $cur['bank_no'],
            'create_time' => time()
        ];
        Db::startTrans();
        $flag = false;
        $re = Db::name('Channel')->where('id','=',$loginId)->setDec('money',$cur['money']);
        if($re){
            $res = Db::name('Withdraw')->insert($data);
            if($res){
                $flag = true;
            }
        }
        if($flag){
            Db::commit();
        }else{
            Db::rollback();
        }
        return $flag;
    }
    
    /**
     * 获取代理状态名称
     * @param number $status 状态值
     * @return string
     */
    public static function getStatusName($status){
        $name = '未知';
        switch ($status){
            case 0:
                $name = '待审核';
                break;
            case 1:
                $name = '已结算';
                break;
            case 2:
                $name = '审核不通过';
                break;
        }
        return $name;
    }
}