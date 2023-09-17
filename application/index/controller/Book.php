<?php

namespace app\index\controller;

use app\index\controller\Common;
use app\common\model\myRequest;
use app\index\model\iBook;
use app\common\model\mySearch;
use app\common\model\myCache;
use app\common\model\myValidate;
use weixin\wx;
use think\Db;
use app\index\model\iMember;

class Book extends Common {

    //小说首页
    public function novel() {
        parent::checkBlock('novel', '小说');
        $block = myCache::getWebblockCache();
        $urlData = myCache::getUrlCache();
        
        $variable = [
            'web_block' => $block,
            'site_info' => $urlData
        ];
        $this->assign($variable);
        return $this->fetch();
    }

    //漫画首页
    public function cartoon() {
        parent::checkBlock('cartoon', '漫画');
        $block = myCache::getWebblockCache();
        $urlData = myCache::getUrlCache();
        $variable = [
            'web_block' => $block,
            'site_info' => $urlData
        ];
        $this->assign($variable);
        return $this->fetch();
    }

    //听书首页
    public function music() {
        parent::checkBlock('music', '听书');
        $block = myCache::getWebblockCache();
        $urlData = myCache::getUrlCache();
        $variable = [
            'web_block' => $block,
            'site_info' => $urlData
        ];
        $this->assign($variable);
        return $this->fetch();
    }

    //书籍详情
    public function info() {
        $get = myRequest::get('book_id,is_dir');
        $book_id = (is_numeric($get['book_id']) && $get['book_id'] > 0) ? $get['book_id'] : 0;
        if (!$book_id) {
            res_return('书籍信息异常');
        }
        $is_dir = $get['is_dir'] == 1 ? $get['is_dir'] : 0;
        $book = myCache::getBookCache($book_id);
        if (empty($book)) {
            res_return('书籍不存在');
        }
        iBook::addHot($book);
        $tpl = 'info';
        if ($book['type'] == 3) {
            $tpl = 'musicInfo';
        }
        $urlData = myCache::getUrlCache();
        if ($book['category']) {
            $book['category'] = explode(',', trim($book['category'], ','));
        }
        $book['hot_num'] = $book['hot_num'] > 10000 ? round($book['hot_num'] / 10000, 2) . '万' : $book['hot_num'];
        $reward = myCache::getRewardCache();
        $jsConfig = $share_data = '';
        if ($urlData['is_wx'] === 1 && $this->device_type === 1) {
            if ($book['cover'] && $book['share_title'] && $book['share_desc']) {
                wx::$config = $urlData;
                $jsConfig = wx::getJsConfig();
                global $loginId;
                $share_data = [
                    'title' => $book['share_title'],
                    'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index/Book/info.html?book_id=' . $book['id'] . '&share_user=' . $loginId,
                    'img' => $book['cover'],
                    'desc' => $book['share_desc']
                ];
            }
        }

//此处是插入广告
        $data = myCache::getAdCache('文章详情页');
        $ad = '';
        if (!empty($data)) {
            $ad .= '<a style="width: 100%;height: 100px" href="' . $data['url'] . '">';
            $ad .= '<img style="width: 100%;height: 150px" src="' . $data['img'] . '"> </a>';
        }
        $text = htmlspecialchars($ad);

        $variable = [
            'book' => $book,
            'is_dir' => $is_dir,
            'reward' => $reward,
            'site_info' => $urlData,
            'jsConfig' => json_encode($jsConfig, JSON_UNESCAPED_UNICODE),
            'share_data' => json_encode($share_data, JSON_UNESCAPED_UNICODE),
            'text' => $text,
        ];
        //my_print($variable);
        $this->assign($variable);
        return $this->fetch($tpl);
    }

    //获取广告图片
    public function getAdvertisement() {
        $name = myRequest::post('name');

        $data = myCache::getAdCache($name['name']);
//          echo '<pre>';print_r($book);exit;
        res_return(['list' => $data]);
    }

