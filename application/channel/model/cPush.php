<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;

class cPush extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'type' => ["require|in:1,2,3,4",["require"=>"请选择推送消息类型","in"=>"未指定该推送消息类型"]],
        'event' => ["require|in:on,off",["require"=>"请选择推送消息状态","in"=>"未指定该推送消息状态"]],
        'name' =>  ["require|max:200",["require"=>"请输入图文标题",'max'=>'图文标题最多支持200个字符']],
        'cover' =>  ['require|max:255',['require'=>'请上传图文封面','max'=>'封面参数异常']],
        'url' =>  ['require|max:255',['require'=>'请输入跳转链接','max'=>'跳转链接参数错误']],
        'desc' =>  ["max:500",['max'=>'图文简介最多支持500个字符']],
    ];
    
    //获取推送消息详情
    public static function getPushInfo(){
        $temp = [
            'type1' => ['status'=>2,'content'=>'','dourl'=>my_url('doPush',['type'=>1])],
            'type2' => ['status'=>2,'content'=>''],
            'type3' => ['status'=>2,'content'=>''],
            'type4' => ['status'=>2,'content'=>'']
        ];
        global $loginId;
        $list = Db::name('TaskMessage')->where('channel_id','=',$loginId)->select();
        if($list){
            foreach ($list as $v){
                $key = 'type'.$v['type'];
                if(!isset($temp[$key])){
                    continue;
                }
                $temp[$key]['status'] = $v['status'];
                if($v['material']){
                    $material = json_decode($v['material'],true);
                    $temp[$key]['content'] = $material[0];
                }
            }
        }
        return $temp;
    }
    
    //更新推送消息
    public static function donePushEvent($data){
        global $loginId;
        $where = [['type','=',$data['type']],['channel_id','=',$loginId]];
        $cur = Db::name('TaskMessage')->where($where)->field('id,status')->find();
        if($cur){
            $status = $data['event'] === 'on' ? 1 : 2;
            $flag = parent::setField('TaskMessage',[['id','=',$cur['id']]],'status', $status);
        }else{
            if($data['event'] == 'on'){
                if($data['type'] == 1){
                    res_return('您尚未配置首冲消息内容');
                }
                $insert = [
                    'type' => $data['type'],
                    'status' => 1,
                    'channel_id' => $loginId
                ];
                $flag = parent::add('TaskMessage', $insert);
            }else{
                $flag = true;
            }
        }
        return $flag;
    }
    
    
    
}