<?php
namespace app\index\model;
use app\index\model\Common;
use think\Db;
use app\common\model\myCache;
use app\common\model\myRequest;

class iBook extends Common{
    
    //获取搜索小说列表
    public static function searchBook($name,$limit=1){
        $where = [['status','=',1],['name','like','%'.$name.'%']];
        $books = Db::name('Book')->where($where)->field('id,name,cover,summary')->limit($limit)->select();
        if($books){
        	foreach ($books as &$v){
        		$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
        	}
        	if($limit == 1){
        		return $books[0];
        	}
        }
        return $books;
    }
    
    //新增阅读量
    public static function addHot($book){
        $re = Db::name('Book')->where('id','=',$book['id'])->setInc('hot_num');
        if($re){
        	$book['hot_num'] += 1;
        	$key = 'book_'.$book['id'];
        	cache($key,$book);
        }
    }
    
    //阅读扣除书币并新增阅读历史
    public static function costMoney($book,$number,$member){
    	Db::startTrans();
    	$flag = false;
    	$fres = Db::name('ReadHistory')->where('uid','=',$member['id'])->where('is_end','=',1)->setField('is_end',2);
    	if($fres !== false){
    		$data = [
    			'book_id' => $book['id'],
    			'type' => $book['type'],
    			'number' => $number,
    			'uid' => $member['id'],
    			'is_end' => 1,
    			'channel_id' => $member['wx_id'],
    			'create_time' => time()
    		];
    		$re = Db::name('ReadHistory')->insert($data);
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
    				$res = Db::name('Member')->where('id','=',$member['id'])->setDec('money',$book['money']);
    				if($res){
    					$log = [
    							'uid' => $member['id'],
    							'money' => $book['money'],
    							'summary' => '阅读书籍《'.$book['name'].'》',
    							'create_time' => time()
    					];
    					$log_res = Db::name('MemberConsume')->insert($log);
    					if($log_res){
    						$member['money'] -= $book['money'];
    						cache('member_info_'.$member['id'],$member,86400);
    						$flag = true;
    					}
    				}
    			}else{
    				$flag = true;
    			}
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
    public static function addReadhistory($book,$number,$member){
    	Db::startTrans();
    	$flag = false;
    	$fres = Db::name('ReadHistory')->where('uid','=',$member['id'])->where('is_end','=',1)->setField('is_end',2);
    	if($fres !== false){
    		$data = [
    			'book_id' => $book['id'],
    			'type' => $book['type'],
    			'number' => $number,
    			'uid' => $member['id'],
    			'is_end' => 1,
    			'channel_id' => $member['wx_id'],
    			'create_time' => time()
    		];
    		$re = Db::name('ReadHistory')->insert($data);
    		if($re){
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
    
    //新增章节阅读量
    public static function chapterRead($book_id,$number){
    	Db::name('BookChapter')->where('book_id','=',$book_id)->where('number','=',$number)->setInc('read_num');
    }
    
    //获取书籍分类列表
    public static function getCategoryList($where,$page){
    	$field = 'a.id,a.type,a.name,a.cover,a.summary,a.over_type,a.category,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
    	$list = Db::name('book a')
    	->join('book_chapter b','a.id=b.book_id','left')
    	->where($where)
    	->field($field)
    	->group('a.id')
    	->page($page,10)
    	->order('a.id','DESC')
    	->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
    			$v['summary'] = $v['summary'] ? : '暂无简介';
    			$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
    			$v['category'] = !empty($category) ? $category : '';
    			$v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
    			$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
    			$v['info_url'] = my_url('info',['book_id'=>$v['id']]);
    			if($v['type'] != 3){
    				$v['read_url'] = my_url('read',['book_id'=>$v['id'],'number'=>1]);
    			}else{
    				$v['read_url'] = $v['info_url'];
    			}
    		}
    	}
    	return $list;
    }
    //获取更多列表
    public static function getMoreList($where,$page){
    	$field = 'a.id,a.name,a.cover,a.summary,a.category,a.over_type,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
    	$list = Db::name('Book a')
    	->join('book_chapter b','a.id=b.book_id','left')
    	->where($where)
    	->field($field)
    	->group('a.id')
    	->page($page,10)
    	->order('a.hot_num','DESC')
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
    			$v['category'] = !empty($category) ? $category[0] : '';
    			$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
    			$v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
    			$v['info_url'] = my_url('Book/info',['book_id'=>$v['id']]);
    			$rank++;
    		}
    	}
    	return $list;
    }
    