    //书籍正文
    public function read() {
        global $loginId;
        $book_id = myRequest::getId('书籍', 'book_id');
        $book = myCache::getBookCache($book_id);
        $number = myRequest::getId('章节', 'number');
        if (!$book) {
            res_return('书籍信息异常');
        }
        if (!in_array($book['type'], [1, 2])) {
            res_return('非法访问');
        }
        if ($book['total_chapter'] == 0) {
            res_return('该书籍尚无可读章节');
        }
        if ($number > $book['total_chapter']) {
            $number = 1;
        }
        $chapter = iBook::getCur('BookChapter', [['book_id', '=', $book_id], ['number', '=', $number]], 'id,name');
        if (!$chapter) {
            res_return('该章节不存在');
        }
        
        $free_chapter=$book['free_chapter'];
        $total_chapter=$book['total_chapter'];
        
                                      //300     -200      150
        $isPass= ($free_chapter >0 &&$number > $free_chapter ||  ($free_chapter<0 &&  $total_chapter+$free_chapter<$number));
        $member = '';
        $urlData = myCache::getUrlCache();
        if ($urlData['is_wx'] === 1 && $this->device_type == 1) {
            $member = myCache::getUserCache($loginId);
        } else {
            if ($loginId) {
                $member = myCache::getUserCache($loginId);
            }
            if ($book['free_type'] == 2) {
                
        //  echo '<pre>';
        // print_r($isPass);
        // exit;
                if ($isPass) {
                    if (!$member) {
                        parent::checkLogin();
                    }
                }
            }
        }
        $spread = myCache::getCurSpreadCache();
        if ($spread && $spread['book_id'] === $book_id) {
            if ($spread['is_sub'] == 1 && $number >= $spread['number']) {
                if (!$member) {
                    res_return('Login/index');
                }
                if ($member['subscribe'] != 1) {
                    res_return('您尚未关注本公众号');
                }
            }
        }
        if ($member) {
            if ($spread && !$member['spread_id']) {
                iMember::changeMemberSpread($member, $spread);
            }
            $read = iBook::getCur('ReadHistory', [['uid', '=', $loginId], ['book_id', '=', $book_id], ['number', '=', $number]]);
            if (!$read) {
                if ($book['free_type'] == 2 &&   $isPass) {
                    $is_money = true;
                    if ($member['viptime']) {
                        $is_money = false;
                        if ($member['viptime'] != 1) {
                            if (time() > $member['viptime']) {
                                $is_money = true;
                            }
                        }
                    }
                    if ($is_money) {
                        if ($member['money'] < $book['money']) {
                            res_return('您的书币余额不足');
                        }
                        $res = iBook::costMoney($book, $number, $member);
                        if (!$res) {
                            res_return('网络异常，请重试');
                        }
                    } else {
                        iBook::addReadhistory($book, $number, $member);
                    }
                } else {
                    iBook::addReadhistory($book, $number, $member);
                }
            }
        }
        $urlData = myCache::getUrlCache();
        $jsConfig = $share_data = '';
        if ($urlData['is_wx'] === 1 && $this->device_type === 1) {
            if ($book['cover'] && $book['share_title'] && $book['share_desc']) {
                wx::$config = $urlData;
                $jsConfig = wx::getJsConfig();
                global $loginId;
                $share_data = [
                    'title' => $book['share_title'],
                    'url' => 'http://' . $_SERVER['HTTP_HOST'] . '/index/Book/info.html?book_id=' . $book['id'] . '&share_user=' . $loginId,
                    'img' => $book['cover'],
                    'desc' => $book['share_desc']
                ];
            }
        }
        $dir = 'book/' . $book_id;

        //此处插入广告
        $content = getBlockText($number, $dir);
        //  $content = '<p>暗黑三等奖爱打架拉建档立卡假两件垃圾啊奥术大师多</p>';
        $data = myCache::getAdCache('文章阅读页');
        $ad = '';
        if (!empty($data)) {
            $ad .= '<a style="width: 100%;height: 100px" href="' . $data['url'] . '">';
            $ad .= '<img style="width: 100%;height: 150px" src="' . $data['img'] . '"> </a>';
        }
        $len = mb_strlen($content, 'UTF8');
        $text = mb_substr($content, 0, ceil($len / 2));
        $text .= $ad;
        $text .= mb_substr($content, ceil($len / 2));
        $text = htmlspecialchars($text);

        $variable = [
            'book' => $book,
            'number' => $number,
            'dir' => $dir,
            'site_info' => $urlData,
            'chapter_title' => $chapter['name'],
            'jsConfig' => json_encode($jsConfig, JSON_UNESCAPED_UNICODE),
            'share_data' => json_encode($share_data, JSON_UNESCAPED_UNICODE),
            'text' => $text
        ];


        $this->assign($variable);

        $tpl = 'readNovel';
        if ($book['type'] == 1) {
            $tpl = 'readCartoon';
        }
        return $this->fetch($tpl);
    }

