<?php
namespace app\admin\model;
use app\admin\model\Common;
use think\Db;
use app\common\model\myValidate;
class mSpread extends Common{
    
    public static $rules = [
        'id' =>  ["require|number|gt:0",["require"=>"主键参数错误",'number'=>'主键参数错误',"gt"=>"主键参数错误"]],
        'book_id' =>  ["require|number|gt:0",["require"=>"书籍参数错误",'number'=>'书籍参数错误',"gt"=>"书籍参数错误"]],
        'chapter_id' => ["require|number|gt:0",["require"=>"请选择推广章节",'number'=>'推广章节参数错误',"gt"=>"推广章节参数错误"]],
        'name' =>  ["require|max:100",["require"=>"请输入推广名称",'max'=>'推广名称最多支持100个字符']],
        'is_sub' => ["require|in:1,2",["require"=>"请选择是否强制关注","in"=>"强制关注选择错误"]],
        'number' => ["requireIf:is_sub,1|number|gt:0",["requireIf"=>"请输入强制关注章节",'number'=>'请输入强制关注章节',"gt"=>"强制关注章节必须大于0"]],
        'remark' =>  ["max:255",['max'=>'备注最多支持255个字符']]
    ];
    
    //获取10章节
    public static function getTenChapter($book_id){
        $chapter = Db::name('BookChapter')->where('book_id','=',$book_id)->field('id,name,number')->order('number','asc')->limit(10)->select();
        return $chapter;
    }
    
    //获取推广链接列表
    public static function getSpreadList($where,$pages){
        $field = 'a.id,a.name,a.url,a.is_sub,a.number,a.member_num,a.visitor_num,a.create_time,b.name as book_name,c.name as chapter_name';
        $list = Db::name('Spread a')
        ->join('book b','a.book_id=b.id')
        ->join('book_chapter c','a.book_id=c.book_id and a.chapter_number=c.number')
        ->where($where)
        ->field($field)
        ->page($pages['page'],$pages['limit'])
        ->order('a.id','desc')
        ->select();
        $count = 0;
        if($list){
            foreach ($list as &$v){
                $sub_str = '未启用';
                if($v['is_sub'] == 1){
                    $sub_str = '强制关注第'.$v['number'].'章';
                }
                $v['sub_str'] = $sub_str;
                $v['qrcode_url'] = my_url('qrcode',['id'=>$v['id']]);
                $v['copy_url'] = my_url('copyLink',['id'=>$v['id']]);
                $v['do_url'] = my_url('doLink',['id'=>$v['id']]);
                $v['create_time'] = date('Y-m-d H:i',$v['create_time']); 
            }
            $count = Db::name('Spread a')
            ->join('book b','a.book_id=b.id')
            ->join('book_chapter c','a.book_id=c.book_id and a.chapter_number=c.number')
            ->where($where)
            ->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    //更新推广链接
    public static function doneSpread($field){
        $flag = false;
        $data = myValidate::getData(self::$rules, $field);
        $time = time();
        $book = parent::getById('Book',$data['book_id'],'id,name,type');
        if(!in_array($book['type'],[1,2])){
            res_return('仅漫画和小说可生成推广链接');
        }
        $chapter = parent::getById('BookChapter',$data['chapter_id'],'id,book_id,number');
        if(!$chapter || $chapter['book_id'] != $data['book_id']){
            res_return('章节信息异常');
        }
        $data['update_time'] = $time;
        if($data['is_sub'] == 1){
            if($chapter['number'] >= $data['number']){
                res_return('强制关注章节必须大于推广章节');
            }
        }
        $data['chapter_number'] = $chapter['number'];
        if(array_key_exists('id',$data)){
            $id = $data['id'];
            unset($data['id']);
            $re = Db::name('Spread')->where('id','=',$id)->update($data);
            if($re !== false){
                $flag = true;
            }
        }else{
            $site = mConfig::getConfig('website');
            if(!$site){
                res_return('您尚未配置站点信息');
            }
            if(!array_key_exists('url', $site)){
                res_return('您尚未配置站点域名');
            }
            $url = 'http://';
            if($site['is_location'] == 1 && $site['location_url']){
                $url .= $site['location_url'];
            }else{
                $url .= $site['url'];
            }
            $url .= '/index/Book/read.html';
            $data['type'] = $book['type'];
            $data['create_time'] = $time;
            $data['update_time'] = $time;
            $re = Db::name('Spread')->insertGetId($data);
            if($re){
                $url .= '?spread='.$re.'&book_id='.$data['book_id'].'&number='.$data['chapter_number'];
                $short_url = getSinaShortUrl($url);
                $save = [
                    'url' => $url,
                    'short_url' => $short_url
                ];
                $res = Db::name('Spread')->where('id','=',$re)->update($save);
                if($res){
                    $flag = true;
                }else{
                    Db::name('Spread')->where('id','=',$re)->delete();
                }
            }
        }
        if($flag){
            res_return('ok');
        }else{
            res_return('生成推广链接失败');
        }
    }
}