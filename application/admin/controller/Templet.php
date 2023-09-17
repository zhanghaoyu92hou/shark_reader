<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\admin\model\mConfig;
use app\common\model\myRequest;
use app\common\model\myValidate;

class Templet extends Common{
    
    //网站板块管理
    public function block(){
        $key = 'web_block';
        $defaultData = [
        	['name'=>'漫画','key'=>'cartoon','url'=>'/index/Book/cartoon.html','is_on'=>1],
            ['name'=>'小说','key'=>'novel','url'=>'/index/Book/novel.html','is_on'=>1],
            ['name'=>'听书','key'=>'music','url'=>'/index/Book/music.html','is_on'=>1],
            ['name'=>'视频','key'=>'video','url'=>'/index/Video/index.html','is_on'=>1],
            ['name'=>'商城','key'=>'shop','url'=>'/index/Product/index.html','is_on'=>1]
        ];
        if($this->request->isAjax()){
            $post = myRequest::post('novel,cartoon,music,video,shop');
            foreach ($defaultData as &$v){
                if(!$post[$v['key']]){
                    $v['is_on'] = 0;
                }
            }
            $re = mConfig::saveConfig($key,$defaultData);
            if($re){
                cache($key,$defaultData);
                res_return();
            }else{
                res_return('保存失败');
            }
        }else{
            $cur = mConfig::getConfig($key);
            if(!$cur){
                $cur = $defaultData;
                $re = mConfig::addConfig($key,$cur);
                if(!$re){
                    res_return('初始化数据失败，请重试');
                }
            }
            $this->assign('cur',$cur);
            return $this->fetch();
        }
    }
    
    //底部导航
    public function footer(){
        $key = 'web_footer';
        if($this->request->isAjax()){
            $data = myValidate::getData(mConfig::$icon_rules, 'src,text,link');
            if(!$data['src']){
                res_return('检测到未上传图标');
            }
            $config = [];
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
            $res = mConfig::saveConfig($key,$config);
            if($res){
            	$this->assign('list',$config);
            	$html = $this->fetch('block/footer');
            	saveBlock($html,'common_footer','other');
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
                'title' => '网站通用底部导航配置',
                'cur' => $cur,
            ];
            $this->assign($variable);
            return $this->fetch('public/icon');
        }
    }
    
    //h5模版
    public function h5(){
        if($this->request->isAjax()){
            $list = [
                ['summary'=>'底部二维码下方通用描述','key'=>'footer_h5'],
                ['summary'=>'充值页面下方通用描述','key'=>'charge_h5'],
                ['summary'=>'二维码登陆页面下方描述','key'=>'qrcode_login_h5'],
                ['summary'=>'手机号登陆页面下方描述','key'=>'phone_login_h5'],
            ];
            foreach ($list as &$v){
                $v['name'] = self::getH5Name($v['key']);
                $v['do_url'] = my_url('doH5',['key'=>$v['key']]);
            }
            res_return($list);
        }else{
            
            return $this->fetch();
        }
    }
    
    //更新H5模版
    public function doH5(){
    	$keys = ['footer_h5','charge_h5','qrcode_login_h5','phone_login_h5'];
        if($this->request->isAjax()){
            $post = myRequest::post('key,content');
            if(!in_array($post['key'], $keys)){
                res_return('未指定该模版');
            }
            saveBlock($post['content'], $post['key'],'other');
            res_return();
        }else{
            $get = myRequest::get('key');
            if(!in_array($get['key'], $keys)){
                res_return('未指定该模版');
            }
            $cur = [
                'key' => $get['key'],
                'name' => self::getH5Name($get['key']),
                'content' => getBlockContent($get['key'],'other')
            ];
            $variable = [
                'cur' => [
                    'key' => $get['key'],
                    'name' => self::getH5Name($get['key']),
                    'content' => getBlockContent($get['key'],'other')
                ],
                'backUrl' => my_url('H5')
            ];
            $this->assign($variable);
            return $this->fetch('doH5');
        }
    }
    
    //获取模版名称
    private function getH5Name($key){
        $name = '未知';
        switch ($key){
            case 'footer_h5':$name='通用底部描述';break;
            case 'charge_h5':$name='充值页面下方描述';break;
            case 'qrcode_login_h5':$name='二维码登陆描述';break;
            case 'phone_login_h5':$name='手机号登陆描述';break;
        }
        return $name;
    }
}