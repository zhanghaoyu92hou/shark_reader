<?php

namespace app\admin\model;

use app\admin\model\Common;
use think\Db;
use OSS\OssClient;
use OSS\Core\OssException;
use app\common\model\myCache;
use app\common\model\myAliyunoss;

class mAdCate extends Common {

    public static $rules = [
        'cid' => ["require|number|gt:0", ["require" => "分集主键参数错误", 'number' => '分集主键参数错误', "gt" => "分集主键参数错误"]],
        'img' => ["require", ['require' => '请上传广告图']],
        'url' => ["require", ['require' => '请输入广告跳转链接']],
    ];

    //获取分集列表
    public static function getAdCatePageList($where, $pages) {
        $list = Db::name('ad_cate')->where($where)->page($pages['page'], $pages['limit'])->order('sort', 'desc')->select();
        $count = 0;
        if ($list) {
//            foreach ($list as &$v){
//                $v['do_url'] = my_url('doChapter',['id'=>$v['id']]);
//                $v['show_url'] = my_url('showInfo',['id'=>$v['id']]);
//            }
            $count = Db::name('ad_cate')->where($where)->count();
        }
        return ['data' => $list, 'count' => $count];
    }

    //获取前10章内容
    public static function getTenChapter($book_id) {
        $list = Db::name('BookChapter')->where('book_id', '=', $book_id)->field('id,name,number')->order('number', 'asc')->limit(10)->select();
        return $list;
    }

    //获取文案书籍信息
    public static function getGuideChapter($book_id, $number) {
        $list = Db::name('BookChapter')->where('book_id', '=', $book_id)->where('number', '<=', $number)->field('id,book_id,name,number')->select();
        if ($list) {
            foreach ($list as &$v) {
                $v['content'] = self::getChapterContent($v['book_id'], $v['number']);
            }
        }
        return $list;
    }

    //检查书籍章节连贯性
    public static function checkChapter($book_id) {
        $cur = parent::getById('Book', $book_id, 'id,type');
        if (!$cur) {
            res_return('检测书籍不存在');
        }
        $field = 'id,chapter';
        $list = Db::name('BookChapter')->where('book_id', '=', $book_id)->field('id,number')->select();
        $max = 0;
        $chapter = $error = [];
        if ($list) {
            foreach ($list as $v) {
                if (in_array($v['number'], $chapter)) {
                    $error[] = '第' . $v['number'] . '章:重复';
                } else {
                    $chapter[] = $v['number'];
                }
                if ($v['number'] > $max) {
                    $max = $v['number'];
                }
            }
        }
        if ($max > 0) {
            $path = env('root_path') . 'static/block/book/' . $book_id;
            for ($i = 1; $i <= $max; $i++) {
                if (!in_array($i, $chapter)) {
                    $error[] = '第' . $i . '章:缺失';
                } else {
                    if (in_array($cur['type'], [1, 2])) {
                        $file = $path . '/' . $i . '.html';
                        if (@is_file($file)) {
                            continue;
                        } else {
                            $error[] = '第' . $i . '章:未检测到内容';
                        }
                    }
                }
            }
        }
        return $error;
    }

    //更新广告
    public static function doneChapter($data) {
        if (array_key_exists('id', $data)) {
            $re = Db::name('ad_cate')->where('id', '=', $data['id'])->update($data);
        } else {
            $data['time'] = time();
            $re = Db::name('ad_cate')->insert($data);
        }
        if ($re !== false) {
            if (!empty($data['id'])) {
                cache('ad_cate_' . $data['id'], null);
            }
            res_return('ok');
        } else {
            res_return('操作失败，请重试');
        }
    }

    //删除广告
    public static function delete($id) {
        $flag = false;
        $res = Db::name('ad_cate')->where('id', '=', $id)->delete();
        if ($res) {
            cache('ad_cate' . $id, null);
            $flag = true;
        }
        return $flag;
    }

