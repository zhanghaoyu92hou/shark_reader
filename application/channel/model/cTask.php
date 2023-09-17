<?php
namespace app\channel\model;
use app\channel\model\Common;
use app\common\model\myValidate;
use app\common\model\myMaterial;

class cTask extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'book_id' =>  ["number|gt:0",['number'=>'书籍参数错误',"gt"=>"书籍参数不规范"]],
        'video_id' =>  ["number|gt:0",['number'=>'视频参数错误',"gt"=>"视频参数不规范"]],
        'activity_id' =>  ["number|gt:0",['number'=>'活动参数错误',"gt"=>"视频参数不规范"]],
        'name' => ['require|max:100',['require'=>'请输入任务名称','max'=>'任务名称字数超出限制']],
        'is_all' => ["require|in:1,2",["require"=>"请选择接收用户群体","in"=>"接收用户群体参数不规范"]],
        'sex' => ["require|in:-1,0,1,2",["require"=>"请选择用户性别","in"=>"用户性别参数不规范"]],
        'is_charge' => ["require|in:-1,1,2",["require"=>"请选择用户充值情况","in"=>"用户充值情况参数不规范"]],
        'money' => ["require|in:-1,1,2,3",["require"=>"请选择用户书币余额","in"=>"用户书币余额参数不规范"]],
        'subscribe_time' => ["require|in:-1,1,2,3,4,5,6",["require"=>"请选择用户关注时间","in"=>"用户关注时间参数不规范"]],
        'send_time' => ["require|date",["require"=>"请选择消息发送时间","in"=>"消息发送时间不规范"]],
        'test_id' =>  ["require|number|gt:0",["require"=>"预览粉丝参数错误",'number'=>'预览粉丝参数错误',"gt"=>"预览粉丝参数错误"]],
    ];
    
    //更新客服消息
    public static function doneTask($field){
        $data = myValidate::getData(self::$rules, $field);
        if(array_key_exists('id', $data)){
            $cur = parent::getById('Task', $data['id'],'id,status');
            if(!$cur){
                res_return('客服消息异常');
            }
            if($cur['status'] != 2){
                res_return('已发送消息禁止编辑');
            }
        }
        $material = myMaterial::getCustomMsg();
        if(count($material) > 1){
            res_return('客服消息仅支持一条图文消息');
        }
        global $loginId;
        $task = [
            'name' => $data['name'],
            'channel_id' => $loginId,
            'material' => json_encode($material,JSON_UNESCAPED_UNICODE),
            'send_time' => strtotime($data['send_time']),
            'is_all' => $data['is_all']
        ];
        if($data['is_all'] == 2){
            $info = self::getSendWhere($loginId);
            $where = $info['where'];
            $condition = $info['data'];
        }else{
            $where = [
                ['wx_id','=',$loginId],
                ['subscribe','=',1]
            ];
            $condition = parent::buildArr('sex:-1,is_charge:-1,money:-1,subscribe_time:-1');
        }
        $task['where'] = json_encode($where);
        $task['condition'] = json_encode($condition);
        if(array_key_exists('id', $data)){
            $re = parent::save('Task',[['id','=',$data['id']]], $task);
        }else{
            $task['create_time'] = time();
            $data['channel_id'] = $loginId;
            $re = parent::add('Task', $task);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败');
        }
    }
    
    //构建查询条件
    public static function getSendWhere($channel_id=0){
        $field = 'sex,is_charge,money,subscribe_time';
        $data = myValidate::getData(self::$rules, $field);
        $where = [
            ['wx_id','=',$channel_id],
            ['subscribe','=',1]
        ];
        if($data['sex'] != -1){
            $where[] = ['sex','=',$data['sex']];
        }
        if($data['is_charge'] != -1){
            $where[] = ['is_charge','=',$data['is_charge']];
        }
        if($data['money'] != -1){
            switch ($data['money']){
                case 1:
                    $where[] = ['money','<',500];
                    break;
                case 2:
                    $where[] = ['money','<',2000];
                    break;
                case 3:
                    $where[] = ['money','<',5000];
                    break;
            }
        }
        if($data['subscribe_time'] != -1){
            $daylong = 86400;
            $cur_time = time();
            switch ($data['subscribe_time']){
                case 1:
                    $where[] = ['subscribe_time','>',($cur_time-$daylong)];
                    break;
                case 2:
                    $where[] = ['subscribe_time','>',($cur_time-$daylong*7)];
                    break;
                case 3:
                    $where[] = ['subscribe_time','>',($cur_time-$daylong*15)];
                    break;
                case 4:
                    $where[] = ['subscribe_time','>',($cur_time-$daylong*30)];
                    break;
                case 5:
                    $where[] = ['subscribe_time','>',($cur_time-$daylong*90)];
                    break;
                case 6:
                    $where[] = ['subscribe_time','<',($cur_time-$daylong*90)];
                    break;
            }
        }
        return ['data'=>$data,'where'=>$where];
    }
    
    //获取筛选条件
    public static function getWhereOption(){
        $option = [
            'is_all' =>  [
                'name' => 'is_all',
                'option' => [
                    ['val'=>1,'text'=>'全部用户','default'=>1],
                    ['val'=>2,'text'=>'条件筛选','default'=>0]
                ]
            ],
            'sex' => [
                'name' => 'sex',
                'option' => [
                    ['val'=>-1,'text'=>'不限','default'=>1],
                    ['val'=>1,'text'=>'男','default'=>0],
                    ['val'=>2,'text'=>'女','default'=>0],
                    ['val'=>0,'text'=>'未知','default'=>0]
                ]
            ],
            'is_charge' => [
                'name' => 'is_charge',
                'option' => [
                    ['val'=>-1,'text'=>'不限','default'=>1],
                    ['val'=>1,'text'=>'已充值用户','default'=>0],
                    ['val'=>2,'text'=>'未充值用户','default'=>0]
                ]
            ],
            'money' => [
                'name' => 'money',
                'option' => [
                    ['val'=>-1,'text'=>'不限','default'=>1],
                    ['val'=>1,'text'=>'低于500','default'=>0],
                    ['val'=>2,'text'=>'低于2000','default'=>0],
                    ['val'=>3,'text'=>'低于5000','default'=>0]
                ]
            ],
            'subscribe_time' => [
                'name' => 'subscribe_time',
                'option' => [
                    ['val'=>-1,'text'=>'不限','default'=>1],
                    ['val'=>1,'text'=>'一天内','default'=>0],
                    ['val'=>2,'text'=>'一周内','default'=>0],
                    ['val'=>3,'text'=>'半月内','default'=>0],
                    ['val'=>4,'text'=>'一个月内','default'=>0],
                    ['val'=>5,'text'=>'三个月内','default'=>0],
                    ['val'=>6,'text'=>'更早','default'=>0]
                ]
            ]
        ];
        return $option;
    }
    
}