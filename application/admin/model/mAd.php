<?php

namespace app\admin\model;

use app\admin\model\Common;
use think\Db;

class mAd extends Common {

    public static $rules = [
        'id' => ["require|number|gt:0", ["require" => "主键参数错误", 'number' => '主键参数错误', "gt" => "主键参数错误"]],
        'name' => ["require|max:200", ["require" => "请输入广告位名称", 'max' => '广告位称最多支持200个字符']],
        'sort' => ["require|max:10", ["require" => "请输入排序", 'max' => '排序最多支持10个字符']],
    ];
    public static $add_rules = [
        'name' => ["require|max:200", ["require" => "请输入广告位名称", 'max' => '广告位名称最多支持200个字符']],
        'sort' => ["require|max:10", ["require" => "请输入排序", 'max' => '排序最多支持10个字符']],
    ];

    //获取广告位置列表
    public static function getAdPageList($where, $pages) {
        $reuest = request()->param('keyword');
        $name = [];
        if (!empty($reuest)) {
            $name = [
                ['a.name', 'like', '%' . $reuest . '%'],
            ];
        }
        $field = 'a.*,IFNULL(count(c.id),0) as total_chapter';
        $list = Db::name('ad')->alias('a')
                ->join('ad_cate c', 'a.id=c.cid', 'LEFT')
                ->where($where)->where($name)
                ->field($field)
                ->group('a.id')
                ->page($pages['page'], $pages['limit'])
                ->order('a.sort DESC,a.id DESC')
                ->select();
//
//        $list = Db::name('Book a')
//                ->join('book_chapter b', 'a.id=b.book_id', 'left')
//                ->where($where)->where($name)->whereOr($map)->whereOr($author)
//                ->field($field)
//                ->group('a.id')
//                ->page($pages['page'], $pages['limit'])
//                ->order('a.id', 'DESC')
//                ->select();
        $count = 0;
        if ($list) {
            $count = Db::name('ad a')->where($where)->count();
        }
        return ['count' => $count, 'data' => $list];
    }

    //处理更新广告
    public static function updateAd($data) {
        $where = [['name', '=', $data['name']]];
        if (!empty($data['id'])) {
            $where[] = ['id', '<>', $data['id']];
        }
        $repeat = parent::getCur('ad', $where, 'id,name');
        if ($repeat) {
            res_return('该广告位置已存在');
        }
        if (!empty($data['id'])) {
            $re = parent::saveIdData('ad', $data);
        } else {
            $data['time'] = time();
       
            $re = Db::name('ad')->insert($data);
        }
       
        if ($re !== false) {
            if (isset($data['id'])) {
                cache('ad_' . $data['id'], null);
            }
            res_return();
        } else {
            res_return('保存失败，请重试');
        }
    }

    //删除广告位
    public static function delete($id) {
        $flag = false;
        $res = Db::name('ad')->where('id', '=', $id)->delete();
        if ($res) {
            cache('ad_' . $id, null);
            $flag = true;
            Db::name('ad_cate')->where('cid', '=', $id)->delete(); //删除广告位下的广告
        }
        return $flag;
    }

