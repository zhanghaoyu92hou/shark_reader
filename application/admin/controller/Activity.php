<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\admin\model\mConfig;
use app\admin\model\mActivity;

class Activity extends Common{
    
    //活动列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name',
                'rules' => ['status'=>'in:1,2']
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mActivity::getActivityList($where,$pages);
            $time = time();
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '禁用';
                    $v['customer_url'] = '';
                    if($time > $v['end_time']){
                        $v['between_time'] = '<font class="text-red">活动已结束</font>';
                    }elseif ($time < $v['start_time']){
                        $v['between_time'] = '<font class="text-red">活动未开始</font>';
                    }else{
                        $v['between_time'] = date('Y-m-d H:i',$v['start_time']).'~'.date('Y-m-d H:i',$v['end_time']);
                        $v['customer_url'] = my_url('Message/addTask',['activity_id'=>$v['id']]);
                    }
                    $v['content'] = '充'.$v['money'].'元送'.$v['send_money'].'书币';
                    $v['do_url'] = my_url('doActivity',['id'=>$v['id']]);
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['first_str'] = $v['is_first'] == 1 ? '仅限一次' : '不限次数'; 
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('活动');
        $cur = mActivity::getById('Activity',$id,'id,name');
        if(!$cur){
            res_return('活动参数错误');
        }
        $config = mConfig::getConfig('website');
        if(!array_key_exists('url', $config) || !$config['url']){
            res_return('您尚未配置站点url');
        }
        $url = 'http://';
        if($config['is_location'] == 1 && $config['location_url']){
            $url .= $config['location_url'];
        }else{
            $url .= $config['url'];
        }
        $short_url = '/Index/Activity/index.html?activity_id='.$id;
        $data = [
            'notice' => '温馨提示 : 相对链接只能应用到页面跳转链接中，如轮播图链接等，渠道用户点击后不会跳转到总站',
            'links' => [
                ['title'=>'相对链接','val'=>$short_url],
                ['title'=>'绝对链接','val'=>$url.$short_url]
            ]
        ];
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
    //新增活动
    public function addActivity(){
        if($this->request->isAjax()){
            $field = 'name,money,send_money,status,is_first,start_time,end_time,bg,cover';
            $data = myValidate::getData(mActivity::$rules, $field);
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            if($data['start_time'] >= $data['end_time']){
                res_return('开始时间不能大于结束时间');
            }
            $data['create_time'] = time();
            $re = mActivity::add('Activity', $data);
            if($re){
            	cache('near_activity',null);
                res_return();
            }else{
                res_return('新增失败，请重试');
            }
        }else{
            $field = 'id,name,money,send_money,status,is_first,start_time,end_time,bg,cover';
            $option = mActivity::getActivityRadioList();
            $option['cur'] = mActivity::buildArr($field);
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('doActivity');
        }
    }
    
    //编辑活动
    public function doActivity(){
        if($this->request->isAjax()){
            $field = 'id,name,money,send_money,status,is_first,start_time,end_time,bg,cover';
            $data = myValidate::getData(mActivity::$rules, $field);
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            if($data['start_time'] >= $data['end_time']){
                res_return('开始时间不能大于结束时间');
            }
            $re = mActivity::saveIdData('Activity', $data);
            if($re){
            	cache('near_activity',null);
            	cache('activity_'.$data['id'],null);
                res_return();
            }else{
                res_return('编辑失败，请重试');
            }
        }else{
            $id = myRequest::getId('活动');
            $cur = mActivity::getById('Activity',$id);
            if(!$cur){
                res_return('活动不存在');
            }
            $cur['start_time'] = date('Y-m-d H:i:s',$cur['start_time']);
            $cur['end_time'] = date('Y-m-d H:i:s',$cur['end_time']);
            $option = mActivity::getActivityRadioList();
            $option['cur'] = $cur;
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('doActivity');
        }
    }
    
    //处理活动事件
    public function doActivityEvent(){
        $field = 'id,event';
        $data = myValidate::getData(mActivity::$rules,$field);
        switch ($data['event']){
            case 'on':
                $status = 1;
                break;
            case 'off':
                $status = 2;
                break;
            case 'delete':
                $status = 3;
                break;
            default:
                res_return('未指定该事件');
                break;
        }
        $re = mActivity::setField('Activity', [['id','=',$data['id']]], 'status', $status);
        if($re){
        	cache('activity_'.$data['id'],null);
        	cache('near_activity',null);
            res_return();
        }else{
            res_return('操作失败');
        }
    }
    
}