<?php
namespace app\agent\model;
use app\agent\model\Common;
use think\Db;
class aBook extends Common{
    
    
    //获取书籍列表
    public static function getBookPageList($where,$pages){
        $field = 'a.*,count(b.id) as total_chapter,c.book_id';
        $list = Db::name('Book a')
        ->join('book_chapter b','a.id=b.book_id','left')
        ->join('book_share c','a.id=c.book_id','left')
        ->where($where)
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
    
    //获取前10章内容
    public static function getTenChapter($book_id){
        $list = Db::name('BookChapter')->where('book_id','=',$book_id)->field('id,name,number')->order('number','asc')->limit(10)->select();
        return $list;
    }
    
    //获取文案书籍信息
    public static function getGuideChapter($book_id,$number){
        $list = Db::name('BookChapter')->where('book_id','=',$book_id)->where('number','<=',$number)->field('id,book_id,name,number')->select();
        if($list){
            foreach ($list as &$v){
                $v['content'] = getBlockContent($v['number'],'book/'.$v['book_id']);
            }
        }
        return $list;
    }
}