    //检查下章是否可读
    public function checkNext() {
        global $loginId;
        $book_id = myRequest::postId('书籍', 'book_id');
        $book = myCache::getBookCache($book_id);
        $number = myRequest::postId('章节', 'number');
        if (!$book) {
            res_return('书籍信息异常');
        }
        if (!in_array($book['type'], [1, 2])) {
            res_return('非法访问');
        }
        if ($book['total_chapter'] == 0) {
            res_return('该书籍尚无可读章节');
        }
        if ($number > $book['total_chapter']) {
            res_return('已经是最后一章了');
        }
        $member = '';
        $free_chapter=$book['free_chapter'];
        $total_chapter=$book['total_chapter'];
        
                                      //300     -200      150
        $isPass= ($free_chapter >0 &&$number > $free_chapter ||  ($free_chapter<0 &&  $total_chapter+$free_chapter<$number));
        if ($this->device_type == 1) {
            $member = myCache::getUserCache($loginId);
        } else {
            if ($loginId) {
                $member = myCache::getUserCache($loginId);
            }
          
       
         
            if ($isPass) {
                if (!$member) {
                    res_return(['flag' => 1, 'msg' => '检测到您尚未登陆,是否立即登陆?', 'url' => my_url('Login/index')]);
                }
            }
        }
        $spread = myCache::getCurSpreadCache();
        if ($spread && $spread['book_id'] === $book_id) {
            if ($spread['is_sub'] == 1 && $number >= $spread['number']) {
                if (!$member) {
                    res_return(['flag' => 1, 'msg' => '检测到您尚未登陆,是否立即登陆?', 'url' => my_url('Login/index')]);
                }
                if ($member['subscribe'] != 1) {
                    res_return(['flag' => 4]);
                }
            }
        }
        if ($member) {
     
            if ($book['free_type'] == 2 && $isPass) {
    
                $read = iBook::getCur('ReadHistory', [['uid', '=', $loginId], ['book_id', '=', $book_id], ['number', '=', $number]]);
                 
                if (!$read) {
                    $is_money = true;
                    if ($member['viptime'] > 0) {
                        $is_money = false;
                        if ($member['viptime'] != 1) {
                            if (time() > $member['viptime']) {
                                $is_money = true;
                            }
                        }
                    }
                    if ($is_money) {
                        if ($member['money'] < $book['money']) {
                            res_return(['flag' => 2, 'url' => my_url('Charge/index', ['book_id' => $book_id])]);
                        }
                        if ($member['is_auto'] != 1) {
                            res_return(['flag' => 3, 'msg' => '阅读下章将扣除' . $book['money'] . '书币,是否继续阅读', 'url' => my_url('read', ['book_id' => $book_id, 'number' => $number])]);
                        }
                    }
                }
            }
        }
        res_return(['flag' => 0, 'url' => my_url('read', ['book_id' => $book_id, 'number' => $number])]);
    }

    //书籍分类
    public function category() {
        if ($this->request->isAjax()) {
            $config = [
                'default' => [['a.status', '=', 1]],
                'eq' => 'gender_type:a.gender_type,type:a.type,over_type:a.over_type,free_type:a.free_type',
                'like' => 'category:a.category',
                'rules' => ['gender_type' => 'in:1,2', 'type' => 'in:1,2,3', 'over_type' => 'in:1,2', 'free_type' => 'in:1,2']
            ];
            $where = mySearch::getWhere($config, 'post');
            $post = myRequest::post('page');
            $page = (is_numeric($post['page']) && $post['page'] > 0) ? $post['page'] : 1;
            $list = iBook::getCategoryList($where, $page);
            $list = $list ?: 0;
            res_return('ok', $list);
        } else {
            $get = myRequest::get('type');
            if (!in_array($get['type'], [1, 2, 3])) {
                res_return('非法访问');
            }
            switch ($get['type']) {
                case 1:
                    $key = 'cartoon_category';
                    break;
                case 2:
                    $key = 'novel_category';
                    break;
                case 3:
                    $key = 'music_category';
                    break;
            }
            $option = iBook::getCategoryOption($key);
            $option['site_title'] = $this->site_title;
            $option['get_type'] = $get['type'];
            $this->assign($option);
            return $this->fetch();
        }
    }

