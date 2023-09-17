<?php
namespace app\channel\controller;
use app\channel\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\channel\model\cBook;
use app\channel\model\cLogin;

class Novel extends Common{
    
    //小说列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','=',1],['a.type','=',2]],
                'eq' => 'status:a.status',
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = cBook::getBookPageList($where, $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['over_type'] = $v['long_type'] == 1 ? '连载中' : '已完结';
                    $v['free_type'] = $v['free_type'] == 1 ? '免费' : '收费';
                    $v['gender_type'] = $v['gender_type'] == 1 ? '男频' : '女频';
                    $v['long_type'] = $v['long_type'] == 1 ? '长篇' : '短篇';
                    $v['spread_url'] = my_url('Spread/createLink',['book_id'=>$v['id']]);
                    $v['guide_url'] = my_url('guide',['book_id'=>$v['id']]);
                    $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                    $v['customer_url'] = my_url('Message/addTask',['book_id'=>$v['id']]);
                    $v['share_url'] = my_url('bookShare',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            return $this->fetch();
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('小说');
        $book = cBook::getById('Book',$id,'id,name');
        if(!$book){
            res_return('书籍参数错误');
        }
        $config = cLogin::getCache();
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
            'links' => [
                ['title'=>'小说链接','val'=>$url.$short_url]
            ]
        ];
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
    
    
    //生成文案
    public function guide(){
        if($this->request->isAjax()){
            $post = myRequest::post('book_id,number');
            if($post['number'] > 0 && $post['number'] <= 10){
                $list = cBook::getGuideChapter($post['book_id'], $post['number']);
                $this->assign('list',$list);
                $html = $this->fetch('public/getGuideChapter');
                $html = htmlspecialchars_decode($html);
                res_return(['info'=>$html]);
            }else{
                res_return('章节信息错误');
            }
        }else{
            $book_id = myRequest::getId('小说','book_id');
            $book = cBook::getById('Book',$book_id,'id,name');
            if(empty($book)){
                res_return('书籍信息错误');
            }
            $cur = cBook::getcur('BookChapter',[['book_id','=',$book_id],['number','=',1]],'id,book_id,name,number');
            if(!$cur){
                res_return('您尚未配置章节信息');
            }
            $cur['book_name'] = $book['name'];
            $cur['content'] = getBlockContent($cur['number'],'book/'.$cur['book_id']);
            $material = cBook::getList('Material',[]);
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
            $chapters = cBook::getTenChapter($cur['book_id']);
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
    
    //分享话术
    public function bookShare(){
    	$book_id = myRequest::getId('小说');
    	$cur = cBook::getCur('BookShare',[['book_id','=',$book_id]]);
    	if(!$cur){
    		$cur = ['title'=>'','content'=>''];
    	}
    	$this->assign('cur',$cur);
    	return $this->fetch('public/bookShare');
    }
    
}