<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
class mMember extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'event' => ["require|in:charge,vipon,vipoff,statuson,statusoff",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']],
        'money' =>  ["require|integer",["require"=>"请输入要调整的书币数",'integer'=>'书币格式错误']],
        'month' =>  ["require|number|gt:0",["require"=>"请输入vip充值月数",'number'=>'vip月数必须为大于0的数值',"gt"=>"vip月数必须为大于0的数值"]]
    ];
    
    /**
     * 获取用户列表
     * @param array $where 查询条件
     * @param array $pages 分页参数
     * @return array
     */
    public static function getMemberList($where,$pages){
        $field = 'a.*,b.name as channel_name,c.name as agent_name';
        $list = Db::name('Member a')
        ->join('Channel b','a.channel_id=b.id','left')
        ->join('Channel c','a.agent_id=c.id','left')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $v['info_url'] = my_url('Member/info',['id'=>$v['id']]);
                $v['status_name'] = ($v['status'] == 1) ? '正常' : '禁用';
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']);
                $v['is_subscribe'] = $v['subscribe'] > 0 ? '已关注' : '未关注';
                $v['phone'] = $v['phone'] ? $v['phone'] : '未绑定';
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
                $v['vip_str'] = 'N/A';
                if($v['viptime'] > 0){
                	if($v['viptime'] == 1){
                		$v['vip_str'] = '终身';
                	}else{
                		$v['vip_str'] = '到期时间:'.date('Y-m-d',$v['viptime']);
                	}
                }
            }
            $count = Db::name('Member a')->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //获取用户累计信息
    public static function getMemberCountMsg($uid){
        $chargeMoney = Db::name('Order')->where('uid','=',$uid)->where('status','=',2)->sum('money');
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