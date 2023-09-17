<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mConfig;
use think\Db;
class Block extends Common{
    
    //更新小说首页缓存
    public function novelCache(){
        self::createNovelHot();
        self::createNovelArea();
    }
    
    //更新热门小说缓存
    private function createNovelHot(){
        $books = Db::name('Book')->where('type','=',2)->where('status','=',1)->field('id,name,cover')->order('hot_num','DESC')->order('id','DESC')->limit(6)->select();
        if($books){
            $hots = [
                'name' => '热门小说',
                'books' => $books
            ];
            $this->assign('hots',$hots);
            $html = $this->fetch('block/novelHot');
            saveBlock($html, 'novel_hot','other');
        }
    }
    
    //更新小说首页发布区域缓存
    private function createNovelArea(){
        $area = mConfig::getConfig('novel_area');
        if($area){
            $temp = [];
            foreach ($area as $v){
                $book = Db::name('Book')->where('type','=',2)->where('status','=',1)->where('area','like',',%'.$v.'%,')->field('id,name,cover')->order('hot_num','DESC')->order('id','DESC')->limit(6)->select();
                if($book){
                    $one = [
                        'name' => $v,
                        'books' => $book
                    ];
                    $temp[] = $one;
                }
            }
            if($temp){
                $this->assign('area',$temp);
                $html = $this->fetch('block/novelArea');
                saveBlock($html,'novel_area','other');
            }
        }
    }
}