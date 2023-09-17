<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
class mBook extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'name' =>  ["require|max:200",["require"=>"请输入书籍名称",'max'=>'书籍名称最多支持200个字符']],
        'author' =>  ["max:50",['max'=>'作者名称最多支持50个字符']],
        'cover' =>  ['max:255',['max'=>'书籍封面异常']],
        'detail_img' =>  ['max:255',['require'=>'书籍详情图片异常']],
        'summary' =>  ["max:500",['max'=>'书籍简介最多支持500个字符']],
        'status' => ["require|in:1,2",["require"=>"请选择书籍状态","in"=>"未指定该书籍状态"]],
        'is_hot' => ["require|in:1,2",["require"=>"请选择书籍是否热门推荐","in"=>"未指定该书籍热门推荐状态"]],
        'sort_num' => ["number",["number"=>"排序值必须为数字"]],
        'area' => ["array",["array"=>"发布区域参数异常"]],
        'category' => ["array",["array"=>"小说分类参数异常"]],
        'free_type' => ["require|in:1,2",["require"=>"请选择书籍是否免费","in"=>"未指定该书籍是否免费状态"]],
        'new_type' => ["require|in:1,2",["require"=>"请选择书籍是否新书","in"=>"未指定该书籍是否新书状态"]],
        'long_type' => ["require|in:1,2",["require"=>"请选择书籍篇幅","in"=>"未指定该书籍篇幅"]],
        'gender_type' => ["require|in:1,2",["require"=>"请选择书籍频道","in"=>"未指定该书籍频道"]],
        'over_type' => ["require|in:1,2",["require"=>"请选择书籍连载状态","in"=>"未指定该书籍连载状态"]],
        'free_chapter' => ["integer",["number"=>"免费章节必须为正整数"]],
        'money' => ["require|number",["require"=>"请输入书籍章节收费书币数量","in"=>"书币数量必须为正整数"]],
        'hot_num' => ["number",["require"=>"人气值必须为正整数"]],
        'share_title' =>  ["max:100",['max'=>'分享标题最多支持100个字符']],
        'share_desc' =>  ["max:500",['max'=>'分享描述最多支持500个字符']],
        'event' => ["require|in:on,off,delete",["require"=>'请选择按钮绑定事件',"in"=>'按钮绑定事件错误']],
        'zip_title' => ['require|array',["require"=>"未检测到上传书籍","array"=>"书籍参数格式不规范"]],
        'zip_filename' => ['require|array',["require"=>"未检测到上传书籍","array"=>"书籍参数格式不规范"]],
    ];
    
    //获取书籍列表
    public static function getBookPageList($where,$pages){
    	$reuest = request()->param('keyword');
    	$map = [];$name=[];$author=[];
    	if (!empty($reuest)){
    		$map=[
    			['a.lead','like','%'.$reuest.'%'],
    			];
    			$name = [
    				['a.name','like','%'.$reuest.'%'],
    				];
    				$author = [
    						['a.author','like','%'.$reuest.'%'],
    					];
    	}
    

        $field = 'a.*,IFNULL(max(b.number),0) as total_chapter';
        $list = Db::name('Book a')
        ->join('book_chapter b','a.id=b.book_id','left')
        ->where($where)->where($name)->whereOr($map)->whereOr($author)
        ->field($field)
        ->group('a.id')
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','DESC')
        ->select();
    
        $count = 0;
        if($list){
            $count = Db::name('Book a')->where($where)->count();
        }
        return ['count'=>$count,'data'=>$list];
    }
    
    //处理更新书籍
    public static function doneBook($data){
        $where = [['name','=',$data['name']]];
        if(isset($data['id'])){
            $where[] = ['id','<>',$data['id']];
        }
        $repeat = parent::getCur('Book', $where,'id,name');
        if($repeat){
            res_return('该书籍已存在');
        }
        if($data['category']){
            $data['category'] = ','.implode(',', $data['category']).',';
        }
        if($data['area']){
            $data['area'] = ','.implode(',', $data['area']).',';
        }
        if(isset($data['id'])){
            $re = parent::saveIdData('Book', $data);
        }else{
            $data['create_time'] = time();
            $re = Db::name('Book')->insert($data);
        }
        if($re !== false){
        	if(isset($data['id'])){
        		cache('book_'.$data['id'],null);
        	}
            res_return();
        }else{
            res_return('保存失败，请重试');
        }
    }
    
    //批量新增小说
    public static function addMore($data){
        $zip_title = $data['zip_title'];
        $zip_filename = $data['zip_filename'];
        unset($data['zip_title']);
        unset($data['zip_filename']);
        if($data['category']){
        	$data['category'] = ','.implode(',', $data['category']).',';
        }
        if($data['area']){
        	$data['area'] = ','.implode(',', $data['area']).',';
        }
        $data['type'] = 2;
        $data['create_time'] = time();
        $zipPath = env('root_path').'static/temp/zip/';
        $table = 'BookChapter';
        $logs = [];
        foreach ($zip_title as $k=>$v){
            $book_name = rtrim($v,'.zip');
            if(!$book_name){
                $logs[] = '小说名称异常';
                continue;
            }
            $data['name'] = $book_name;
            $repeat = Db::name('Book')->where('name','=',$book_name)->value('id');
            if($repeat){
                $logs[] = '小说【'.$book_name.'】已存在';
                continue;
            }
            $zipFile = $zipPath.$zip_filename[$k];
            if(!@is_file($zipFile)){
                $logs[] = '小说【'.$book_name.'】压缩包不存在';
                continue;
            }
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if(!$rs){
                $zip->close();
                @unlink($zipFile);
                $logs[] = '小说【'.$book_name.'】解压失败';
                continue;
            }
            $book_id = Db::name('Book')->insertGetId($data);
            if(!$book_id){
                $logs[] = '小说【'.$book_name.'】新增失败';
                $zip->close();
                @unlink($zipFile);
                continue;
            }
            $docnum = $zip->numFiles;
            for($i = 0; $i < $docnum; $i++) {
                $stateInfo = $zip->statIndex($i);
                if($stateInfo['crc'] != 0 && $stateInfo['size'] > 0){
                    $name = $zip->getNameIndex($i, \ZipArchive::FL_ENC_RAW);
                    $encode = mb_detect_encoding($name,['ASCII','GB2312','GBK','UTF-8']);
                    $encode = $encode ? $encode : 'GBK';
                    $thisName = mb_convert_encoding($name,'UTF-8',$encode);
                    $pathInfo = pathinfo($thisName);
                    if($pathInfo && is_array($pathInfo)){
                        if($pathInfo['extension'] === 'txt'){
                            $title = $pathInfo['filename'];
                            $number = self::getFileNumber($title);
                            if($number > 0){
                                $chapter_repeat = Db::name($table)->where('book_id','=',$book_id)->where('number','=',$number)->value('id');
                                if(!$chapter_repeat){
                                    $content = file_get_contents('zip://'.$zipFile.'#'.$stateInfo['name']);
                                    $encode = mb_detect_encoding($content,['ASCII','GB2312','GBK','UTF-8']);
                                    $encode = $encode ? $encode : 'GBK';
                                    $content = mb_convert_encoding($content, 'UTF-8', $encode);
                                    $html = "<p>".$content;
                                    $html = preg_replace('/\n|\r\n/','</p><p>',$html);
                                    $chapter_data = [
                                        'book_id'=>$book_id,
                                        'name'=>$title,
                                        'number'=>$number,
                                        'create_time'=>time()
                                    ];
                                    $chapter_id = Db::name($table)->insertGetId($chapter_data);
                                    if($chapter_id){
                                        $block_res = saveBlock($html,$number,'book/'.$book_id);
                                        if(!$block_res){
                                            Db::name($table)->where('id','=',$chapter_id)->delete();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $logs[] = '小说【'.$book_name.'】新增成功';
            $zip->close();
            @unlink($zipFile);
        }
        res_return($logs);
    }
    
    //删除书籍
    public static function delete($book_id){
        $flag = false;
        $re = mChapter::delAllChapter($book_id);
        if($re){
            $res = Db::name('Book')->where('id','=',$book_id)->delete();
            if($res){
            	cache('book_'.$book_id,null);
                $flag = true;
            }
        }
        return $flag;
    }
    
    //获取书籍属性选项
    public static function getBookRadioList(){
        $option = [
            'status' => [
                'name' => 'status',
                'option' => [['val'=>1,'text'=>'上架','default'=>0],['val'=>2,'text'=>'下架','default'=>1]]
            ],
            'long_type' => [
                'name' => 'long_type',
                'option' => [['val'=>1,'text'=>'长篇','default'=>1],['val'=>2,'text'=>'短篇','default'=>0]]
            ],
            'new_type' => [
                'name' => 'new_type',
                'option' => [['val'=>1,'text'=>'新书','default'=>1],['val'=>2,'text'=>'非新书','default'=>0]]
            ],
            'free_type' => [
                'name' => 'free_type',
                'option' => [['val'=>1,'text'=>'免费','default'=>0],['val'=>2,'text'=>'收费','default'=>1]]
            ],
            'gender_type' => [
                'name' => 'gender_type',
                'option' => [['val'=>1,'text'=>'男频','default'=>1],['val'=>2,'text'=>'女频','default'=>0]]
            ],
            'over_type' => [
                'name' => 'over_type',
                'option' => [['val'=>1,'text'=>'连载中','default'=>1],['val'=>2,'text'=>'已完结','default'=>0]]
            ],
            'is_hot' => [
                'name' => 'is_hot',
                'option' => [['val'=>1,'text'=>'是','default'=>1],['val'=>2,'text'=>'否','default'=>0]]
            ],
        ];
        return $option;
    }
    
    //获取更新数据
    public static function getBlockData($type=1){
        $hot = Db::name('Book')->where('status','=',1)->where('type','=',$type)->field('id,cover,name')->order('hot_num','DESC')->limit(8)->select();
        $foot_hot = Db::name('Book a')
            ->join('book_chapter b','a.id=b.book_id','left')
            ->where('a.status','=',1)
            ->where('a.is_hot','=',1)
            ->where('a.type','=',$type)
            ->field('a.id,a.name,a.cover,a.category,a.summary,a.over_type,a.hot_num,IFNULL(max(b.number),0) as count')
            ->group('a.id')
            ->order('a.hot_num','DESC')
            ->limit(8)
            ->select();
        $foot_hot = self::makeupBookList($foot_hot);
        $temp = [];
        $key = 'cartoon_area';
        switch ($type){
            case 2: $key = 'novel_area'; break;
            case 3: $key = 'music_area'; break;
        }
        $area = mConfig::getConfig($key);
        if($area){
            foreach ($area as $v){
                $list = Db::name('Book')
                    ->where('status','=',1)
                    ->where('type','=',$type)
                    ->where('area','like','%,'.$v.',%')
                    ->field('id,name,cover,summary')
                    ->limit(6)
                    ->order('sort_num','desc')
                    ->order('id','desc')
                    ->select();
                if($list){
                    $temp[] = [
                        'name'=>$v,
                        'url' => '/index/Book/more.html?type='.$type.'&area='.urlencode($v),
                        'child'=>$list
                    ];
                }
            }
        }
        $rank_field = 'a.id,a.name,a.cover,a.summary,a.over_type,a.category,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
        $total_ranks = Db::name('Book a')
        	->join('book_chapter b','a.id=b.book_id','left')
        	->where('a.status','=',1)
        	->where('a.type','=',$type)
        	->group('a.id')
        	->field($rank_field)
        	->limit(10)
        	->order('a.hot_num','DESC')
        	->select();
        $total_ranks = self::makeupBookList($total_ranks);
        $man_ranks = Db::name('Book a')
	        ->join('book_chapter b','a.id=b.book_id','left')
	        ->where('a.status','=',1)
	        ->where('a.type','=',$type)
	        ->where('a.gender_type','=',1)
	        ->group('a.id')
	        ->field($rank_field)
	        ->limit(10)
	        ->order('a.hot_num','DESC')
	        ->select();
        $man_ranks = self::makeupBookList($man_ranks);
        $women_ranks = Db::name('Book a')
	        ->join('book_chapter b','a.id=b.book_id','left')
	        ->where('a.status','=',1)
	        ->where('a.type','=',$type)
	        ->where('a.gender_type','=',2)
	        ->group('a.id')
	        ->field($rank_field)
	        ->limit(10)
	        ->order('a.hot_num','DESC')
	        ->select();
        $women_ranks = self::makeupBookList($women_ranks);
        $res = ['hot'=>$hot,'area'=>$temp,'foot_hot'=>['list'=>$foot_hot,'url'=>'/index/Book/more.html?type='.$type.'&is_hot=1'],'ranks'=>['total'=>$total_ranks,'man'=>$man_ranks,'women'=>$women_ranks]];
        return $res;
    }
    
    private static function makeupBookList($list){
    	if($list){
    		foreach ($list as &$v){
    			$v['over_type'] = $v['over_type'] == 1 ? '连载中' : '已完结';
    			$v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
    			if($v['category']){
    				$category = explode(',', trim($v['category'],','));
    				$v['category'] = $category[0];
    			}
    		}
    	}
    	return $list;
    }
    
    //获取章节
    private static function getFileNumber($title){
    	$arr1 = explode('章', $title);
    	$number = 0;
    	if(count($arr1) > 1){
    		$number = trim(str_replace('第','',$arr1[0]));
    	}else{
    		$arr2 = explode('话', $title);
    		if(count($arr2) > 1){
    			$number = trim(str_replace('第','',$arr2[0]));
    		}
    	}
    	if($number){
    		if(is_numeric($number)){
    			$number = intval($number);
    		}else{
    			$reg = '/^[零一壹二贰三叁四肆五伍六陆七柒八捌九玖十拾百佰千仟万两]{1,}$/';
    			if(preg_match($reg, $number)){
    				$number = self::chrtonum($number);
    			}else{
    				$number = 0;
    			}
    		}
    	}
    	return $number;
    }
    
    //删除文件夹及子目录和文件
    private static function delDirAndFile( $dirName){
    	if ( @$handle = opendir( "$dirName" ) ) {
    		while ( false !== ( $item = readdir( $handle ) ) ) {
    			if ( $item !== "." && $item !== ".." ) {
    				if ( is_dir( "$dirName/$item" ) ) {
    					self::delDirAndFile( "$dirName/$item" );
    				} else {
    					@unlink( "$dirName/$item" );
    				}
    			}
    		}
    		closedir( $handle );
    		rmdir( "$dirName/$item" );
    	}
    }
    
    //中文转阿拉伯
    private static function chrtonum($string){
    	if(is_numeric($string)){
    		return $string;
    	}
    	$string = str_replace('仟', '千', $string);
    	$string = str_replace('佰', '百', $string);
    	$string = str_replace('拾', '十', $string);
    	$num = 0;
    	$wan = explode('万', $string);
    	if (count($wan) > 1) {
    		$num += self::chrtonum($wan[0]) * 10000;
    		$string = $wan[1];
    	}
    	$qian = explode('千', $string);
    	if (count($qian) > 1) {
    		$num += self::chrtonum($qian[0]) * 1000;
    		$string = $qian[1];
    	}
    	$bai = explode('百', $string);
    	if (count($bai) > 1) {
    		$num += self::chrtonum($bai[0]) * 100;
    		$string = $bai[1];
    	}
    	$shi = explode('十', $string);
    	if (count($shi) > 1) {
    		$num += self::chrtonum($shi[0] ? $shi[0] : '一') * 10;
    		$string = $shi[1] ? $shi[1] : '零';
    	}
    	$ling = explode('零', $string);
    	if (count($ling) > 1) {
    		$string = $ling[1];
    	}
    	$d = [
    			'一' => '1','二' => '2','三' => '3','四' => '4','五' => '5','六' => '6','七' => '7','八' => '8','九' => '9',
    			'壹' => '1','贰' => '2','叁' => '3','肆' => '4','伍' => '5','陆' => '6','柒' => '7','捌' => '8','玖' => '9',
    			'零' => 0, '0' => 0, 'O' => 0, 'o' => 0,
    			'两' => 2
    	];
    	return $num + @$d[$string];
    }
    
}