    //删除章节
    public static function delChapter($id, $book_id) {
        $book = parent::getById('Book', $book_id, 'id,type');
        if (empty($book)) {
            res_return('书籍信息异常');
        }
        $cur = parent::getById('BookChapter', $id, 'id,book_id,number,src,files');
        if (empty($cur)) {
            res_return('章节信息异常');
        }
        $config = [];
        if (in_array($book['type'], [1, 3])) {
            $config = myCache::getAliossCache();
            if (!$config) {
                res_return('您尚未配置阿里云参数');
            }
            myAliyunoss::$config = $config;
        }
        $res = false;
        $re = Db::name('BookChapter')->where('id', '=', $id)->delete();
        if ($re) {
            $res = true;
            $filename = env('root_path') . 'static/block/book/' . $book_id . '/' . $cur['number'] . '.html';
            if (@is_file($filename)) {
                @unlink($filename);
            }
            cache('book_' . $book_id, null);
            switch ($book['type']) {
                case 1:
                    $files = json_decode($cur['files'], true);
                    if ($files && is_array($files)) {
                        myAliyunoss::delFiles($files);
                    }
                    break;
                case 3:
                    if ($cur['src']) {
                        myAliyunoss::delFile($cur['src']);
                    }
                    break;
            }
        }
        return $res;
    }

    //删除广告位下所有广告
    public static function delAll($cid) {
        $res = false;
        $ad = Db::name('ad_cate')->where('cid', '=', $cid)->field('id,img')->find();
        if (empty($ad)) {
            res_return('广告信息异常');
        }

        $re = Db::name('ad_cate')->where('cid', '=', $cid)->delete();
        if ($re !== false) {
            $dir = env('root_path') . 'static/block/book/' . $cid;
            if (@is_dir($dir)) {
                self::delDirAndFile($dir);
            }
            cache('book_' . $cid, null);
            $res = true;
        }
        return $res;
    }

    //获取小说章节内容
    public static function getChapterContent($book_id, $number) {
        $filename = env('root_path') . 'static/block/book/' . $book_id . '/' . $number . '.html';
        $content = '';
        if (@is_file($filename)) {
            $content = file_get_contents($filename);
        }
        return $content;
    }

