<?php
namespace app\index\controller;

use think\Controller;
use weixin\wx;
use app\common\model\myRequest;
use app\index\model\iMember;
use app\index\model\iBook;
use app\common\model\myCache;
use think\Db;

class Open extends Controller{
    
    /**
     * 公众号消息与事件接收URL
     */
    public function callback(){
        $get = myRequest::get('echostr');
        $config = myCache::getUrlCache();
        wx::$config = $config;
        if($get['echostr']){
            $res = wx::valid();
            $str = $res ? $get['echostr'] : '';
            echo $str;
        }else{
            $this->handler();
        }
    }
    
    public function logs(){
    	if($this->request->get('clear') == 1){
    		cache('sys_log',null);
    		echo 'ok';
    	}else{
    		$log = cache('sys_log');
    		my_print($log);
    	}
    	
    }
    
    /**
     * 分类处理消息
     */
    public function handler(){
        $postStr = file_get_contents('php://input');
        libxml_disable_entity_loader(true);
        $message = json_decode(json_encode(simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if(isset($message['MsgType'])){
            wx::$object = $message;
            switch($message['MsgType']){
                case 'text':
                    $this->textHandler();
                    break;
                case 'event':
                    $this->eventHandler();
                    break;
                default:
                    echo '';
                    break;
            }
        }else{
            echo '';
        }
        exit;
    }
    
    /**
     * 处理回复发送过来的事件信息
     */
    private function eventHandler(){
        //解析参数
        switch (wx::$object['Event']){
            //用户关注
            case 'subscribe':
                if(isset(wx::$object['EventKey']) && wx::$object['EventKey']){
                	self::scanQrcode();
                }else{
                	self::doSubscribe();
                }
                break;
            case 'SCAN':
            	self::scanQrcode();
            	break;
                //用户取消关注
            case 'unsubscribe':
                iMember::unsubscribeMember(wx::$object['FromUserName']);
                echo '';
                break;
                //用户点击事件推菜单
            case 'CLICK':
                self::getClickStr();
                break;
            default:
                echo '';
                break;
        }
        exit;
    }
    
    //扫码登陆
    private function scanQrcode(){
    	$info = wx::getUserInfo(wx::$object['FromUserName']);
    	if($info){
    		$code = ltrim(wx::$object['EventKey'],'qrscene_');
    		$flag = iMember::subscribeByQrcode($info,$code);
    		if(is_bool($flag)){
    			$str = '扫码登陆成功';
    			self::doHistoryReply($str);
    		}else{
    			wx::responseText($flag);
    		}
    	}else{
    		$text = '获取信息失败，扫码登陆失败';
    		wx::responseText($text);
    	}
    }
    
    //直接关注
    private function doSubscribe(){
    	$info = wx::getUserInfo(wx::$object['FromUserName']);
    	if($info){
    		$is_first = iMember::subscribeMember($info);
    		switch ($is_first){
    			case 1:
    				$user = myCache::getUserByOpenId(wx::$object['FromUserName']);
    				$is_read = Db::name('ReadHistory')->where('uid','=',$user['id'])->where('type','between',[1,2])->value('id');
    				if($is_read){
    					self::doHistoryReply('尊敬的用户,您已关注成功!');
    				}else{
    					self::doAttentionBack();
    				}
    				break;
    			case 2:
    				self::doHistoryReply('尊敬的用户,您已关注成功!');
    				break;
    			default:
    				wx::responseText('注册失败');
    				break;
    		}
    	}else{
    		wx::responseText('获取微信用户公开信息失败');
    	}
    }
    
    /**
     * 处理菜单事件推
     */
    private function getClickStr(){
        $keyword = isset(wx::$object['EventKey']) ? wx::$object['EventKey'] : '';
        if($keyword){
            if($keyword === 'sign'){
                self::doSign();
            }else{
                $where = [['status','=',1],['channel_id','=',wx::$config['channel_id']],['keyword','=',$keyword]];
                $cur = Db::name('WxSpecial')->where($where)->field('id,type,content,material')->find();
                if($cur){
                    if($cur['type'] == 1){
                        wx::responseText($cur['content']);
                    }else{
                        wx::responseNews(json_decode($cur['material'],true));
                    }
                }
            }
        }
        wx::responseText('未定义该菜单');
    }
    
    /**
     * 处理签到回复
     */
    private function doSign(){
        $website = myCache::getWebSiteCache();
        if(isset($website['is_sign']) && $website['is_sign'] == 1){
            $signConfig = $website['sign_config'];
            $title = iMember::doSign(wx::$object['FromUserName'],$signConfig);
            self::doHistoryReply($title);
        }else{
            wx::responseText('签到功能已关闭');
        }
    }
    
    /**
     * 首次关注回复
     */
    private function doAttentionBack(){
        $where = [['status','=',1],['channel_id','=',wx::$config['channel_id']],['keyword','=','attention']];
        $cur = Db::name('WxSpecial')->where($where)->field('id,type,content,material')->find();
        if($cur){
            if($cur['type'] == 1){
                wx::responseText($cur['content']);
            }else{
                wx::responseNews(json_decode($cur['material'],true));
            }
        }
        wx::responseText('欢迎关注!');
    }
    
    /**
     * 回复阅读记录
     * @param string $first_str
     */
    private function doHistoryReply($first_str){
    	$user = myCache::getUserByOpenId(wx::$object['FromUserName']);
    	$urlData = myCache::getUrlCache();
    	$str = '';
    	if($user){
    		$field = 'id,book_id,number';
    		$last_read = Db::name('ReadHistory')->where('uid','=',$user['id'])->where('type','between',[1,2])->where('is_end','=',1)->field($field)->find();
    		if($last_read){
    			$str .= $first_str;
    			$str .= "\n\n";
    			$href = '➢ <a href="http://'.$urlData['url'].'/index/Book/read.html?book_id='.$last_read['book_id'].'&number='.$last_read['number'].'">继续阅读</a>';
    			$str .= $href;
    			$fields = 'max(a.id) as id,book_id,max(a.number) as number,ANY_VALUE(b.name) as name';
    			$books = Db::name('ReadHistory a')
    			->join('book b','a.book_id=b.id')
    			->where('a.uid','=',$user['id'])
    			->where('a.type','between',[1,2])
    			->where('a.book_id','<>',$last_read['book_id'])
    			->field($fields)
    			->group('a.book_id')
    			->limit(4)
    			->order('id','desc')
    			->select();
    			if($books){
    				$str .= "\n\n";
    				$str .= '历史阅读记录';
    				foreach ($books as $v){
    					$str .= "\n\n";
    					$str .= '➢ <a href="http://'.$urlData['url'].'/index/Book/read.html?book_id='.$v['book_id'].'&number='.$v['number'].'">'.$v['name'].'</a>';
    				}
    			}
    			$str .= "\n\n";
    			$str .= '为方便下次阅读，请置顶公众号';
    		}
    	}
    	if(!$str){
    		$str .= $first_str;
    		$str .= "\n\n";
    		$str .= '为方便下次阅读，请置顶公众号';
    		$str .= "\n\n";
    		$href = '➢ <a href="http://'.$urlData['url'].'">进入书城</a>';
    		$str .= $href;
    	}
    	wx::responseText($str);
    }
    
    /**
     * 处理发送过来的文字消息
     */
    private function textHandler(){
        $where = [['status','=',1],['channel_id','=',wx::$config['channel_id']],['keyword','=',wx::$object['Content']]];
        $cur = Db::name('WxReply')->where($where)->field('id,type,content,material')->find();
        if($cur){
            if($cur['type'] == 1){
                wx::responseText($cur['content']);
            }else{
                wx::responseNews(json_decode($cur['material'],true));
            }
        }else{
            self::searchBook(); 
        }
    }
    
    /**
     * 搜索推荐小说并发送,当只匹配到一本小说时推送图文消息
     */
    private function searchBook(){
        $path = '/index/Book/info.html';
        $config = wx::$config;
        $url = $config['is_location'] == 1 ? $config['location_url'] : $config['url'];
        $books = iBook::searchBook(wx::$object['Content'],6);
        if(!empty($books)){
            if(count($books) > 1){
                $html = "我们找到以下书籍；";
                $html.="\n\n";
                foreach ($books as $v){
                    $link = "http://".$url.$path."?book_id=".$v['id'];
                    $a = '➢  <a href="'.$link.'">'.$v['name'].'</a>';
                    $html .= $a;
                    $html .= "\n\n";
                }
                wx::responseText($html);
            }else{
                $data = [
                    [
                        'title'=>$books[0]['name'],
                        'picurl' => $books[0]['cover'],
                        'url' => 'http://'.$url.$path.'?book_id='.$books[0]['id'],
                        'description' => $books[0]['summary']
                    ]
                ];
                wx::responseNews($data);
            }
        }else{
        	$str = '未找到匹配的内容';
        	$str .= "\n\n";
        	$str .= '请回复小说/漫画书名';
        	$str .= "\n\n";
        	$str .= '我们帮您查找其他内容哦......';
            wx::responseText($str);
        }
    }
    
}