    //更多列表
    public function more() {
        if ($this->request->isAjax()) {
            $post = myRequest::post('type,area,is_hot,page');
            $list = 0;
            if (in_array($post['type'], [1, 2, 3])) {
                $where = [['a.status', '=', 1], ['a.type', '=', $post['type']]];
                $is_ok = false;
                if ($post['area']) {
                    $is_ok = true;
                    $where[] = ['a.area', 'like', '%,' . $post['area'] . ',%'];
                } else {
                    if ($post['is_hot'] == 1) {
                        $is_ok = true;
                        $where[] = ['a.is_hot', '=', 1];
                    }
                }
                if ($is_ok) {
                    $page = $post['page'] >= 1 ? $post['page'] : 1;
                    $list = iBook::getMoreList($where, $page);
                    $list = $list ?: 0;
                }
            }
            res_return('ok', $list);
        } else {
            $get = myRequest::get('type,area,is_hot');
            if (!$get['type']) {
                res_return('书籍类型有误');
            }
            $title = $get['area'];
            if ($get['is_hot'] == 1) {
                $title = '热门推荐';
            }
            $get['page_title'] = $title;
            $get['site_title'] = $this->site_title;
            $this->assign('cur', $get);
            return $this->fetch();
        }
    }

    //排行
    public function rank() {
        $get = myRequest::get('type,gender_type');
        if (!in_array($get['type'], [1, 2, 3])) {
            res_return('非法访问');
        }
        switch ($get['type']) {
            case 1:
                $block_name = 'cartoon_ranks';
                break;
            case 2:
                $block_name = 'novel_ranks';
                break;
            case 3:
                $block_name = 'music_ranks';
                break;
        }
        $gender_type = in_array($get['gender_type'], [1, 2]) ? $get['gender_type'] : 3;
        $variable = [
            'site_title' => $this->site_title,
            'block_name' => $block_name,
            'gender_type' => $gender_type
        ];
        $this->assign($variable);
        return $this->fetch();
    }

    //获取猜你喜欢书籍
    public function getSameBooks() {
        $book_id = myRequest::postId('书籍', 'book_id');
        $book = myCache::getBookCache($book_id);
        $data = 0;
        if ($book) {
            $category = $book['category'];
            $type = $book['type'];
            $where = [['type', '=', $type], ['category', '=', $category], ['status', '=', 1], ['id', '<>', $book_id]];
            $list = iBook::getLimitBooks($where, 6);
            $data = $list ? $list : 0;
        }
        res_return(['list' => $data]);
    }

    //获取该书获得打赏金额
    public function getRewardMoney() {
        $book_id = myRequest::postId('书籍', 'book_id');
        iBook::getRewardMoney($book_id);
    }

    //获取书籍章节列表
    public function getBookChapter() {
        $book_id = myRequest::postId('书籍', 'book_id');
        $book = myCache::getBookCache($book_id);
        if (!$book) {
            res_return('书籍信息异常');
        }
        $total_chapter= $book['total_chapter'];
        $page = myRequest::postId('分页', 'page');
        $num =30;
        $end = $page * $num;
        $start = $end - $num + 1;
        $list = [];
        while ($start <= $end) {
            if ($start <= $book['total_chapter']) {
                $tmp = [
                    'name' => '第' . $start . '章',
                    'number' => $start,
                    'money' => 0
                ];
                if ($book['free_type'] == 2) {
                  $free_chapter=intval($book['free_chapter']);
                 if( $free_chapter<0){
                 	 $free_chapter=   $total_chapter+$free_chapter;
                 }
                    if ($start >$free_chapter) {
                        $tmp['money'] = intval($book['money']);
                    }
                }
                $list[] = $tmp;
                $start++;
            } else {
                break;
            }
        }
        $list = $list ?: '';
        res_return('ok', $list);
    }

