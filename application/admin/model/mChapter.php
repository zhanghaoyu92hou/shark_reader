<?php

namespace app\admin\model;

use app\admin\model\Common;
use think\Db;
use OSS\OssClient;
use OSS\Core\OssException;
use app\common\model\myCache;
use app\common\model\myAliyunoss;

class mChapter extends Common {

    public static $chapter = [
        'id' => ["require|number|gt:0", ["require" => "分集主键参数错误", 'number' => '分集主键参数错误', "gt" => "分集主键参数错误"]],
        'book_id' => ["require|number|gt:0", ["require" => "书籍参数错误", 'number' => '书籍参数错误', "gt" => "书籍参数错误"]],
        'content' => ["require", ['require' => '请输入分集内容']],
        'number' => ["require|number|gt:0", ["require" => "章节参数错误", 'number' => '章节参数错误', "gt" => "章节参数错误"]],
        'name' => ["require|max:50", ["require" => "请输入章节名称", 'max' => '章节名称最多支持50个字符']],
        'filename' => ["require|length:36", ["require" => "文件名异常", 'max' => '文件名错误']],
    ];

    //获取分集列表
    public static function getChapterPageList($where, $pages) {
        $list = Db::name('BookChapter')->where($where)->page($pages['page'], $pages['limit'])->order('number', 'desc')->select();
        $count = 0;
        if ($list) {
            foreach ($list as &$v) {
                $v['do_url'] = my_url('doChapter', ['id' => $v['id']]);
                $v['show_url'] = my_url('showInfo', ['id' => $v['id']]);
            }
            $count = Db::name('BookChapter')->where($where)->count();
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

    //更新章节
    public static function doneChapter($data) {
        $content = '';
        if (array_key_exists('content', $data)) {
            $content = $data['content'];
            unset($data['content']);
        }
        $res = false;
        if (array_key_exists('id', $data)) {
            $re = Db::name('BookChapter')->where('id', '=', $data['id'])->update($data);
        } else {
            $repeat = Db::name('BookChapter')->where('book_id', '=', $data['book_id'])->where('number', '=', $data['number'])->value('id');
            if ($repeat) {
                res_return('该章节已存在');
            }
            $data['create_time'] = time();
            $re = Db::name('BookChapter')->insert($data);
        }
        if ($re !== false) {
            if ($content) {
                $dir = 'book/' . $data['book_id'];
                saveBlock($content, $data['number'], $dir);
            }
            cache('book_' . $data['book_id'], null);
            res_return('ok');
        } else {
            res_return('操作失败，请重试');
        }
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
            $config['type'] = isset($config['type']) ? $config['type'] : 0;
            if ($config['type']) {
                self::localDelChapter($id, $book_id);
                res_return();
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

    //若没配置阿里云oss则删除本地章节
    public static function localDelChapter($id, $book_id) {
        $book = parent::getById('Book', $book_id, 'id,type');
        if (empty($book)) {
            res_return('书籍信息异常');
        }
        $cur = parent::getById('BookChapter', $id, 'id,book_id,number,src,files');

        if (empty($cur)) {
            res_return('章节信息异常');
        }
//        $config = [];
//        if (in_array($book['type'], [1, 3])) {
//            $config = myCache::getAliossCache();
//            if (!$config) {
//                res_return('您尚未配置阿里云参数');
//            }
//            myAliyunoss::$config = $config;
//        }
        $res = false;
        $re = Db::name('BookChapter')->where('id', '=', $id)->delete();
        if ($re) {
            $res = true;
            $filename = env('root_path') . 'static/block/book/' . $book_id . '/' . $cur['number'] . '.html';
            if (@is_file($filename)) {
                @unlink($filename);
            }
            cache('book_' . $book_id, null);
            $files = json_decode($cur['files'], true);
            switch ($book['type']) {
                case 1:
                    if ($files && is_array($files)) {
                        foreach ($files as $v) {
                            @unlink('./uploads/cartoon/' . $book_id . '/' . $cur['number'] . '/' . $v);
                        }
                    }
                    break;
                case 3:
                    if ($cur['src']) {
                        @unlink('./uploads/cartoon/' . $book_id . '/' . $cur['number'] . '/' . $cur['src']);
                    }
                    break;
            }
        }
        return $res;
    }

    //删除所有章节
    public static function delAllChapter($book_id) {
        $res = false;
        $book = parent::getById('Book', $book_id, 'id,type');
        if (empty($book)) {
            res_return('书籍信息异常');
        }
        $list = $config = [];
        if (in_array($book['type'], [1, 3])) {
            $list = Db::name('BookChapter')->where('book_id', '=', $book_id)->field('id,src,files')->select();
            $config = myCache::getAliossCache();
            $config['type'] = isset($config['type']) ? $config['type'] : 0;
            if ($config['type']) {
                self::localDelAllChapter($book_id);
                res_return();
                res_return('您尚未配置阿里云参数');
            }
            myAliyunoss::$config = $config;
        }
        $re = Db::name('BookChapter')->where('book_id', '=', $book_id)->delete();
        if ($re !== false) {
            $dir = env('root_path') . 'static/block/book/' . $book_id;
            if (@is_dir($dir)) {
                self::delDirAndFile($dir);
            }
            if (in_array($book['type'], [1, 3])) {
                if ($list) {
                    $files = [];
                    foreach ($list as $v) {
                        switch ($book['type']) {
                            case 1:
                                if ($v['files']) {
                                    $cur_fiels = json_decode($v['files'], true);
                                    if ($cur_fiels && is_array($cur_fiels)) {
                                        $files = array_merge($files, $cur_fiels);
                                    }
                                }
                                break;
                            case 3:
                                if ($v['src']) {
                                    $files[] = $v['src'];
                                }
                                break;
                        }
                    }
                    if ($files) {
                        myAliyunoss::delFiles($files);
                    }
                }
            }
            cache('book_' . $book_id, null);
            $res = true;
        }
        return $res;
    }

    //未设置阿里oss时本地删除所有章节
    public static function localDelAllChapter($book_id) {
        $res = false;
        $book = parent::getById('Book', $book_id, 'id,type');
        if (empty($book)) {
            res_return('书籍信息异常');
        }
        $list = $config = [];
        if (in_array($book['type'], [1, 3])) {
            $list = Db::name('BookChapter')->where('book_id', '=', $book_id)->field('id,src,files')->select();
        }
        $re = Db::name('BookChapter')->where('book_id', '=', $book_id)->delete();
        if ($re !== false) {
            $dir = env('root_path') . 'static/block/book/' . $book_id;
            if (@is_dir($dir)) {
                self::delDirAndFile($dir);
            }
            if (in_array($book['type'], [1, 3])) {
                if ($list) {
                    $files = [];
                    foreach ($list as $v) {
                        switch ($book['type']) {
                            case 1:
                                if ($v['files']) {
                                    $cur_fiels = json_decode($v['files'], true);
                                    if ($cur_fiels && is_array($cur_fiels)) {
                                        $files = array_merge($files, $cur_fiels);
                                    }
                                }
                                break;
                            case 3:
                                if ($v['src']) {
                                    $files[] = $v['src'];
                                }
                                break;
                        }
                    }
                    if ($files) {
                        self::delDirAndFile('./uploads/cartoon/' . $book_id);
                        //     @unlink();
                    }
                }
            }
            cache('book_' . $book_id, null);
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
            $config = myCache::getAliossCache();
            $config['type'] = isset($config['type']) ? $config['type'] : 0;
            if ($config['type'] == '0') {
                //    $zip->close();
                // @unlink($zipFile);
                //尚未配置阿里云时调用本地
                self::localUpCartoon($filename, $book_id);
                res_return();
                res_return('您尚未配置阿里云参数');
            }
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if (!$rs) {
                @unlink($zipFile);
                res_return('读取压缩文件失败');
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
                        if (isset($pathInfo['filename'])) {
                            if (isset($pathInfo['dirname']) && $pathInfo['dirname']) {
                                if (in_array($pathInfo['dirname'], $temp)) {
                                    $number = array_search($pathInfo['dirname'], $temp);
                                } else {
                                    //$dirInfo = self::getDirNumber($pathInfo['dirname']);
                                    //$number = $dirInfo['number'];
                                    $number = self::getFileNumber($pathInfo['filename']);
                                    $dirInfo = [
                                        'number'=>$number,
                                        'title'=>str_replace('.txt','',$pathInfo['filename'])
                                    ];
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
            

            if ($config['type'] == '0' && !empty($chapter)) {
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
            }else{
                
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
                                    
                                    $root_rel = '/uploads/cartoon/'.date('Y/m/d');
                                    $root = $_SERVER['DOCUMENT_ROOT'].$root_rel;
                                    
                                    $val_arr = explode('/',$val['savename']);
                                    $savename = end($val_arr);

                                    if(!file_exists($root)){
                                        mkdir($root,0755,true);
                                    }
                                    
                                    $savename_abs = $root.'/'.$savename;
                                    
                                    file_put_contents($savename_abs,$content);
                                    
                                    $url = $root_rel . '/' . $savename;
                                        if (!$data['src']) {
                                            $data['src'] = $url;
                                        }
                                        $data['files'][] = $savename;
                                        
                                        $lines = file($savename_abs);
                                        foreach($lines as $line_num => $line){
                                            if (empty($line)) {
                                                continue;
                                            }
                                            $html .= '<img src="' . $line . '" />';
                                        }
                                        
                                        //$html .= '<img src="' . $url . '" />';

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

//当未设置阿里云oss时，则存储到本地
    public static function localUpCartoon($filename, $book_id) {
        $zipFile = env('root_path') . 'static/temp/zip/' . $filename;
        if (@is_file($zipFile)) {
            $zip = new \ZipArchive();
            $rs = $zip->open($zipFile);
            if (!$rs) {
                @unlink($zipFile);
                res_return('读取压缩文件失败');
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

//                      echo '<pre>';
//            print_r($pathInfo);exit;

                    if ($pathInfo && is_array($pathInfo)) {
                        if (isset($pathInfo['filename'])) {

                            if (isset($pathInfo['dirname']) && $pathInfo['dirname']) {
                                if (in_array($pathInfo['dirname'], $temp)) {
                                    //   echo 111;exit;
                                    $number = array_search($pathInfo['dirname'], $temp);
                                } else {
                                    //        echo 222;exit;
                                    $dirInfo = self::getDirNumber($pathInfo['dirname']);
                                    $number = $dirInfo['number'];
                                }
//                                echo '<pre>';
//                                print_r($number);
//                                exit;
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
                //   $ossClient = new OssClient($config['accessKey'], $config['secretKey'], $config['url']);
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

                            $file_path = './uploads/cartoon/' . $book_id . '/' . $v['number'];
                            $file_name = '/uploads/cartoon/' . $book_id . '/' . $v['number'];
                            foreach ($child as $val) {
                                $local_file = 'zip://' . $zipFile . '#' . $val['name'];

                                $savename = explode('/', $val['savename']);
                                $savename = end($savename);
                                $content = file_get_contents($local_file);
                                if (!is_dir($file_path)) { //判断目录是否存在 不存在就创建
                                    mkdir($file_path, 0777, true);
                                }
                                if ($content) {
                                    try {
                                        //     $ossClient->putObject($config['bucket'], $val['savename'], $content);
                                        file_put_contents($file_path . '/' . $savename, $content);
                                        $url = 'http://' . $_SERVER['HTTP_HOST'] . $file_name . '/' . $savename;
                                        if (!$data['src']) {
                                            $data['src'] = $url;
                                        }
                                        $data['files'][] = $savename;
                                        $html .= '<img src="' . $url . '" />';
                                    } catch (OssException $e) {
                                        try {
//                                            $ossClient->putObject($config['bucket'], $val['savename'], $content);
//                                            $url = 'https://' . $config['bucket'] . '.' . $config['url'] . '/' . $val['savename'];
                                            file_put_contents($file_path . '/' . $savename, $content);
                                            $url = 'http://' . $_SERVER['HTTP_HOST'] . $file_name . '/' . $savename;
                                            if (!$data['src']) {
                                                $data['src'] = $url;
                                            }
                                            $data['files'][] = $savename;
                                            $html .= '<img src="' . $url . '" />';
                                        } catch (OssException $e) {
                                            $zip->close();
                                            @unlink($zipFile);
                                            res_return('章节：' . $v['title'] . '=>上传失败，原因：' . $e->getMessage());
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

            if ($config['type'] == '0' && empty($config['accessKey'])) {
                $zip->close();
                @unlink($zipFile);
                res_return('您尚未配置阿里云参数');
            }
            $docnum = $zip->numFiles;
            $table = 'BookChapter';
            $config = myCache::getAliossCache();
            if ($config['type'] == '0' && empty($config['accessKey'])) {
                $zip->close();
                @unlink($zipFile);
                res_return('您尚未配置阿里云参数');
            }
            
            if($config['type'] == '0'){
                myAliyunoss::$config = $config;
            }
            
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
                                    
                                    if($config['type'] == '0'){
                                        $up_url = myAliyunoss::putObject($savename, $content);
                                    }else {
                                        
                                        $savename = md5($fileKey) . '.' . $pathInfo['extension'];
                                        
                                        $savename_path = '/uploads/music/'.date('Y/m/d');
                                        $root = $_SERVER['DOCUMENT_ROOT'].$savename_path;
                                        if(!file_exists($root)){
                                            mkdir($root,0755,true);
                                        }
                                        $savename_abs = $root.'/'.$savename;
                                        file_put_contents($savename_abs,$content);
                                        $savename_path .= '/'.$savename;
                                        $up_url = $savename_path;
                            
                                    }
                                    
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
        
        if(is_numeric($title)){
            $number = (int)$title;
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
