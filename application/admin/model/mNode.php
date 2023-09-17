<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
use app\common\model\myValidate;
class mNode extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'pid' =>  ["require|number",["require"=>"父节点参数错误",'number'=>'父节点参数错误']],
        'name' =>  ["require|max:12",["require"=>"请输入节点名称",'max'=>'节点名称最多支持12个字符']],
        'url' => ["max:100",["max"=>"链接长度超出限制"]],
        'is_menu' => ["require|in:1,2",["require"=>"请选择节点类型","in"=>"未指定该节点类型"]],
        'icon' => ["max:40",["max"=>"节点图片长度超出限制"]],
        'child_nodes' => ["max:500",["max"=>"附属节点长度超出限制"]],
        'event' => ["require|in:sortUp,sortDown,delete,on,off",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']]
    ];
    
    /**
     * 获取节点列表
     * @param number $type 1总站，2渠道，3代理
     * @return array
     */
    public static function getNodeList($type){
        $list = Db::name('Nodes')->where('type','=',$type)->where('status','=',1)->order('sort_num','desc')->select();
        return $list;
    }
    
    //获取渠道菜单列表
    public static function getChannelNode($type){
        $list = Db::name('Nodes')->where('type','=',$type)->where('status','between',[1,2])->order('sort_num','desc')->select();
        return $list;
    }
    
    //获取树形插件
    public static function getOptionList(){
        $list = Db::name('Nodes')->where('type','=',1)->where('status','=',1)->field('id,pid,name as label')->order('sort_num','desc')->select();
        return $list;
    }
    
    //批量更新节点层级关系
    public static function updateAll($type){
        $list = self::getList($type);
        if($list){
            foreach ($list as $v){
                $pids = self::getPids($v['pid']);
                if($pids){
                    $level = count(explode(',', trim($pids,',')))+1;
                    $val = ','.$pids;
                    $data = [
                        'pids' => $val,
                        'level' => $level
                    ];
                    Db::name('Nodes')->where('id','=',$v['id'])->update($data);
                }
            }
        }
    }
    
    //获取当前节点的父节点集
    public static function getPids($pid){
        $str = '';
        if($pid > 0){
            $cur = Db::name('Nodes')->where('id','=',$pid)->field('id,pid')->find();
            $str .= $cur['id'].',';
            if($cur['pid'] > 0){
                $str .= self::getPids($cur['pid']);
            }
        }
        return $str;
    }
    
    //更新节点
    public static function doneNodes($field,$type=1){
        $data = myValidate::getData(self::$rules,$field);
        $data['type'] = $type;
        if(isset($data['child_nodes'])){
            $data['all_nodes'] = mNode::getAllNodes($data['url'], $data['child_nodes']);
        }
        $pids = mNode::getPids($data['pid']);
        $level = 1;
        if($pids){
            $pids = ','.$pids;
            $level = count(explode(',', trim($pids,',')))+1;
        }
        $data['pids'] = $pids;
        $data['level'] = $level;
        if(array_key_exists('id', $data)){
            $flag = parent::saveIdData('Nodes', $data);
        }else{
            $data['create_time'] = time();
            Db::startTrans();
            $flag = false;
            $re = Db::name('Nodes')->insertGetId($data);
            if($re){
                $res = Db::name('Nodes')->where('id','=',$re)->setField('sort_num',$re);
                if($res !== false){
                    $flag = true;
                }
            }
            if($flag){
                Db::commit();
            }else{
                Db::rollback();
            }
        }
        if($flag){
        	switch ($type){
        		case 1:
        			mLogin::clearNode();
        			break;
        		case 2:
        			cache('CHANNEL_LOGIN_MENU',null);
        			break;
        		case 3:
        			cache('AGENT_LOGIN_MENU',null);
        			break;
        	}
        	
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    //处理节点排序
    public static function doNodeSort($data){
        $flag = false;
        $cur = Db::name('Nodes')->where('id','=',$data['id'])->field('id,pid,type,level,sort_num')->find();
        if($cur){
            switch ($data['event']){
                case 'sortUp':
                    $where = [['sort_num','>',$cur['sort_num']],['pid','=',$cur['pid']],['type','=',$cur['type']],['level','=',$cur['level']]];
                    $other = Db::name('Nodes')->where($where)->field('id,sort_num')->order('sort_num','ASC')->find();
                    break;
                case 'sortDown':
                    $where = [['sort_num','<',$cur['sort_num']],['pid','=',$cur['pid']],['type','=',$cur['type']],['level','=',$cur['level']]];
                    $other = Db::name('Nodes')->where($where)->field('id,sort_num')->order('sort_num','DESC')->find();
                    break;
            }
            if($other){
                Db::startTrans();
                $re = Db::name('Nodes')->where('id','=',$cur['id'])->setField('sort_num',$other['sort_num']);
                if($re){
                    $res = Db::name('Nodes')->where('id','=',$other['id'])->setField('sort_num',$cur['sort_num']);
                    if($res){
                        $flag = true;
                    }
                }
                if($flag){
                    Db::commit();
                }else{
                    Db::rollback();
                }
            }
        }
        return $flag;
    }
    
    //获取该节点权限集
    public static function getAllNodes($url,$child_node){
        $all_nodes = '';
        if($url){
            if(!stripos($url,'http')){
                $arr = explode('/', $url);
                if(count($arr) === 2){
                    $all_nodes .= $arr[0].':'.$arr[1];
                }else{
                    $this->error('链接地址格式错误');
                }
            }
        }
        if($child_node){
            $all_nodes = $all_nodes ? $all_nodes.','.$child_node : $child_node;
        }
        if($all_nodes){
            $all_nodes = strtolower($all_nodes);
        }
        return $all_nodes;
    }
    
    //删除节点
    public static function deleteNodes($id){
        $flag = false;
        $re = Db::name('Nodes')->where('id','=',$id)->whereOr('pids','like','%,'.$id.',%')->setField('status',3);
        if($re){
            $flag = true;
        }
        return $flag;
    }
    
    //获取节点选项
    public static function getOptions(){
        $option = [
            'is_menu' => [
                'name' => 'is_menu',
                'option' => [
                    ['val'=>1,'text'=>'菜单','default'=>1],
                    ['val'=>2,'text'=>'方法','default'=>0]
                ]
            ]
        ];
        return $option;
    }
}