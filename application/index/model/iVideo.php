<?php
namespace app\index\model;
use app\index\model\Common;
use think\Db;
use app\common\model\myRequest;
use app\common\model\myCache;
class iVideo extends Common{
	
	//获取书籍分类列表
	public static function getCategoryList($where,$page){
		$field = 'id,name,cover,category,summary,hot_num,free_type';
		$list = Db::name('Video')
		->where($where)
		->field($field)
		->page($page,10)
		->order('id','DESC')
		->select();
		if($list){
			foreach ($list as &$v){
				$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
				$v['summary'] = $v['summary'] ? : '暂无简介';
				$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
				$v['category'] = !empty($category) ? $category : '';
				$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
				$v['info_url'] = my_url('info',['video_id'=>$v['id']]);
			}
		}
		return $list;
	}
	
	//获取视频分类选项
	public static function getCategoryOption(){
		$get = myRequest::get('category,free_type');
		$cateConfig = myCache::getBookConfigCache('video_category');
		$temp = [['name'=>'全部','val'=>'','is_check'=>1]];
		if($cateConfig){
			$is_check = false;
			if($get['category'] && in_array($get['category'], $cateConfig)){
				$is_check = true;
				$temp = [['name'=>'全部','val'=>'','is_check'=>0]];
			}
			foreach ($cateConfig as $v){
				if($is_check && $get['category'] === $v){
					$temp[] = ['name'=>$v,'val'=>$v,'is_check'=>1];
				}else{
					$temp[] = ['name'=>$v,'val'=>$v,'is_check'=>0];
				}
			}
		}
		$free_type = [
			['name'=>'全部','val'=>'','is_check'=>1],
			['name'=>'免费','val'=>'1','is_check'=>0],
			['name'=>'收费','val'=>'2','is_check'=>0]
		];
		if($get['free_type'] && in_array($get['free_type'],[1,2])){
			foreach ($free_type as &$fv){
				if($get['free_type'] == $fv['val']){
					$fv['is_check'] = 1;
				}else{
					$fv['is_check'] = 0;
				}
			}
		}
		return ['category'=>$temp,'free_type'=>$free_type];
	}
	
	//猜你喜欢
	public static function getLimitVideos($where,$limit){
		$list = Db::name('Video')->where($where)->field('id,name,cover,hot_num')->limit($limit)->order('hot_num','desc')->select();
		if($list){
			foreach ($list as &$v){
				$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
			}
		}
		return $list;
	}
	
	//新增阅读量
	public static function addHot($video){
		$re = Db::name('Video')->where('id','=',$video['id'])->setInc('hot_num');
		if($re){
			$video['hot_num'] += 1;
			$key = 'video_'.$video['id'];
			cache($key,$video);
		}
	}

    //增加点赞
    public static function zan($video){
        $re = Db::name('Video')->where('id','=',$video['id'])->setInc('zan');
        if($re){
            $video['zan'] += 1;
            $key = 'video_'.$video['id'];
            cache($key,$video);
        }
    }
    //增加踩
    public static function cai($video){
        $re = Db::name('Video')->where('id','=',$video['id'])->setInc('cai');
        if($re){
            $video['cai'] += 1;
            $key = 'video_'.$video['id'];
            cache($key,$video);
        }
    }

	//阅读扣除书币并新增阅读历史
	public static function costMoney($video,$member){
		Db::startTrans();
		$flag = false;
		$data = [
			'video_id' => $video['id'],
			'uid' => $member['id'],
			'create_time' => time()
		];
		$re = Db::name('PlayHistory')->insert($data);
		if($re){
			$is_money = true;
			if($member['viptime'] > 0){
				$is_money = false;
				if($member['viptime'] != 1){
					$time = time();
					if($time > $member['viptime']){
						$is_money = true;
					}
				}
			}
			if($is_money){
				$res = Db::name('Member')->where('id','=',$member['id'])->setDec('money',$video['money']);
				if($res){
					$log = [
						'uid' => $member['id'],
						'money' => $video['money'],
						'summary' => '观看视频《'.$video['name'].'》',
						'create_time' => time()
					];
					$log_res = Db::name('MemberConsume')->insert($log);
					if($log_res){
						$member['money'] -= $video['money'];
						cache('member_info_'.$member['id'],$member,86400);
						$flag = true;
					}
				}
			}else{
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
	
	//添加阅读历史
	public static function addReadhistory($video_id,$uid){
		$data = [
			'video_id' => $video_id,
			'uid' => $uid,
			'create_time' => time()
		];
		$re = parent::add('PlayHistory', $data);
		return $re;
	}
	
	//获取更多列表
	public static function getMoreList($where,$page){
		$field = 'id,name,cover,summary,category,hot_num,free_type';
		
		$list = Db::name('Video')
		->where($where)
		->field($field)
		->page($page,10)
		->order('hot_num','desc')
		->select();
		if($list){
			$rank = 1;
			if($page > 1){
				$rank = ($page-1)*10+1;
			}
			foreach ($list as &$v){
				$v['rank'] = $rank;
				$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
				$v['summary'] = $v['summary'] ? : '暂无简介';
				$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
				$v['category'] = !empty($category) ? $category : '';
				$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
				$v['info_url'] = my_url('Video/info',['video_id'=>$v['id']]);
				$rank++;
			}
		}
		return $list;
	}

    /*
     * 20190925
     * wuxiong
     * */
    //获取我的视频收藏列表
    public static function getMyCollect($uid,$page){
        $field = 'max(a.id) as id,a.video_id,ANY_VALUE(b.name) as name,ANY_VALUE(b.cover) as cover';
        $list = Db::name('VideoCollection a')
            ->join('video b','a.video_id=b.id')
            ->where('a.uid','=',$uid)
            ->field($field)
            ->group('a.video_id')
            ->page($page,10)
            ->order('id','DESC')
            ->select();
        if($list){
            foreach ($list as &$v){
                $v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
                $v['info_url'] = my_url('Video/info',['video_id'=>$v['video_id']]);
            }
        }else{
            $list = '';
        }
        return $list;
    }

    //获取我的视频历史
    public static function getMyVideohistory($uid,$page){
        $field = 'max(a.id) as id,a.video_id,ANY_VALUE(b.name) as name,ANY_VALUE(b.cover) as cover';
        $list = Db::name('PlayHistory a')
            ->join('video b','a.video_id=b.id')
            ->where('a.uid','=',$uid)
            ->field($field)
            ->group('a.video_id')
            ->page($page,10)
            ->order('id','DESC')
            ->select();
        if($list){
            foreach ($list as &$v){
                $v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
                $v['info_url'] = my_url('Video/info',['video_id'=>$v['video_id']]);
            }
        }else{
            $list = '';
        }
        return $list;
    }
    //删除视频收藏
    public static function delCollect($video_id,$uid){
        $res = false;
        $re = Db::name('VideoCollection')
            ->where('video_id','in',$video_id)
            ->where('uid','=',$uid)
            ->delete();
        if($re !== false){
            $res = true;
        }
        return $res;
    }

    //删除视频记录
    public static function delplayCollect($video_id,$uid){
        $res = false;
        $re = Db::name('PlayHistory')
            ->where('video_id','in',$video_id)
            ->where('uid','=',$uid)
            ->delete();
        if($re !== false){
            $res = true;
        }
        return $res;
    }
}