    //获取我的收藏列表
    public static function getMyCollect($uid,$page){
    	$field = 'max(a.id) as id,a.book_id,ANY_VALUE(b.name) as name,ANY_VALUE(b.type) as type,ANY_VALUE(b.cover) as cover,ANY_VALUE(b.summary) as summary,ANY_VALUE(b.over_type) as over_type,ANY_VALUE(b.category) as category,ANY_VALUE(b.hot_num) as hot_num,IFNULL(max(c.number),0) as total_chapter';
    	$list = Db::name('MemberCollect a')
    	->join('book b','a.book_id=b.id')
    	->join('book_chapter c','a.book_id=c.book_id','left')
    	->where('a.uid','=',$uid)
    	->field($field)
    	->group('a.book_id')
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
    			$v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
    			$v['info_url'] = my_url('Book/info',['book_id'=>$v['book_id']]);
    			$type = '';
    			switch ($v['type']){
    				case 1:$type = '漫画';break;
    				case 2:$type = '小说';break;
    				case 3:$type = '听书';break;
    			}
    			$v['type'] = $type;
    		}
    	}else{
    		$list = '';
    	}
    	return $list;
    }
    
    //删除阅读历史
    public static function delCollect($book_id,$uid){
    	$res = false;
    	$re = Db::name('MemberCollect')
    	->where('book_id','in',$book_id)
    	->where('uid','=',$uid)
    	->delete();
    	if($re !== false){
    		$res = true;
    	}
    	return $res;
    }
    
    //获取我的阅读历史
    public static function getMyReadhistory($uid,$page){
    	$field = 'max(a.id) as id,max(a.number) as read_number,a.book_id,ANY_VALUE(b.name) as name,ANY_VALUE(b.type) as type,ANY_VALUE(b.cover) as cover,ANY_VALUE(b.summary) as summary,ANY_VALUE(b.over_type) as over_type,ANY_VALUE(b.category) as category,ANY_VALUE(b.hot_num) as hot_num,IFNULL(max(c.number),0) as total_chapter';
    	$list = Db::name('ReadHistory a')
    	->join('book b','a.book_id=b.id')
    	->join('book_chapter c','a.book_id=c.book_id','left')
    	->where('a.uid','=',$uid)
    	->field($field)
    	->group('a.book_id')
    	->page($page,10)
    	->order('id','desc')
    	->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
    			$v['summary'] = $v['summary'] ? : '暂无简介';
    			$category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
    			$v['category'] = !empty($category) ? $category : '';
    			$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
    			$v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
    			$v['info_url'] = my_url('Book/info',['book_id'=>$v['book_id']]);
    			if($v['type'] == 3){
    				$v['read_url'] = $v['info_url'];
    			}else{
    				$v['read_url'] = my_url('Book/read',['book_id'=>$v['book_id'],'number'=>$v['read_number']]);
    			}
    			$type = '';
    			switch ($v['type']){
    				case 1:$type = '漫画';break;
    				case 2:$type = '小说';break;
    				case 3:$type = '听书';break;
    			}
    			$v['type'] = $type;
    		}
    	}else{
    		$list = '';
    	}
    	return $list;
    }
    
    //删除阅读历史
    public static function delReadhistory($book_id,$uid){
    	$res = false;
    	$re = Db::name('ReadHistory')
    	->where('book_id','in',$book_id)
    	->where('uid','=',$uid)
    	->delete();
    	if($re !== false){
    		$res = true;
    	}
    	return $res;
    }
    
    //猜你喜欢
    public static function getLimitBooks($where,$limit){
    	$list = Db::name('Book')->where($where)->field('id,name,cover,hot_num')->limit($limit)->order('hot_num','desc')->select();
    	if($list){
    		foreach ($list as &$v){
    			$v['cover'] = $v['cover'] ? : '/static/templet/default/cover.png';
    		}
    	}
    	return $list;
    }
    
    //获取该书获得打赏金额
    public static function getRewardMoney($book_id){
    	$list = Db::name('Order')
    	->where('status','=',2)
    	->where('type','=',3)
    	->where('relation_id','=',$book_id)
    	->field('sum(money) as money,uid')
    	->group('uid')
    	->select();
    	$money = $people = 0;
    	if($list){
    		foreach ($list as $v){
    			$money += $v['money'];
    			$people++;
    		}
    	}
    	res_return(['money'=>$money,'people'=>$people]);
    }
    
    //获取分类选项
    public static function getCategoryOption($key){
    	$get = myRequest::get('category,free_type,gender_type,over_type');
    	$cateConfig = myCache::getBookConfigCache($key);
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
    	$gender_type = [
    			['name'=>'全部','val'=>'','is_check'=>1],
    			['name'=>'男生','val'=>'1','is_check'=>0],
    			['name'=>'女生','val'=>'2','is_check'=>0]
    	];
    	if($get['gender_type'] && in_array($get['gender_type'], [1,2])){
    		foreach ($gender_type as &$gv){
    			if($get['gender_type'] == $gv['val']){
    				$gv['is_check'] = 1;
    			}else{
    				$gv['is_check'] = 0;
    			}
    		}
    	}
    	$over_type = [
    			['name'=>'全部','val'=>'','is_check'=>1],
    			['name'=>'连载','val'=>'1','is_check'=>0],
    			['name'=>'完结','val'=>'2','is_check'=>0]
    	];
    	if($get['over_type'] && in_array($get['over_type'],[1,2])){
    		foreach ($over_type as &$ov){
    			if($get['over_type'] == $ov['val']){
    				$ov['is_check'] = 1;
    			}else{
    				$ov['is_check'] = 0;
    			}
    		}
    	}
    	$free_type = [
    			['name'=>'全部','val'=>'','is_check'=>1],
    			['name'=>'付费','val'=>'2','is_check'=>0],
    			['name'=>'免费','val'=>'1','is_check'=>0]
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
    	return ['category'=>$temp,'gender_type'=>$gender_type,'over_type'=>$over_type,'free_type'=>$free_type];
    }
    
}