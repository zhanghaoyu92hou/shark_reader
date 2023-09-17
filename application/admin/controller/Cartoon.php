<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mBook;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\common\model\myValidate;
use app\admin\model\mConfig;
use app\admin\model\mChapter;
use think\Db;

class Cartoon extends Common{
    
    //漫画列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','between',[1,2]],['a.type','=',1]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mBook::getBookPageList($where, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['status_name'] = $v['status'] == 1 ? '正常' : '已下架';
                    $v['over_type'] = $v['over_type'] == 1 ? '连载中' : '已完结';
                    $v['free_type'] = $v['free_type'] == 1 ? '免费' : '收费';
                    $v['gender_type'] = $v['gender_type'] == 1 ? '男频' : '女频';
                    $v['long_type'] = $v['long_type'] == 1 ? '长篇' : '短篇';
                    $v['is_hot'] = $v['is_hot'] == 1 ? '推荐' : '不推荐';
                    $v['do_url'] = my_url('doBook',['id'=>$v['id']]);
                    $v['chapter_url'] = my_url('chapter',['book_id'=>$v['id']]);
                    $v['spread_url'] = my_url('Spread/createLink',['book_id'=>$v['id']]);
                    $v['guide_url'] = my_url('guide',['book_id'=>$v['id']]);
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['customer_url'] = my_url('Message/addTask',['book_id'=>$v['id']]);
                    $v['share_url'] = my_url('setShareData',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('漫画');
        $book = mBook::getById('Book',$id,'id,name');
        if(!$book){
            res_return('书籍参数错误');
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
        
        $short_url = '/Index/Book/info.html?book_id='.$id;
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
    
    //新增漫画
    public function addBook(){
        if($this->request->isAjax()){
            $field = 'name,cover,detail_img,author,summary,money,status,long_type,free_type,new_type,gender_type,over_type,is_hot,free_chapter';
            $field .= ',hot_num,share_title,share_desc,sort_num,area,category';
            $data = myValidate::getData(mBook::$rules, $field);
            $data['type'] = 1;
            mBook::doneBook($data);
        }else{
            $field = 'id,name,cover,detail_img,author,summary,money:58,status,long_type,free_type:2,new_type,gender_type,over_type,is_hot,free_chapter:10';
            $field .= ',hot_num:0,share_title,share_desc,sort_num,area,category';
            $option = mBook::getBookRadioList();
            $option['cur'] = mBook::buildArr($field);
            $option['category'] = mConfig::getConfig('cartoon_category');
            $option['area'] = mConfig::getConfig('cartoon_area');
            $option['title'] = '更新漫画信息';
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('public/doBook');
        }
    }
    
    //编辑漫画
    public function doBook(){
        if($this->request->isAjax()){
            $field = 'id,name,cover,detail_img,author,summary,money,status,long_type,free_type,new_type,gender_type,over_type,is_hot,free_chapter';
            $field .= ',hot_num,share_title,share_desc,sort_num,area,category';
            $data = myValidate::getData(mBook::$rules, $field);
            mBook::doneBook($data);
        }else{
            $id = myRequest::getId('漫画');
            $cur = mBook::getById('Book',$id);
            if(!$cur){
                res_return('书籍不存在');
            }
            $option = mBook::getBookRadioList();
            $option['category'] = mConfig::getConfig('cartoon_category');
            $option['area'] = mConfig::getConfig('cartoon_area');
            $option['cur'] = $cur;
            $option['title'] = '更新漫画信息';
            $option['backUrl'] = my_url('index');
            $this->assign($option);
            return $this->fetch('public/doBook');
        }
    }
    
    //处理漫画事件
    public function doBookEvent(){
    	//file_put_contents("456.txt","123");
        $field = 'id,event';
        $data = myValidate::getData(mBook::$rules,$field);
        //res_return($data);
        if(in_array($data['event'], ['on','off'])){
            switch ($data['event']){
                case 'on':
                    $status = 1;
                    break;
                case 'off':
                    $status = 2;
                    break;
            }
            $re = mBook::setField('Book', [['id','=',$data['id']]], 'status', $status);
            if($re){
                res_return();
            }else{
                res_return('操作失败');
            }
        }else{
            if($data['event'] === 'delete'){
            	file_put_contents("data.txt",$data['id']);
                //$re = mBook::delete($data['id']);
                $re = Db::name('Book')->where('id','=',$data['id'])->delete();
                file_put_contents("123.txt","123");
                //$re = mBook::delete('501');
                if($re){
                    res_return();
                }else{
                    res_return('操作失败,请重试');
                }
            }
        }
    }
    
    //分集列表
    public function chapter(){
        $book_id = myRequest::getId('漫画','book_id');
        if($this->request->isAjax()){
            $config = [
                'default' => [['book_id','=',$book_id]],
                'eq' => 'number',
                'like' => 'keyword:name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mChapter::getChapterPageList($where,$pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            $this->assign('book_id',$book_id);
            return $this->fetch();
        }
    }
    
    //解析zip文件
    public function doDecodeZip(){
        set_time_limit(3600);
        $data = myValidate::getData(mChapter::$chapter, 'book_id,filename');
        $cur = mChapter::getById('Book', $data['book_id'],'id,type');
        if(empty($cur)){
            res_return('书籍不存在');
        }
        mChapter::readAndSaveCartoon($data['filename'], $data['book_id']);
        res_return();
    }
    
    //查看章节
    public function showInfo(){
        $id = myRequest::getId('漫画');
        $cur = mChapter::getById('BookChapter', $id);
        if(!$cur){
            res_return('章节不存在');
        }
        $cur['content'] = getBlockContent($cur['number'],'book/'.$cur['book_id']);
        $this->assign('cur',$cur);
        return $this->fetch('showInfo');
    }
    
    //章节检测
    public function checkChapter(){
        $book_id = myRequest::postId('漫画','book_id');
        $error = mChapter::checkChapter($book_id);
        if($error){
            res_return($error);
        }else{
            res_return('ok',0);
        }
    }
    
    //删除章节
    public function delChapter(){
        $data = myValidate::getData(mChapter::$chapter, 'id,book_id');
        $re = mChapter::delChapter($data['id'], $data['book_id']);
        if($re){
            res_return();
        }else{
            res_return('删除失败');
        }
    }
    
    //清空所有章节
    public function delAllChapter(){
        set_time_limit(1800);
        $book_id = myRequest::postId('漫画','book_id');
        $re = mChapter::delAllChapter($book_id);
        if($re){
            res_return();
        }else{
            res_return('删除失败');
        }
    }
    
    //发布区域配置
    public function area(){
        $key = 'cartoon_area';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'area');
            $data = $data ? : [];
            $res = mConfig::saveConfig($key,$data);
            if($res){
                if($data){
                    cache($key,$data,3600);
                }else{
                    cache($key,null);
                }
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if($cur === false){
                $cur = [];
                $re = mConfig::addConfig($key, $cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $variable = [
                'title' => '漫画发布区域配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/area');
        }
    }
    
    //轮播图片配置
    public function banners(){
        $key = 'cartoon_banner';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'src,link');
            $config = [];
            if($data['src']){
                $num = 0;
                foreach ($data['src'] as $k=>$v){
                    $num++;
                    $one = ['src'=>$v];
                    if(!$one['src']){
                        res_return('第'.$num.'张轮播图片未上传');
                    }
                    $one['link'] = $data['link'][$k];
                    $config[] = $one;
                }
            }
            $res = mConfig::saveConfig($key,$config);
            if($res){
                $this->assign('list',$config);
                $html = $this->fetch('block/banners');
                saveBlock($html, $key,'other');
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if($cur === false){
                $cur = [];
                $re = mConfig::addConfig($key, $cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $variable = [
                'title' => '漫画轮播图配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/banners');
        }
    }
    
    //类型配置
    public function category(){
        $key = 'cartoon_category';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'category');
            $data = $data ? : [];
            $res = mConfig::saveConfig($key,$data);
            if($res){
                if($data){
                    cache($key,$data,3600);
                }else{
                    cache($key,null);
                }
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if($cur === false){
                $cur = [];
                $re = mConfig::addConfig($key, $cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $variable = [
                'title' => '漫画类型配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/category');
        }
    }
    
    //底部导航
    public function footer(){
        $key = 'cartoon_footer';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'src,text,link');
            $config = [];
            if($data['src']){
                $num = 0;
                foreach ($data['src'] as $k=>$v){
                    $num++;
                    $one = ['src'=>$v];
                    if(!$one['src']){
                        res_return('第'.$num.'张图标未上传');
                    }
                    $one['link'] = $data['link'][$k];
                    $one['text'] = $data['text'][$k];
                    $config[] = $one;
                }
                if($num > 5){
                    res_return('最多可配置5个底部导航图标');
                }
            }
            $res = mConfig::saveConfig($key,$config);
            if($res){
            	$this->assign('list',$config);
            	$html = $this->fetch('block/footer');
            	saveBlock($html,'cartoon_footer','other');
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if($cur === false){
                $cur = [];
                $re = mConfig::addConfig($key,$cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $variable = [
                'title' => '漫画底部导航配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }
    
    //菜单导航
    public function nav(){
        $key = 'cartoon_nav';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'src,text,link');
            $config = [];
            if($data['src']){
                $num = 0;
                foreach ($data['src'] as $k=>$v){
                    $num++;
                    $one = ['src'=>$v];
                    if(!$one['src']){
                        res_return('第'.$num.'张图标未上传');
                    }
                    $one['link'] = $data['link'][$k];
                    $one['text'] = $data['text'][$k];
                    $config[] = $one;
                }
                if($num > 5){
                    res_return('最多可配置5个菜单导航图标');
                }
            }
            $res = mConfig::saveConfig($key,$config);
            if($res){
                $this->assign('list',$config);
                $html = $this->fetch('block/circleIcon');
                saveBlock($html,$key,'other');
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if($cur === false){
                $cur = [];
                $re = mConfig::addConfig($key,$cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $variable = [
                'title' => '漫画导航菜单配置',
                'cur' => $cur,
                'backUrl' => my_url('index')
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }
    
    //生成文案
    public function guide(){
        if($this->request->isAjax()){
            $post = myRequest::post('book_id,number');
            if($post['number'] > 0 && $post['number'] <= 10){
                $list = mChapter::getGuideChapter($post['book_id'], $post['number']);
                $this->assign('list',$list);
                $html = $this->fetch('block/getGuideChapter');
                $html = htmlspecialchars_decode($html);
                res_return(['info'=>$html]);
            }else{
                res_return('章节信息错误');
            }
        }else{
            $book_id = myRequest::getId('书籍','book_id');
            $book = mBook::getById('Book',$book_id,'id,name');
            if(empty($book)){
                res_return('书籍信息错误');
            }
            $cur = mChapter::getCur('BookChapter',[['book_id','=',$book_id],['number','=',1]],'id,book_id,name,number');
            if(!$cur){
                res_return('您尚未配置章节信息');
            }
            $cur['book_name'] = $book['name'];
            $cur['content'] = mChapter::getChapterContent($cur['book_id'], $cur['number']);
            $material = mBook::getList('Material',[]);
            $title = $cover = [];
            if($material){
                foreach ($material as $v){
                    $title[] = $v['title'];
                    $cover[] = $v['cover'];
                }
            }
            $max = count($material);
            if($max == 0){
                res_return('您尚未配置文案信息');
            }
            $random = 0;
            if($max > 1){
                $random = mt_rand(1,$max)-1;
            }
            $readpic = [
                '/static/templet/readpic/pic1.png',
                '/static/templet/readpic/pic2.png',
                '/static/templet/readpic/pic3.png',
                '/static/templet/readpic/pic4.gif',
                '/static/templet/readpic/pic5.gif',
                '/static/templet/readpic/pic6.gif'
            ];
            $chapters = mChapter::getTenChapter($cur['book_id']);
            $variable = [
                'cur' => $cur,
                'title' => $title,
                'cover' => $cover,
                'chapters' => $chapters,
                'cur_title' => $title[$random],
                'cur_cover' => $cover[$random],
                'readpic' => $readpic
            ];
            $this->assign($variable);
            return $this->fetch();
        }
    }
    
    //设置分享话术
    public function setShareData(){
    	if($this->request->isAjax()){
    		$rules = [
    				'book_id' => ['require|number|gt:0',['require'=>'书籍信息异常','number'=>'书籍信息异常','gt'=>'书籍信息异常']],
    				'title' => ['max:100',['max'=>'分享标题最多输入100个字符']],
    				'content' => ['max:500',['max'=>'分享内容最多输入500个字符']]
    		];
    		$data = myValidate::getData($rules,'book_id,title,content');
    		$cur = mBook::getCur('BookShare',[['book_id','=',$data['book_id']]]);
    		if($cur){
    			$re = mBook::save('BookShare',[['book_id','=',$data['book_id']]], $data);
    		}else{
    			$re = mBook::add('BookShare', $data);
    		}
    		if($re){
    			res_return();
    		}else{
    			res_return('配置失败,请重试');
    		}
    	}else{
    		$book_id = myRequest::getId('漫画');
    		$cur = mBook::getCur('BookShare',[['book_id','=',$book_id]]);
    		if(!$cur){
    			$field = 'book_id:'.$book_id.',title,content';
    			$cur = mBook::buildArr($field);
    		}
    		$variable = ['cur'=>$cur,'backUrl'=>my_url('index')];
    		$this->assign($variable);
    		return $this->fetch('public/setShareData');
    	}
    }
    
    //刷新缓存
    public function refreshCache(){
        $variable = mBook::getBlockData(1);
        $variable['title'] = '漫画';
        $this->assign($variable);
        $html = $this->fetch('block/bookContent');
        saveBlock($html, 'cartoon_content','other');
        $html = $this->fetch('block/ranks');
        saveBlock($html,'cartoon_ranks','other');
        res_return();
    }
    
}