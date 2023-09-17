<?php

namespace app\admin\controller;

use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\myAliyunoss;
use app\common\model\myCache;
use function GuzzleHttp\json_encode;

class Upload extends Common {

    //裁剪图片
    public function crop() {
        if ($this->request->isAjax()) {
            $post = myRequest::post('img');
            $image = $post['img'];
            if (!empty($image)) {

                $config = myCache::getAliossCache();
                $config['type'] = isset($config['type']) ? $config['type'] : 0;
                //type=0上传到阿里oss, 1上传到本地
                if ($config['type']) {
                    $this->localUpImg();
                    res_return();
                    //   res_return('您尚未配置阿里云oss参数');
                }
                myAliyunoss::$config = $config;
                $content = base64_decode($image);
                $name = md5(microtime() . mt_rand(10000, 99999)) . '.jpg';
                $savename = 'images/' . date('Ymd') . '/' . $name;
                $url = myAliyunoss::putObject($savename, $content);
                if ($url) {
                    res_return(['url' => $url]);
                } else {
                    res_return('上传失败,请重试');
                }
            } else {
                res_return('未检测到上传文件');
            }
        } else {
            $get = myRequest::get('crop_size');
            $crop_size = $get['crop_size'];
            $reg = '/^[\d]{1,}[x][\d]{1,}$/';
            if (!preg_match($reg, $crop_size)) {
                res_return('裁剪尺寸格式错误');
            }
            $arr = explode('x', $crop_size);
            $config = ['width' => $arr[0], 'height' => $arr[1], 'ratio' => ($arr[0] / $arr[1])];
            $this->assign('config', $config);
            return $this->fetch();
        }
    }

    //没阿里云配置时，使用本地上传
    private function localUpImg() {
        $post = myRequest::post('img');
        $image = $post['img'];
        if (!empty($image)) {
            $imageName = "25220_" . date("His", time()) . "_" . rand(100000, 999999) . '.png';
            if (strstr($image, ",")) {
                $image = explode(',', $image);
                $image = $image[1];
            }
            $path = "./uploads/img/" . date("Ymd", time());
            if (!is_dir($path)) { //判断目录是否存在 不存在就创建
                mkdir($path, 0777, true);
            }
            $imageSrc = $path . "/" . $imageName; //图片地址
            $imageSrc1 = "/uploads/img/" . date("Ymd", time()) . "/" . $imageName; //图片名字
            $rs = file_put_contents($imageSrc, base64_decode($image)); //返回的是字节数
            if (!$rs) {
                res_return('上传失败,请重试');
            } else {
                res_return(['url' => 'http://' . $_SERVER['HTTP_HOST'] . $imageSrc1]);
            }
        } else {
            res_return('未检测到上传文件');
        }
    }

    //处理图片上传****
    public function doUploadEditorImg() {
        $config = myCache::getAliossCache();
        if (!$config) {
            echo json_encode(['code' => 1, 'msg' => '您尚未配置阿里云oss参数']);
            exit;
        }
        myAliyunoss::$config = $config;
        $file = request()->file('file');
        $validate = array(
            'size' => 1024 * 1024 * 1,
            'ext' => 'jpg,jpeg,png,gif,bmp'
        );
        $date = date('Ymd');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path') . 'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
                ->validate($validate)
                ->move($path, $name);
        if ($info) {
            $filename = $path . '/' . $name;
            $savename = 'images/' . $date . '/' . $name;
            $url = myAliyunoss::putLocalFile($savename, $filename);
            @unlink($filename);
            if ($url) {
                echo json_encode(['code' => 0, 'msg' => 'ok', 'data' => ['src' => $url, 'title' => '']]);
            } else {
                echo json_encode(['code' => 1, 'msg' => '上传失败,请重试']);
            }
        } else {
            echo json_encode(['code' => 1, 'msg' => $file->getError()]);
        }
    }

