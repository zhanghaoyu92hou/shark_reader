<?php
namespace app\channel\model;
use app\channel\model\Common;
use think\Db;
class cMaterial extends Common{
    
    //获取文案分组信息
    public static function getMaterialGroup(){
        $material = Db::name('Material')->field('title,cover')->select();
        $title = $cover = $res = [];
        if($material){
            $max = count($material);
            foreach ($material as $v){
                $title[] = $v['title'];
                $cover[] = $v['cover'];
            }
            $res = [
                'count' => $max,
                'title' => $title,
                'cover' => $cover
            ];
        }
        return $res;
    }
}