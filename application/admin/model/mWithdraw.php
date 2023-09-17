<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
class mWithdraw extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'remark' => ["requireIf:event,fail",["requireIf"=>'请输入不通过原因']],
        'event' => ["require|in:pass,fail",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    //获取提现结算列表
    public static function getWithdrawList($where,$pages){
        $list = Db::name('Withdraw a')
        ->join('Channel b','a.channel_id=b.id','left')
        ->where($where)
        ->field('a.*,b.name as channel_name')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['status_name'] = mWithdraw::getStatusName($v['status']);
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
        $cur = Db::name('Withdraw')->where('id','=',$data['id'])->field('id,channel_id,money,status')->find();
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