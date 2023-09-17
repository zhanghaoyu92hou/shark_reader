<?php
namespace app\admin\controller;
use app\admin\controller\Common;
use app\common\model\myRequest;
use app\common\model\mySearch;
use app\admin\model\mMaterial;
use app\admin\model\mSpread;
use Endroid\QrCode\QrCode;

class Spread extends Common{
    
    //推广链接列表
    public function index(){
        if($this->request->isAjax()){
            $config = [
                'default' => [['a.status','=',1],['a.channel_id','=',0]],
                'eq' => 'type:a.type',
                'like' => 'keyword:a.name'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mSpread::getSpreadList($where, $pages);
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            $get = myRequest::get('from');
            $this->assign('from',$get['from']);
            return $this->fetch();
        }
    }
    
    
    //生成推广链接
    public function createLink(){
        if($this->request->isAjax()){
            $field = 'book_id,name,chapter_id,is_sub,number,remark';
            mSpread::doneSpread($field);
        }else{
            $book_id = myRequest::getId('书籍','book_id');
            $book = mSpread::getById('Book',$book_id,'id,name,type');
            if(!in_array($book['type'],[1,2])){
                res_return('仅漫画和小说可生成推广链接');
            }
            $chapter = mSpread::getTenChapter($book_id);
            if(!$chapter){
                res_return('该书籍未添加章节');
            }
            $book['chapter'] = $chapter;
            $field = 'id,name,chapter_id,is_sub:1,number:5,remark';
            $radio = [
                'name' => 'is_sub',
                'filter' => 'is_sub',
                'option' => [['val'=>1,'text'=>'是','default'=>1],['val'=>2,'text'=>'否','default'=>0]]
            ];
            $cur = mSpread::buildArr($field);
            $variable = [
                'book' => $book,
                'radio' => $radio,
                'cur' => $cur
            ];
            $this->assign($variable);
            return $this->fetch('createLink');
        }
    }
    
    //编辑链接
    public function doLink(){
        if($this->request->isAjax()){
            $field = 'id,book_id,name,is_sub,number,remark,chapter_id';
            mSpread::doneSpread($field);
        }else{
            $id = myRequest::getId('推广链接');
            $cur = mSpread::getById('Spread',$id);
            if(!$cur){
                res_return('推广链接参数异常');
            }
            $book = mSpread::getById('Book',$cur['book_id'],'id,name,type');
            if(!in_array($book['type'],[1,2])){
                res_return('仅漫画和小说可生成推广链接');
            }
            $chapter = mSpread::getTenChapter($cur['book_id']);
            if(!$chapter){
                res_return('该书籍未添加章节');
            }
            $book['chapter'] = $chapter;
            $radio = [
                'name' => 'is_sub',
                'filter' => 'is_sub',
                'option' => [['val'=>1,'text'=>'是','default'=>1],['val'=>2,'text'=>'否','default'=>0]]
            ];
            $variable = [
                'book' => $book,
                'radio' => $radio,
                'cur' => $cur
            ];
            $this->assign($variable);
            return $this->fetch('createLink');
        }
    }
    
    //删除链接
    public function delLink(){
        $id = myRequest::postId('推广链接');
        $re = mSpread::setField('Spread',[['id','=',$id]],'status',2);
        if($re !== false){
            res_return('ok');
        }else{
            res_return('删除失败');
        }
    }
    
    //复制链接
    public function copyLink(){
        $id = myRequest::getId('推广链接');
        $cur = mSpread::getById('Spread',$id,'id,url,short_url,status');
           // print_r($cur);
        if(empty($cur)){
            res_return('推广链接不存在');
        }
        if($cur['status'] != 1){
            res_return('该链接已删除');
        }
        $data = [
            'notice'=>'温馨提示 : 短链接为长链接转化而来，长链接和短链接实际跳转到同一地址',
            'links' => [
                ['title' => '短链接','val' => $cur['short_url']],
                ['title' => '长链接','val' => $cur['url']],
            ]
        ];
       // print_r($data);
        $this->assign('data',$data);
        return $this->fetch('public/copyLink');
    }
    
    //生成二维码
    public function qrcode(){
        $id = myRequest::getId('推广链接');
        $cur = mSpread::getById('Spread', $id,'id,url');
        if(!$cur){
            res_return('推广信息异常');
        }
        if(!$cur['url']){
            res_return('推广链接不存在');
        }
        $path = './qrcode/'.$id;
        if(!is_dir($path)){
            mkdir($path,0777,true);
        }
        $filename = $path.'/qrcode.png';
        if(!@is_file($filename)){
            $qrcode = new QrCode();
            $qrcode->setText($cur['url']);
            $qrcode->setSize(400);
            $qrcode->writeFile($filename);
            if(!@is_file($filename)){
                res_return('二维码生成失败');
            }
            $bgs = [
                './static/templet/qrbg/qrcode_bg1.jpg',
                './static/templet/qrbg/qrcode_bg2.jpg',
                './static/templet/qrbg/qrcode_bg3.jpg',
                './static/templet/qrbg/qrcode_bg4.jpg',
                './static/templet/qrbg/qrcode_bg5.jpg'
            ];
            foreach($bgs as $k=>$v){
                $key = $k+1;
                $im_dst = imagecreatefromjpeg($v);
                $imgsize = getimagesize($filename);
                if(strpos($imgsize['mime'],'png') !== false){
                    $im_src = imagecreatefrompng($filename);
                }else{
                    $im_src = imagecreatefromjpeg($$filename);
                }
                $width = $imgsize[0];
                switch ($key){
                    case 1:
                        imagecopyresized ( $im_dst, $im_src,216, 25, 0, 0, 150, 150, $width, $width);
                        break;
                    case 2:
                        imagecopyresized ( $im_dst, $im_src,365, 26, 0, 0, 180, 180, $width, $width);
                        break;
                    case 3:
                        imagecopyresized ( $im_dst, $im_src,340, 60, 0, 0, 146, 146, $width, $width);
                        break;
                    case 4:
                        imagecopyresized ( $im_dst, $im_src,105, 25, 0, 0, 150, 150, $width, $width);
                        break;
                    case 5:
                        imagecopyresized ( $im_dst, $im_src,210, 56, 0, 0, 152, 152, $width, $width);
                        break;
                }
                $picname = 'qrcode_pic_'.$key.'.png';
                $newfile = './qrcode/'.$id.'/'.$picname;
                imagejpeg($im_dst,$newfile);
                imagedestroy($im_src);
                imagedestroy($im_dst);
            }
        }
        $this->assign('path','/qrcode/'.$id.'/');
        return $this->fetch();
    }
    
    //文案素材列表
    public function material(){
        if($this->request->isAjax()){
            $config = [
                'like' => 'keyword:title'
            ];
            $pages = myRequest::getPageParams();
            $where = mySearch::getWhere($config);
            $res = mMaterial::getPageList('Material',$where, '*', $pages);
            if($res['data']){
                foreach ($res['data'] as &$v){
                    $v['do_url'] = my_url('doMaterial',['id'=>$v['id']]);
                }
            }
            res_return('ok',$res['data'],$res['count']);
        }else{
            
            return $this->fetch();
        }
    }
    
    //新增素材
    public function addMaterial(){
        if($this->request->isAjax()){
            $field = 'title,cover';
            mMaterial::doneMaterial($field);
        }else{
            $field = 'id,title,cover';
            $cur = mMaterial::buildArr($field);
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('material')
            ];
            $this->assign($variable);
            return $this->fetch('doMaterial');
        }
    }
    
    //编辑素材
    public function doMaterial(){
        if($this->request->isAjax()){
            $field = 'id,title,cover';
            mMaterial::doneMaterial($field);
        }else{
            $id = myRequest::getId('素材');
            $cur = mMaterial::getById('Material',$id);
            if(!$cur){
                res_return('文案不存在');
            }
            $variable = [
                'cur' => $cur,
                'backUrl' => my_url('material')
            ];
            $this->assign($variable);
            return $this->fetch('doMaterial');
        }
    }
    
    //删除素材
    public function delMaterial(){
        $id = myRequest::postId('素材');
        $re = mMaterial::delById('Material',$id);
        if($re){
            res_return();
        }else{
            res_return('操作失败,请重试');
        }
    }
    
}