    //检查音乐播放
    public function checkMusicChapter() {
        global $loginId;
        $rules = [
            'book_id' => ['require|number|gt:0', ['require' => '书籍参数错误', 'number' => '书籍参数格式不规范', 'gt' => '书籍格式不规范']],
            'number' => ['require|number|gt:0', ['require' => '章节参数错误', 'number' => '章节参数格式不规范', 'gt' => '章节格式不规范']],
            'is_confirm' => ['eq:yes', ['eq' => '访问参数错误']]
        ];
        $data = myValidate::getData($rules, 'book_id,number,is_confirm');
        $book = myCache::getBookCache($data['book_id']);
        if (!$book) {
            res_return('书籍信息异常');
        }
        if ($book['type'] != 3) {
            res_return('非法访问');
        }
        if ($book['total_chapter'] == 0) {
            res_return('该书籍尚无可读章节');
        }
        if ($data['number'] > $book['total_chapter']) {
            res_return('已经是最后一章了');
        }
        $cur = iBook::getCur('BookChapter', [['book_id', '=', $data['book_id']], ['number', '=', $data['number']]], 'id,src');
        if (!$cur || !$cur['src']) {
            res_return('章节信息异常');
        }
        $member = '';
        if ($this->device_type == 1) {
            $member = myCache::getUserCache($loginId);
        } else {
            if ($loginId) {
                $member = myCache::getUserCache($loginId);
            }
            if ($data['number'] > $book['free_chapter']) {
                if (!$member) {
                    res_return(['flag' => 1, 'msg' => '您尚未登陆,是否立即登陆?', 'url' => my_url('Login/index')]);
                }
            }
        }
        if ($member) {
            if ($book['free_type'] == 2 && $data['number'] > $book['free_chapter']) {
                $read = iBook::getCur('ReadHistory', [['uid', '=', $loginId], ['book_id', '=', $data['book_id']], ['number', '=', $data['number']]]);
                if (!$read) {
                    $is_money = true;
                    if ($member['viptime']) {
                        $is_money = false;
                        if ($member['viptime'] != 1) {
                            if (time() > $member['viptime']) {
                                $is_money = true;
                            }
                        }
                    }
                    if ($is_money) {
                        if ($member['money'] < $book['money']) {
                            res_return(['flag' => 2, 'url' => my_url('Charge/index')]);
                        }
                        if ($member['is_auto'] != 1) {
                            if ($data['is_confirm'] !== 'yes') {
                                res_return(['flag' => 3, 'msg' => '阅读下章将扣除' . $book['money'] . '书币,是否继续阅读']);
                            } else {
                                $re = iBook::costMoney($book, $data['number'], $member);
                                if (!$re) {
                                    res_return('支付书币失败，请重试');
                                }
                            }
                        } else {
                            $re = iBook::costMoney($book, $data['number'], $member);
                            if (!$re) {
                                res_return('支付书币失败，请重试');
                            }
                        }
                    } else {
                        iBook::addReadhistory($book, $data['number'], $member);
                    }
                }
            }
        }
        res_return(['flag' => 0, 'url' => $cur['src']]);
    }
  
  	 public function booksearch() {
       global $loginId;
        $get = myRequest::get('type,keyword');
       $list='';
        if($get['keyword']){
          			$keyword = $get['keyword'] ? : '';
                    if(!$keyword){
                        res_return('未键入关键字');
                    }
                    if($loginId){
                        $repeat = Db::name('SearchRecord')->where('keyword','=',$keyword)->where('uid','=',$loginId)->value('id');
                        if(!$repeat){
                            Db::name('SearchRecord')->insert(['uid'=>$loginId,'keyword'=>$keyword]);
                        }
                    }
                    $where = [['a.status','=',1],['a.name|a.author|a.lead','like','%'.$keyword.'%']];
                    $field = 'a.id,a.name,a.type,a.cover,a.summary,a.category,a.over_type,a.hot_num,IFNULL(max(b.number),0) as total_chapter';
                    $list = Db::name('Book a')
                        ->join('book_chapter b','a.id=b.book_id','left')
                        ->where($where)
                        ->field($field)
                        ->group('a.id')
                        ->select();
                    if($list){
                        foreach ($list as &$v){
                            $category = $v['category'] ? explode(',', trim($v['category'],',')) : [];
                            $v['category'] = !empty($category) ? $category : '';
                            $v['hot_num'] = $v['hot_num'] > 10000 ? round($v['hot_num']/10000,2).'万' : $v['hot_num'];
                            $v['over_type'] = $v['over_type'] == 1 ? '连载' : '完结';
                            $v['info_url'] = my_url('Book/info',['book_id'=>$v['id']]);
                            $v['type'] = intval($v['type']);
                        }
                    }else{
                        $list = '';
                    }
          			//print_r($list);
          			
        }
        $this->assign('list',$list);
        return $this->fetch();
    }

}
