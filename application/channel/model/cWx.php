<?php
namespace app\channel\model;
use app\channel\model\Common;
use app\common\model\myValidate;
use app\common\model\myMaterial;
use think\Db;

class cWx extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'status' => ["require|in:1,2",["require"=>"请选择状态","in"=>"未指定该状态"]],
        'pid' =>  ["require|number",["require"=>"父节点参数错误",'number'=>'父节点参数错误']],
        'name' =>  ["require|max:12",["require"=>"请输入菜单名称",'max'=>'菜单名称最多支持12个字符']],
        'type' => ["require|in:0,1,2,3",["require"=>"请选择菜单点击事件","in"=>"未指定该点击事件"]],
        'program_id' => ["min:1",["min"=>"请输入小程序app_id"]],
        'value' => ["min:1",["max"=>"请输入菜单点击事件值"]],
        'event' => ["require|in:sortUp,sortDown,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']],
        'appid' => ['require',['require'=>'请输入公众号appid']],
        'appsecret' => ['require',['require'=>'请输入公众号secret']],
        'apptoken' => ['require',['require'=>'请输入公众号token']],
        'qrcode' => ['require',['require'=>'请上传公众号二维码图片']]
    ];
    
    public static $reply = [
        'id' =>  ["require|number|gt:0",["require"=>"自动回复主键参数错误",'number'=>'自动回复主键格式不规范',"gt"=>"自动回复主键格式不规范"]],
        'status' => ["require|in:1,2",["require"=>"请选择状态","in"=>"未指定该状态"]],
        'type' => ["require|in:1,2",["require"=>"请选择消息类型","in"=>"未指定该消息类型"]],
        'keyword' =>  ["require|max:20",["require"=>"请输入关键字",'max'=>'关键字最多支持20个字符']],
        'content' => ["requireIf:type,1|max:500",["requireIf"=>"请输入文本消息",'max'=>'回复内容长度超出限制']],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']],
    ];
    
    /**
     * 获取菜单列表
     * @param number $channel_id 渠道ID
     * @return array
     */
    public static function getMenuList($channel_id){
        $list = Db::name('WxMenu')->where('channel_id','=',$channel_id)->order('sort_num','desc')->select();
        return $list;
    }
    
    //处理菜单排序
    public static function doMenuSort($data){
        $flag = false;
        $cur = Db::name('WxMenu')->where('id','=',$data['id'])->field('id,channel_id,pid,sort_num')->find();
        if($cur){
            switch ($data['event']){
                case 'sortUp':
                	$where = [['sort_num','>',$cur['sort_num']],['pid','=',$cur['pid']],['channel_id','=',$cur['channel_id']]];
                    $other = Db::name('WxMenu')->where($where)->field('id,sort_num')->order('sort_num','ASC')->find();
                    break;
                case 'sortDown':
                	$where = [['sort_num','<',$cur['sort_num']],['pid','=',$cur['pid']],['channel_id','=',$cur['channel_id']]];
                    $other = Db::name('WxMenu')->where($where)->field('id,sort_num')->order('sort_num','DESC')->find();
                    break;
            }
            if($other){
                Db::startTrans();
                $re = Db::name('WxMenu')->where('id','=',$cur['id'])->setField('sort_num',$other['sort_num']);
                if($re){
                    $res = Db::name('WxMenu')->where('id','=',$other['id'])->setField('sort_num',$cur['sort_num']);
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
    
    //查询添加菜单数量
    public static function getMenuCount($pid){
        global $loginId;
        $count = Db::name('WxMenu')->where('channel_id','=',$loginId)->where('pid','=',$pid)->count();
        return $count;
    }
    
    //处理更新菜单
    public static function doneMenu($field){
        global $loginId;
        $data = myValidate::getData(self::$rules,$field);
        $data['channel_id'] = $loginId;
        $content = ['value'=>$data['value'],'appid'=>''];
        if($data['pid'] > 0 && $data['type'] == 0){
            res_return('子菜单必须选择点击事件');
        }
        if($data['type'] > 0){
            if(!$data['value']){
                res_return('请输入菜单事件值');
            }
            if($data['type'] == 2){
                if(!$data['program_id']){
                    res_return('请输入小程序app_id');
                }
                $content['appid'] = $data['program_id'];
            }
        }
        unset($data['value']);
        unset($data['program_id']);
        $data['content'] = json_encode($content);
        if(array_key_exists('id', $data)){
            $flag = parent::saveIdData('WxMenu',$data);
        }else{
            $count = self::getMenuCount($data['pid']);
            if($data['pid'] > 0){
                if($count >= 5){
                    res_return('最多允许添加5个子菜单');
                }
            }else{
                if($count >= 3){
                    res_return('最多允许添加3个顶级菜单');
                }
            }
            Db::startTrans();
            $flag = false;
            $re = Db::name('WxMenu')->insertGetId($data);
            if($re){
                $res = Db::name('WxMenu')->where('id','=',$re)->setField('sort_num',$re);
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
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    
    //添加菜单
    public static function addMenu($data){
        Db::startTrans();
        $flag = false;
        $re = Db::name('WxMenu')->insertGetId($data);
        if($re){
            $res = Db::name('WxMenu')->where('id','=',$re)->setField('sort_num',$re);
            if($res !== false){
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
    
    //创建默认菜单
    public static function createDefaultMenu(){
    	$cur = cLogin::getCache();
    	if(!$cur['url']){
    		res_return('尚未配置站点url');
    	}
    	$url = $cur['is_location'] == 1 && $cur['location_url'] ? $cur['location_url'] : $cur['url'];
    	$data = [
    			['name'=>'签到','channel_id'=>$cur['id'],'pid'=>0,'type'=>3,'content'=>'{"appid": "", "value": "sign"}'],
    			['name'=>'进入书城','channel_id'=>$cur['id'],'pid'=>0,'type'=>0,'content'=>'{"appid": "", "value": ""}','child'=>[
    					['name'=>'书城首页','channel_id'=>$cur['id'],'pid'=>0,'type'=>1,'content'=>'{"appid": "", "value": "http://'.$url.'/index/Book/novel.html"}'],
    					['name'=>'个人中心','channel_id'=>$cur['id'],'pid'=>0,'type'=>1,'content'=>'{"appid": "", "value": "http://'.$url.'/index/User/index.html"}'],
    					['name'=>'精品推荐','channel_id'=>$cur['id'],'pid'=>0,'type'=>1,'content'=>'{"appid": "", "value": "http://'.$url.'/index/Book/more.html?type=2&is_hot=1"}'],
    					['name'=>'我要充值','channel_id'=>$cur['id'],'pid'=>0,'type'=>1,'content'=>'{"appid": "", "value": "http://'.$url.'/index/Charge/index.html"}'],
    					['name'=>'联系客服','channel_id'=>$cur['id'],'pid'=>0,'type'=>3,'content'=>'{"appid": "", "value": "lianxikefu"}'],
    			]],
    			['name'=>'阅读记录','channel_id'=>$cur['id'],'pid'=>0,'type'=>1,'content'=>'{"appid": "", "value": "http://'.$url.'/index/User/myHistory.html"}'],
    	];
    	Db::startTrans();
    	$flag = false;
    	$re = Db::name('WxMenu')->where('channel_id','=',$cur['id'])->delete();
    	if($re !== false){
    		foreach ($data as $v){
    			$child = '';
    			$result = false;
    			if(isset($v['child'])){
    				$child = $v['child'];
    				unset($v['child']);
    			}
    			$re = Db::name('WxMenu')->insertGetId($v);
    			if($re){
    				$res = Db::name('WxMenu')->where('id','=',$re)->setField('sort_num',$re);
    				if($res !== false){
    					$result = true;
    				}
    			}
    			if($result){
    				if($child){
    					$result = false;
    					foreach ($child as $val){
    						$val['pid'] = $re;
    						$re1 = Db::name('WxMenu')->insertGetId($val);
    						if($re1){
    							$res1 = Db::name('WxMenu')->where('id','=',$re1)->setField('sort_num',$re1);
    							if($res1 !== false){
    								$result = true;
    							}
    						}
    					}
    					if(!$result){
    						break;
    					}
    				}
    			}else{
    				break;
    			}
    		}
    		if($result){
    			$flag = true;
    		}
    	}
    	if($flag){
    		Db::commit();
    		res_return();
    	}else{
    		Db::rollback();
    		res_return('设置默认菜单失败');
    	}
    }
    
    //删除节点
    public static function deleteMenu($id){
        $flag = false;
        $re = Db::name('WxMenu')->where('id','=',$id)->whereOr('pid','=',$id)->delete();
        if($re){
            $flag = true;
        }
        return $flag;
    }
    
    //处理更新关键字回复
    public static function doneReply($field){
        global $loginId;
        $data = myValidate::getData(self::$reply,$field);
        $data['channel_id'] = $loginId;
        if($data['type'] == 2){
            $data['content'] = '';
            $material = myMaterial::getCustomMsg();
            $data['material'] = json_encode($material,JSON_UNESCAPED_UNICODE);
        }else{
            $data['material'] = '{}';
        }
        $where = [['keyword','=',$data['keyword']],['channel_id','=',$loginId]];
        if(isset($data['id'])){
        	$where[] = ['id','<>',$data['id']];
        }
        $repeat = parent::getCur('WxReply', $where,'id,keyword');
        if($repeat){
        	res_return('该关键字已存在');
        }
        if(array_key_exists('id', $data)){
            $re = parent::saveIdData('WxReply',$data);
        }else{
            $re = parent::add('WxReply', $data);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //处理更新关键字回复
    public static function doneSpecial($field){
        global $loginId;
        $data = myValidate::getData(self::$reply,$field);
        $data['channel_id'] = $loginId;
        if($data['type'] == 2){
            $data['content'] = '';
            $material = myMaterial::getGraphicMessageData();
            $data['material'] = json_encode($material,JSON_UNESCAPED_UNICODE);
        }else{
            $data['material'] = '{}';
        }
        $where = [['keyword','=',$data['keyword']],['channel_id','=',$loginId]];
        if(isset($data['id'])){
        	$where[] = ['id','<>',$data['id']];
        }
        $repeat = parent::getCur('WxSpecial', $where,'id,keyword');
        if($repeat){
        	res_return('该关键字已存在');
        }
        if(array_key_exists('id', $data)){
            $re = parent::saveIdData('WxSpecial',$data);
        }else{
            $re = parent::add('WxSpecial', $data);
        }
        if($re){
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //获取更新自动回复选项
    public static function getReplyOption(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [
                    ['val'=>1,'text'=>'启用','default'=>1],
                    ['val'=>2,'text'=>'禁用','default'=>0]
                ]
            ],
            'type' => [
                'name' => 'type',
                'option' => [
                    ['val'=>1,'text'=>'文本','default'=>1],
                    ['val'=>2,'text'=>'图文','default'=>0]
                ]
            ]
        ];
        return $option;
    }
    //获取更新菜单选项
    public static function getMenuOption(){
        $option = [
            'type' => [
                'name' => 'type',
                'option' => [
                    ['val'=>0,'text'=>'无事件','default'=>1],
                    ['val'=>1,'text'=>'跳转网页','default'=>0],
                    ['val'=>2,'text'=>'跳转小程序','default'=>0],
                    ['val'=>3,'text'=>'点击推事件','default'=>0],
                ]
            ],
            'backUrl' => my_url('menu')
        ];
        return $option;
    }
    
    //获取菜单类型名称
    public static function getMenuTypeName($type){
        $name = '未知';
        switch ($type){
            case 0:$name='无事件';break;
            case 1:$name='跳转网页';break;
            case 2:$name='跳转小程序';break;
            case 3:$name='点击推事件';break;
        }
        return $name;
    }
}