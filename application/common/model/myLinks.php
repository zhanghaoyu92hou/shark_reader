<?php
namespace app\common\model;

class myLinks{
    
    public static function getAll(){
        $list = [
            'novel' => [
                'name' => '小说相关链接',
                'links' => [
                    ['url'=>'/index/Book/novel.html','summary'=>'小说首页'],
                    ['url'=>'/index/Book/category.html?type=2','summary'=>'小说分类'],
                    ['url'=>'/index/Book/category.html?type=2&free_type=1','summary'=>'免费小说'],
                    ['url'=>'/index/Book/category.html?type=2&gender_type=1','summary'=>'男频小说'],
                    ['url'=>'/index/Book/category.html?type=2&gender_type=2','summary'=>'女频小说'],
                    ['url'=>'/index/Book/category.html?type=2&category=XXX','summary'=>'其他分类小说'],
                    ['url'=>'/index/Book/rank.html?type=2&gender_type=1','summary'=>'小说男生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=2&gender_type=2','summary'=>'小说女生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=2','summary'=>'小说排行'],
                    ['url'=>'/index/Book/more.html?type=2&is_hot=1','summary'=>'小说热门推荐']
                ]
            ],
            'cartoon' => [
                'name' => '漫画相关链接',
                'links' => [
                    ['url'=>'/index/Book/cartoon.html','summary'=>'首页'],
                    ['url'=>'/index/Book/category.html?type=1','summary'=>'漫画分类'],
                    ['url'=>'/index/Book/category.html?type=1&free_type=1','summary'=>'免费漫画'],
                    ['url'=>'/index/Book/category.html?type=1&gender_type=1','summary'=>'男频漫画'],
                    ['url'=>'/index/Book/category.html?type=1&gender_type=2','summary'=>'女频漫画'],
                    ['url'=>'/index/Book/category.html?type=1&category=XXX','summary'=>'其他分类漫画'],
                    ['url'=>'/index/Book/rank.html?type=1&gender_type=1','summary'=>'漫画男生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=1&gender_type=2','summary'=>'漫画女生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=1','summary'=>'漫画排行'],
                	['url'=>'/index/Book/more.html?type=1&is_hot=1','summary'=>'漫画热门推荐']
                ]
            ],
            'music' => [
                'name' => '听书相关链接',
                'links' => [
                    ['url'=>'/index/Book/music.html','summary'=>'首页'],
                    ['url'=>'/index/Book/category.html?type=3','summary'=>'听书分类'],
                    ['url'=>'/index/Book/category.html?type=3&free_type=1','summary'=>'免费听书'],
                    ['url'=>'/index/Book/category.html?type=3&gender_type=1','summary'=>'男频听书'],
                    ['url'=>'/index/Book/category.html?type=3&gender_type=2','summary'=>'女频听书'],
                    ['url'=>'/index/Book/category.html?type=3&category=XXX','summary'=>'其他分类听书'],
                    ['url'=>'/index/Book/rank.html?type=3&gender_type=1','summary'=>'听书男生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=3&gender_type=2','summary'=>'听书女生榜排行'],
                    ['url'=>'/index/Book/rank.html?type=3','summary'=>'听书排行'],
                	['url'=>'/index/Book/more.html?type=3&is_hot=1','summary'=>'听书热门推荐'],
                ]
            ],
            'video' => [
                'name' => '视频相关链接',
                'links' => [
                    ['url'=>'/index/Video/index.html','summary'=>'首页'],
                    ['url'=>'/index/Video/category.html','summary'=>'分类'],
                    ['url'=>'/index/Video/category.html?free_type=1','summary'=>'免费'],
                    ['url'=>'/index/Video/category.html?category=XXX','summary'=>'视频分类页面'],
                    ['url'=>'/index/Video/rank.html','summary'=>'热门排行'],
                	['url'=>'/index/Video/more.html?is_hot=1','summary'=>'视频热门推荐'],
                ]
            ],
            'product' => [
                'name' => '商品相关链接',
                'links' => [
                    ['url'=>'/index/Product/index.html','summary'=>'商城首页'],
                    ['url'=>'/index/User/myOrder.html','summary'=>'我的商品订单']
                ]
            ],
            'other' => [
                'name' => '其他链接',
                'links' => [
                    ['url'=>'/index/User/index.html','summary'=>'个人中心'],
                    ['url'=>'/index/User/bindPhone.html','summary'=>'绑定手机号'],
                    ['url'=>'/index/User/myHistory.html','summary'=>'阅读历史'],
                    ['url'=>'/index/User/myCollect.html','summary'=>'我的收藏'],
                    ['url'=>'/index/User/feedback.html','summary'=>'我的反馈列表'],
                    ['url'=>'/index/User/doFeedback.html','summary'=>'提交反馈'],
                    ['url'=>'/index/User/message.html','summary'=>'我的消息'],
                    ['url'=>'/index/Charge/index.html','summary'=>'书币充值']
                ]
            ],
        ];
        return $list;
    }
}