    //读取zip并保存小说章节
    public static function readAndSaveNovel($filename, $book_id) {
        $zipFile = env('root_path') . 'static/temp/zip/' . $filename;
        if (@is_file($zipFile)) {
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if (!$rs) {
                @unlink($zipFile);
                res_return('读取压缩文件失败');
            }
            $docnum = $zip->numFiles;
            $table = 'BookChapter';
            for ($i = 0; $i < $docnum; $i++) {
                $stateInfo = $zip->statIndex($i);
                if ($stateInfo['crc'] != 0 && $stateInfo['size'] > 0) {
                    $name = $zip->getNameIndex($i, \ZipArchive::FL_ENC_RAW);
                    $encode = mb_detect_encoding($name, ['ASCII', 'GB2312', 'GBK', 'UTF-8']);
                    $encode = $encode ? $encode : 'GBK';
                    $thisName = mb_convert_encoding($name, 'UTF-8', $encode);
                    $pathInfo = pathinfo($thisName);
                    if ($pathInfo && is_array($pathInfo)) {
                        if ($pathInfo['extension'] === 'txt') {
                            $title = $pathInfo['filename'];
                            $number = self::getFileNumber($title);
                            if ($number > 0) {
                                $repeat = Db::name($table)->where('book_id', '=', $book_id)->where('number', '=', $number)->value('id');
                                if (!$repeat) {

                                    $content = file_get_contents('zip://' . $zipFile . '#' . $stateInfo['name']);
                                    $encode = mb_detect_encoding($content, ['ASCII', 'GB2312', 'GBK', 'UTF-8']);
                                    $encode = $encode ? $encode : 'GBK';
                                    $content = mb_convert_encoding($content, 'UTF-8', $encode);
                                    $html = "<p>" . $content;
                                    $html = preg_replace('/\n|\r\n/', '</p><p>', $html);
                                    $data = [
                                        'book_id' => $book_id,
                                        'name' => $title,
                                        'number' => $number,
                                        'create_time' => time()
                                    ];
                                    $chapter_id = Db::name($table)->insertGetId($data);
                                    if ($chapter_id) {
                                        $block_res = saveBlock($html, $number, 'book/' . $book_id);
                                        if (!$block_res) {
                                            Db::name($table)->where('id', '=', $chapter_id)->delete();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $zip->close();
            @unlink($zipFile);
            cache('book_' . $book_id, null);
        } else {
            res_return('zip文件不存在');
        }
    }

    //读取并解析上传漫画章节
    public static function readAndSaveCartoon($filename, $book_id) {
        $zipFile = env('root_path') . 'static/temp/zip/' . $filename;
        if (@is_file($zipFile)) {
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if (!$rs) {
                @unlink($zipFile);
                res_return('读取压缩文件失败');
            }
            $config = myCache::getAliossCache();
            if (!$config) {
                $zip->close();
                @unlink($zipFile);
                res_return('您尚未配置阿里云参数');
            }
            $table = 'BookChapter';
            $docnum = $zip->numFiles;
            $res = $temp = $chapter = [];
            for ($i = 0; $i < $docnum; $i++) {
                $stateInfo = $zip->statIndex($i);
                if ($stateInfo['crc'] != 0 && $stateInfo['size'] > 0) {
                    $name = $zip->getNameIndex($i, \ZipArchive::FL_ENC_RAW);
                    $encode = mb_detect_encoding($name, ['ASCII', 'GB2312', 'GBK', 'UTF-8']);
                    $encode = $encode ? $encode : 'GBK';
                    $thisName = mb_convert_encoding($name, 'UTF-8', $encode);
                    $pathInfo = pathinfo($thisName);
                    if ($pathInfo && is_array($pathInfo)) {
                        if (is_numeric($pathInfo['filename'])) {
                            if (isset($pathInfo['dirname']) && $pathInfo['dirname']) {
                                if (in_array($pathInfo['dirname'], $temp)) {
                                    $number = array_search($pathInfo['dirname'], $temp);
                                } else {
                                    $dirInfo = self::getDirNumber($pathInfo['dirname']);
                                    $number = $dirInfo['number'];
                                }
                                if ($number > 0) {
                                    $fileKey = $pathInfo['filename'];
                                    if (!isset($chapter[$number])) {
                                        $chapter[$number] = [
                                            'title' => $dirInfo['title'],
                                            'number' => $number,
                                            'child' => [
                                                $fileKey => [
                                                    'name' => $stateInfo['name'],
                                                    'savename' => 'book/' . $book_id . '/' . $number . '/' . md5($fileKey) . '.' . $pathInfo['extension']
                                                ]
                                            ]
                                        ];
                                    } else {
                                        $chapter[$number]['child'][$fileKey] = [
                                            'name' => $stateInfo['name'],
                                            'savename' => 'book/' . $book_id . '/' . $number . '/' . md5($fileKey) . '.' . $pathInfo['extension']
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }
            if (!empty($chapter)) {
                $ossClient = new OssClient($config['accessKey'], $config['secretKey'], $config['url']);
                ksort($chapter);
                foreach ($chapter as $v) {
                    if ($v['child']) {
                        $child = $v['child'];
                        ksort($child);
                        $repeat = Db::name($table)->where('book_id', '=', $book_id)->where('number', '=', $v['number'])->value('id');
                        if (!$repeat) {
                            $data = [
                                'book_id' => $book_id,
                                'name' => $v['title'],
                                'src' => '',
                                'number' => $v['number'],
                                'files' => [],
                                'create_time' => time()
                            ];
                            $html = '';
                            foreach ($child as $val) {
                                $local_file = 'zip://' . $zipFile . '#' . $val['name'];
                                $content = file_get_contents($local_file);
                                if ($content) {
                                    try {
                                        $ossClient->putObject($config['bucket'], $val['savename'], $content);
                                        $url = 'https://' . $config['bucket'] . '.' . $config['url'] . '/' . $val['savename'];
                                        if (!$data['src']) {
                                            $data['src'] = $url;
                                        }
                                        $data['files'][] = $val['savename'];
                                        $html .= '<img src="' . $url . '" />';
                                    } catch (OssException $e) {
                                        try {
                                            $ossClient->putObject($config['bucket'], $val['savename'], $content);
                                            $url = 'https://' . $config['bucket'] . '.' . $config['url'] . '/' . $val['savename'];
                                            if (!$data['src']) {
                                                $data['src'] = $url;
                                            }
                                            $data['files'][] = $val['savename'];
                                            $html .= '<img src="' . $url . '" />';
                                        } catch (OssException $e) {
                                            $zip->close();
                                            @unlink($zipFile);
                                            res_return('章节：' . $v['title'] . '=>上传到阿里云OSS失败，原因：' . $e->getMessage());
                                        }
                                    }
                                } else {
                                    $zip->close();
                                    @unlink($zipFile);
                                    res_return('章节：' . $v['title'] . '=>读取失败');
                                }
                            }
                            $data['files'] = json_encode($data['files']);
                            $chapter_id = Db::name($table)->insertGetId($data);
                            if ($chapter_id) {
                                $block_res = saveBlock($html, $v['number'], 'book/' . $book_id);
                                if (!$block_res) {
                                    Db::name($table)->where('id', '=', $chapter_id)->delete();
                                }
                            }
                        }
                    }
                }
            }
            $zip->close();
            @unlink($zipFile);
            cache('book_' . $book_id, null);
        } else {
            res_return('zip文件不存在');
        }
    }

    //上传听书
    public static function readAndSaveMusic($filename, $book_id) {
        $zipFile = env('root_path') . 'static/temp/zip/' . $filename;
        if (@is_file($zipFile)) {
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if (!$rs) {
                @unlink($zipFile);
                res_return('读取压缩文件失败');
            }
            $config = myCache::getAliossCache();
            if (!$config) {
                $zip->close();
                @unlink($zipFile);
                res_return('您尚未配置阿里云参数');
            }
            $docnum = $zip->numFiles;
            $table = 'BookChapter';
            $config = myCache::getAliossCache();
            if (!$config) {
                $zip->close();
                @unlink($zipFile);
                res_return('您尚未配置阿里云参数');
            }
            myAliyunoss::$config = $config;
            for ($i = 0; $i < $docnum; $i++) {
                $stateInfo = $zip->statIndex($i);
                if ($stateInfo['crc'] != 0 && $stateInfo['size'] > 0) {
                    $name = $zip->getNameIndex($i, \ZipArchive::FL_ENC_RAW);
                    $encode = mb_detect_encoding($name, ['ASCII', 'GB2312', 'GBK', 'UTF-8']);
                    $encode = $encode ? $encode : 'GBK';
                    $thisName = mb_convert_encoding($name, 'UTF-8', $encode);
                    $pathInfo = pathinfo($thisName);
                    if ($pathInfo && is_array($pathInfo)) {
                        $ext = strtolower($pathInfo['extension']);
                        if (in_array($ext, ['mp3', 'wma'])) {
                            $title = $pathInfo['filename'];
                            $number = self::getFileNumber($title);
                            if ($number > 0) {
                                $repeat = Db::name($table)->where('book_id', '=', $book_id)->where('number', '=', $number)->value('id');
                                if (!$repeat) {
                                    $content = file_get_contents('zip://' . $zipFile . '#' . $stateInfo['name']);
                                    $fileKey = $pathInfo['filename'];
                                    $savename = 'book/' . $book_id . '/' . $number . '/' . md5($fileKey) . '.' . $pathInfo['extension'];
                                    $up_url = myAliyunoss::putObject($savename, $content);
                                    if ($up_url) {
                                        $data = [
                                            'book_id' => $book_id,
                                            'name' => $title,
                                            'src' => $up_url,
                                            'number' => $number,
                                            'files' => json_encode([$savename]),
                                            'create_time' => time()
                                        ];
                                        Db::name($table)->insert($data);
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $zip->close();
            @unlink($zipFile);
            cache('book_' . $book_id, null);
        } else {
            res_return('zip文件不存在');
        }
    }

    //解析路径中文件章节
    private static function getDirNumber($dir) {
        $arr = explode('/', $dir);
        $title = end($arr);
        $number = self::getFileNumber($title);
        return ['number' => $number, 'title' => $title];
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