    //获取更新数据
    public static function getBlockData($type = 1) {
        $hot = Db::name('Book')->where('status', '=', 1)->where('type', '=', $type)->field('id,cover,name')->order('hot_num', 'DESC')->limit(8)->select();
        $foot_hot = Db::name('Book a')
                ->join('book_chapter b', 'a.id=b.book_id', 'left')
                ->where('a.status', '=', 1)
                ->where('a.is_hot', '=', 1)
                ->where('a.type', '=', $type)
                ->field('a.id,a.name,a.cover,a.category,a.summary,a.over_type,a.hot_num,IFNULL(max(b.number),0) as count')
                ->group('a.id')
                ->order('a.hot_num', 'DESC')
                ->limit(8)
                ->select();
        $foot_hot = self::makeupBookList($foot_hot);
        $temp = [];
        $key = 'cartoon_area';
        switch ($type) {
            case 2: $key = 'novel_area';
                break;
            case 3: $key = 'music_area';
                break;
        }
        $area = mConfig::getConfig($key);
        if ($area) {
            foreach ($area as $v) {
                $list = Db::name('Book')
                        ->where('status', '=', 1)
                        ->where('type', '=', $type)
                        ->where('area', 'like', '%,' . $v . ',%')
                        ->field('id,name,cover,summary')
                        ->limit(6)
                        ->order('sort_num', 'desc')
                        ->order('id', 'desc')
                        ->select();
                if ($list) {
                    $temp[] = [
                        'name' => $v,
                        'url' => '/index/Book/more.html?type=' . $type . '&area=' . urlencode($v),
                        'child' => $list
                    ];
                }
            }
        }
        $rank_field = 'a.id,a.name,a.cover,a.summary,a.over_type,a.category,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
        $total_ranks = Db::name('Book a')
                ->join('book_chapter b', 'a.id=b.book_id', 'left')
                ->where('a.status', '=', 1)
                ->where('a.type', '=', $type)
                ->group('a.id')
                ->field($rank_field)
                ->limit(10)
                ->order('a.hot_num', 'DESC')
                ->select();
        $total_ranks = self::makeupBookList($total_ranks);
        $man_ranks = Db::name('Book a')
                ->join('book_chapter b', 'a.id=b.book_id', 'left')
                ->where('a.status', '=', 1)
                ->where('a.type', '=', $type)
                ->where('a.gender_type', '=', 1)
                ->group('a.id')
                ->field($rank_field)
                ->limit(10)
                ->order('a.hot_num', 'DESC')
                ->select();
        $man_ranks = self::makeupBookList($man_ranks);
        $women_ranks = Db::name('Book a')
                ->join('book_chapter b', 'a.id=b.book_id', 'left')
                ->where('a.status', '=', 1)
                ->where('a.type', '=', $type)
                ->where('a.gender_type', '=', 2)
                ->group('a.id')
                ->field($rank_field)
                ->limit(10)
                ->order('a.hot_num', 'DESC')
                ->select();
        $women_ranks = self::makeupBookList($women_ranks);
        $res = ['hot' => $hot, 'area' => $temp, 'foot_hot' => ['list' => $foot_hot, 'url' => '/index/Book/more.html?type=' . $type . '&is_hot=1'], 'ranks' => ['total' => $total_ranks, 'man' => $man_ranks, 'women' => $women_ranks]];
        return $res;
    }

    private static function makeupBookList($list) {
        if ($list) {
            foreach ($list as &$v) {
                $v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
                $v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num'] / 10000, 2) . '万' : $v['hot_num'];
                if ($v['category']) {
                    $category = explode(',', trim($v['category'], ','));
                    $v['category'] = $category[0];
                }
            }
        }
        return $list;
    }

    //获取章节
    private static function getFileNumber($title) {
        $arr1 = explode('章', $title);
        $number = 0;
        if (count($arr1) > 1) {
            $number = trim(str_replace('第', '', $arr1[0]));
        } else {
            $arr2 = explode('话', $title);
            if (count($arr2) > 1) {
                $number = trim(str_replace('第', '', $arr2[0]));
            }
        }
        if ($number) {
            if (is_numeric($number)) {
                $number = intval($number);
            } else {
                $reg = '/^[零一壹二贰三叁四肆五伍六陆七柒八捌九玖十拾百佰千仟万两]{1,}$/';
                if (preg_match($reg, $number)) {
                    $number = self::chrtonum($number);
                } else {
                    $number = 0;
                }
            }
        }
        return $number;
    }

    //删除文件夹及子目录和文件
    private static function delDirAndFile($dirName) {
        if (@$handle = opendir("$dirName")) {
            while (false !== ( $item = readdir($handle) )) {
                if ($item !== "." && $item !== "..") {
                    if (is_dir("$dirName/$item")) {
                        self::delDirAndFile("$dirName/$item");
                    } else {
                        @unlink("$dirName/$item");
                    }
                }
            }
            closedir($handle);
            rmdir("$dirName/$item");
        }
    }

    //中文转阿拉伯
    private static function chrtonum($string) {
        if (is_numeric($string)) {
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
            '一' => '1', '二' => '2', '三' => '3', '四' => '4', '五' => '5', '六' => '6', '七' => '7', '八' => '8', '九' => '9',
            '壹' => '1', '贰' => '2', '叁' => '3', '肆' => '4', '伍' => '5', '陆' => '6', '柒' => '7', '捌' => '8', '玖' => '9',
            '零' => 0, '0' => 0, 'O' => 0, 'o' => 0,
            '两' => 2
        ];
        return $num + @$d[$string];
    }

}