    //处理图片上传
    public function doUploadImg() {
        $config = myCache::getAliossCache();
        if (!$config) {
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $file = request()->file('file');
        $validate = array(
            'size' => 1024 * 1024 * 1,
            'ext' => 'jpg,jpeg,png'
        );
        $date = date('Ymd');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path') . 'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
                ->validate($validate)
                ->move($path, $name);
        if ($info) {
            $filename = $path . '/' . $name;
            $savename = 'images/' . $date . '/' . $name;
            $url = myAliyunoss::putLocalFile($savename, $filename);
            @unlink($filename);
            if ($url) {
                res_return(['url' => $url]);
            } else {
                res_return('上传失败,请重试');
            }
        } else {
            res_return($file->getError());
        }
    }

    //处理图标上传
    public function doUploadIcon() {
        $config = myCache::getAliossCache();
        $config['type'] = isset($config['type']) ? $config['type'] : 0;
        if ($config['type']) {
            $this->localUpIcon();
            res_return();
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $file = request()->file('file');
        $validate = array(
            'size' => 1024 * 100,
            'ext' => 'jpg,jpeg,png'
        );
        $date = date('Ymd');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path') . 'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
                ->validate($validate)
                ->move($path, $name);
        if ($info) {
            $filename = $path . '/' . $name;
            $savename = 'images/' . $date . '/' . $name;
            $url = myAliyunoss::putLocalFile($savename, $filename);
            @unlink($filename);
            if ($url) {
                res_return(['url' => $url]);
            } else {
                res_return('上传失败,请重试');
            }
        } else {
            res_return($file->getError());
        }
    }

//未配置阿里oss时上传到此处
    private function localUpIcon() {
        $file = request()->file('file');
        $validate = array(
            'size' => 1024 * 100,
            'ext' => 'jpg,jpeg,png'
        );
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path') . 'static/temp/images';
        $name = self::createOnlyName($path, $ext);
        $info = $file
                ->validate($validate)
                ->move($path, $name);
        if ($info) {
            $filename = $path . '/' . $name;
            $imageName = "25220_" . date("His") . "_" . rand(100000, 999999) . '.png';
            $path = "./uploads/icon/" . date("Ymd");
            if (!is_dir($path)) { //判断目录是否存在 不存在就创建
                mkdir($path, 0777, true);
            }
            $imageSrc = $path . "/" . $imageName; //图片地址
            $imageSrc1 = "/uploads/icon/" . date("Ymd") . "/" . $imageName; //图片名字
            $new_file = file_get_contents($filename);
            file_put_contents($imageSrc, $new_file);
            @unlink($filename);
            $url = 'http://' . $_SERVER['HTTP_HOST'] . $imageSrc1;
            if ($url) {
                res_return(['url' => $url]);
            } else {
                res_return('上传失败,请重试');
            }
        } else {
            res_return($file->getError());
        }
    }

    //上传分集zip
    public function doUploadZip() {
        set_time_limit(0);
        $file = request()->file('file');
        $validate = array(
            'size' => 1024 * 1024 * 20,
            'ext' => 'zip'
        );
        $date = date('Ymd');
        $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
        $path = env('root_path') . 'static/temp/zip';
        $name = self::createOnlyName($path, $ext);
        $info = $file
                ->validate($validate)
                ->move($path, $name);
        if ($info) {
            res_return(['filename' => $name]);
        } else {
            res_return($file->getError());
        }
    }

    //处理分片上传文件
    public function doUploadFile() {
        $post = myRequest::post('chunk,chunks,name');
        $cur_chunk = $post['chunk'] + 1;
        $total = $post['chunks'];
        $server_tmp_name = $_FILES['file']['tmp_name'];
        $path = env('root_path') . 'static/temp/zip';
        if (false === file_exists($path)) {
            mkdir($path);
        }
        if ($total == 1) {
            $file = request()->file('file');
            $validate = array(
                'size' => 1024 * 1024,
                'ext' => 'zip'
            );
            $date = date('Ymd');
            $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $name = self::createOnlyName($path, $ext);
            $info = $file
                    ->validate($validate)
                    ->move($path, $name);
            if ($info) {
                res_return(['filename' => $name]);
            } else {
                res_return($file->getError());
            }
        } else {
            $name = $post['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext != 'zip') {
                echo json_encode(['code' => 0, 'msg' => '文件格式不正确']);
                exit;
            }
            $file_name = md5($name) . '.zip';
            $path .= '/' . $file_name;
            if ($cur_chunk == 1) {
                if (@is_file($path)) {
                    @unlink($path);
                }
                move_uploaded_file($server_tmp_name, $path);
                res_return('ok');
            } else {
                if (!@is_file($path)) {
                    echo json_encode(['code' => 0, 'msg' => '文件格式不正确']);
                    exit;
                }
                $blob = file_get_contents($server_tmp_name);
                @unlink($server_tmp_name);
                file_put_contents($path, $blob, FILE_APPEND);
                if ($cur_chunk == $total) {
                    res_return(['filename' => $file_name]);
                } else {
                    res_return('ok');
                }
            }
        }
    }

    //处理分片上传视频
    public function doUploadVideo() {
        $config = myCache::getAliossCache();
        //type=0上传到阿里oss, 1上传到本地
        $config['type'] = isset($config['type']) ? $config['type'] : 0;
        if ($config['type']) {
            //若没配置阿里云oss则上传本地
            $this->localUpVideo();
            res_return();
            res_return('您尚未配置阿里云oss参数');
        }
        myAliyunoss::$config = $config;
        $post = myRequest::post('chunk,chunks,name');

        $cur_chunk = $post['chunk'] + 1; //4
        $total = $post['chunks'];
        $path = env('root_path') . 'static/temp/video';
        if ($total == 1) {
            $file = request()->file('file');
            $validate = array(
                'size' => 1024 * 500,
                'ext' => 'mp4'
            );
            $date = date('Ymd');
            $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $name = self::createOnlyName($path, $ext);
            $info = $file
                    ->validate($validate)
                    ->move($path, $name);
            if ($info) {
                $savename = 'video/' . date('Ymd') . '/' . md5(microtime() . mt_rand(100000, 9999999)) . '.' . $ext;
                $filename = $path . '/' . $name;
                $url = myAliyunoss::putLocalFile($savename, $filename);
                @unlink($filename);
                if ($url) {
                    res_return(['url' => $url, 'file_key' => $savename]);
                } else {
                    res_return('上传失败，请重试');
                }
            } else {
                res_return($file->getError());
            }
        } else {
            $name = $post['name'];
            $server_tmp_name = $_FILES['file']['tmp_name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext != 'mp4') {
                res_return('文件格式不正确');
            }
            $session_name = md5($name);
            $upsession = session($session_name);
            $savename = 'video/' . date('Ymd') . '/' . md5($name) . '.' . $ext;

            if ($cur_chunk == 1) {
                if ($upsession) {
                    if (isset($upsession['position'])) {
                        res_return('上传异常1');
                    }
                }
                $upsession = [
                    'object' => $savename,
                    'is_file' => false
                ];
                $exits = myAliyunoss::fileExits($savename);

                $upsession['is_file'] = $exits;
                $position = 0;
            } else {
                if (!$upsession || $upsession['object'] != $savename) {
                    res_return('上传异常2');
                }
                if ($upsession['is_file'] === false) {
                    $position = $upsession['position'];
                }
            }
            if ($upsession['is_file'] === false) {
                $content = file_get_contents($server_tmp_name);

                @unlink($server_tmp_name);
                $res = myAliyunoss::appendFiles($savename, $content, $position);
                if ($total == $cur_chunk) {
                    session($session_name, null);
                    $url = 'https://' . $config['bucket'] . '.' . $config['url'] . '/' . $savename;
                    res_return(['url' => $url, 'file_key' => $savename]);
                } else {
                    $upsession['position'] = $res;
                    session($session_name, $upsession);
                    res_return('ok');
                }
            } else {
                @unlink($server_tmp_name);
                session($session_name, $upsession);
                $url = 'https://' . $config['bucket'] . '.' . $config['url'] . '/' . $savename;
                res_return(['url' => $url, 'file_key' => $savename]);
            }
        }
    }

    //处理分片上传文件
    public function localUpVideo() {
        $post = myRequest::post('chunk,chunks,name');
        $cur_chunk = $post['chunk'] + 1;
        $total = $post['chunks'];
        $server_tmp_name = $_FILES['file']['tmp_name'];
        $date = date('Ymd');
        $path = './uploads/video/' . $date;
        $read_path = 'http://' . $_SERVER['HTTP_HOST'] . '/uploads/video/' . $date; //保存文件路径
        if (false === file_exists($path)) {
            mkdir($path, 0777, true);
        }
        if ($total == 1) {
            $file = request()->file('file');
            $validate = array(
                'size' => 1024 * 1024,
                'ext' => 'mp4'
            );
            $ext = strtolower(pathinfo($file->getInfo('name'), PATHINFO_EXTENSION));
            $name = self::createOnlyName($path, $ext);
            $info = $file
                    ->validate($validate)
                    ->move($path, $name);
            if ($info) {
                res_return(['filename' => $name]);
            } else {
                res_return($file->getError());
            }
        } else {
            $name = $post['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if ($ext != 'mp4') {
                echo json_encode(['code' => 0, 'msg' => '文件格式不正确']);
                exit;
            }
            $file_name = md5($name) . '.mp4';
            $path .= '/' . $file_name;
            if ($cur_chunk == 1) {
                if (@is_file($path)) {
                    @unlink($path);
                }
                move_uploaded_file($server_tmp_name, $path);
                res_return('ok');
            } else {
                if (!@is_file($path)) {
                    echo json_encode(['code' => 0, 'msg' => '文件格式不正确']);
                    exit;
                }
                $blob = file_get_contents($server_tmp_name);
                @unlink($server_tmp_name);
                file_put_contents($path, $blob, FILE_APPEND);
                if ($cur_chunk == $total) {
                    res_return(['url' => $read_path . '/' . $file_name, 'file_key' => $file_name]);
                } else {
                    res_return('ok');
                }
            }
        }
    }

    //创建唯一文件名
    private function createOnlyName($path, $ext) {
        $name = md5(microtime() . mt_rand(10000, 99999)) . '.' . $ext;
        $file = $path . '/' . $name;
        if (@is_file($file)) {
            self::createOnlyName($path, $ext);
        } else {
            return $name;
        }
    }

}
