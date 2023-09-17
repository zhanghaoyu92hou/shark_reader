<?php
namespace app\common\model;

class myMaterial{
    
    //获取提交的图文消息
    public static function getGraphicMessageData(){
        $rules = [
            'message_title' => ['require|array',['require'=>'请填写消息标题','array'=>'消息标题格式错误']],
            'message_cover' => ['require|array',['require'=>'请选择消息封面','array'=>'消息封面格式错误']],
            'message_link' => ['require|array',['require'=>'请填写消息链接','array'=>'消息链接格式错误']],
            'message_desc' => ['max:255',['max'=>'首条消息描述字数超出限制']]
        ];
        $data = myValidate::getData($rules, 'message_title,message_cover,message_link,message_desc');
        $main = $child = [];
        $max = count($data['message_title']);
        if($max == 0){
            res_return('消息内容数据异常');
        }
        $key = 1;
        $news = [];
        foreach ($data['message_title'] as $k=>$v){
            $title = $data['message_title'][$k];
            if(!$title){
                res_return('第'.$key.'条消息未输入标题');
            }
            $link = $data['message_link'][$k];
            if(!$link){
                res_return('第'.$key.'条消息未输入链接');
            }
            $cover = $data['message_cover'][$k];
            if(!$cover){
                res_return('第'.$key.'条消息未上传封面');
            }
            $one = ['title'=>$title,'url'=>$link,'picurl'=>$cover];
            if($key === 1){
                $one['description'] = $data['message_desc'];
            }else{
                $one['description'] = '';
            }
            $news[] = $one;
            $key++;
        }
        return $news;
    }
    
    //获取提交的客服消息内容
    public static function getCustomMsg(){
        $rules = [
            'message_title' => ['require|max:30',['require'=>'请填写消息标题','array'=>'消息标题字数超出限制']],
            'message_cover' => ['require|max:255',['require'=>'请选择消息封面','array'=>'消息封面异常']],
            'message_link' => ['require|max:100',['require'=>'请填写消息链接','array'=>'消息链接字数超出限制']],
            'message_desc' => ['max:255',['max'=>'消息描述字数超出限制']]
        ];
        $data = myValidate::getData($rules,'message_title,message_cover,message_link,message_desc');
        $data = [
            [
                'title' => $data['message_title'],
                'picurl' => $data['message_cover'],
                'url' => $data['message_link'],
                'description' => $data['message_desc']
            ]
        ];
        return $data;
    }
}
