<?php
namespace weixin;
use app\common\model\myRequest;
use other\myHttp;

class wx{
    //微信公众号参数
    public static $config;
    //微信推送对象
    public static $object;
    
    /**
     * 验证服务器地址的有效性
     * @return boolean
     */
    public static function valid(){
        $res = false;
        $token = isset(self::$config['apptoken']) ? self::$config['apptoken'] : '';
        if($token){
            $get = myRequest::get('signature,timestamp,nonce');
            $tmpArr = array($token,$get['timestamp'],$get['nonce']);
            sort($tmpArr, SORT_STRING);
            $tmpStr = implode($tmpArr);
            $tmpStr = sha1($tmpStr);
            if($tmpStr === $get['signature']){
                $res = true;
            }
        }
        return $res;
    }
    
    /**
     * 发布菜单
     * @param array $treeData 菜单树状图
     * @return boolean
     */
    public static function createMenu($treeData){
        $res = false;
        $temp = [];
        foreach ($treeData as $v){
            $one = ['name' => $v['name']];
            if(isset($v['child'])){
                $one['sub_button'] = [];
                foreach ($v['child'] as $val){
                    $child_one = ['name'=>$val['name']];
                    $content = json_decode($val['content'],true);
                    switch ($val['type']){
                        case 1:
                            $child_one['type'] = 'view';
                            $child_one['url'] = $content['value'];
                            break;
                        case 2:
                            $child_one['type'] = 'miniprogram';
                            $child_one['appid'] = $content['appid'];
                            $child_one['pagepath'] = $content['value'];
                            break;
                        case 3:
                            $child_one['type'] = 'click';
                            $child_one['key'] = $content['value'];
                            break;
                    }
                    $one['sub_button'][] = $child_one;
                }
            }else{
                $content = json_decode($v['content'],true);
                switch ($v['type']){
                    case 1:
                        $one['type'] = 'view';
                        $one['url'] = $content['value'];
                        break;
                    case 2:
                        $one['type'] = 'miniprogram';
                        $one['appid'] = $content['appid'];
                        $one['pagepath'] = $content['value'];
                        break;
                    case 3:
                        $one['type'] = 'click';
                        $one['key'] = $content['value'];
                        break;
                }
            }
            $temp[] = $one;
        }
        $data = json_encode(['button'=>$temp],JSON_UNESCAPED_UNICODE);
        $token = self::getAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        $re = myHttp::postData($url,$data);
        if(isset($re['errcode']) && $re['errcode'] == 0){
            $res = true;
        }
        return $res;
    }
    
    /**
     * 获取微信用户公开信息
     * @param string $openid
     * @return mixed
     */
    public static function getUserInfo($openid){
        $token = self::getAccessToken();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$token."&openid=".$openid."&lang=zh_CN";
        $info = myHttp::getData($url);
        if(isset($info['openid'])){
            return $info;
        }else{
            return false;
        }
    }
    
    /**
     * 创建临时二维码
     * @param string $code 二维码携带参数
     * @param number $time 过期时间
     */
    public static function createTmpQrcode($code,$time=3600){
    	$data = [
    		'expire_seconds' => $time,
    		'action_name' => 'QR_STR_SCENE',
    		'action_info' => [
    			'scene' => [
    				'scene_str' => $code	
    			]
    		]
    	];
    	$token = self::getAccessToken();
    	$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$token;
    	$json = json_encode($data,JSON_UNESCAPED_UNICODE);
    	$re = myHttp::postData($url,$json);
    	$res = false;
    	if(isset($re['ticket']) && $re['ticket']){
    		$res = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$re['ticket'];
    	}
    	return $res;
    }
    
    /**
     * 回复图文消息
     * @param array $newsContent 图文消息内容
     * @return string
     */
    public static function responseNews($newsContent){
        $newsTplHead = "<xml>
				    <ToUserName><![CDATA[%s]]></ToUserName>
				    <FromUserName><![CDATA[%s]]></FromUserName>
				    <CreateTime>%s</CreateTime>
				    <MsgType><![CDATA[news]]></MsgType>
				    <ArticleCount>%s</ArticleCount>
				    <Articles>";
        $newsTplBody = "<item>
				    <Title><![CDATA[%s]]></Title>
				    <Description><![CDATA[%s]]></Description>
				    <PicUrl><![CDATA[%s]]></PicUrl>
				    <Url><![CDATA[%s]]></Url>
				    </item>";
        $newsTplFoot = "</Articles>
					<FuncFlag>0</FuncFlag>
				    </xml>";
        $bodyCount = count($newsContent);
        $bodyCount = $bodyCount < 10 ? $bodyCount : 10;
        $header = sprintf($newsTplHead,self::$object['FromUserName'],self::$object['ToUserName'],time(),$bodyCount);
        $body = '';
        foreach($newsContent as $key => $value){
            $body .= sprintf($newsTplBody, $value['title'], $value['description'], $value['picurl'], $value['url']);
        }
        $FuncFlag = 0;
        $footer = sprintf($newsTplFoot, $FuncFlag);
        echo  $header.$body.$footer;
        exit;
    }
    
    /**
     * 回复文字消息
     * @param $text 回复消息
     * @return string
     */
    public static function responseText($text){
        $textTpl = "<xml>
					<ToUserName><![CDATA[%s]]></ToUserName>
					<FromUserName><![CDATA[%s]]></FromUserName>
					<CreateTime>%s</CreateTime>
					<MsgType><![CDATA[text]]></MsgType>
					<Content><![CDATA[%s]]></Content>
					</xml>";
        $resultStr = sprintf($textTpl,self::$object['FromUserName'],self::$object['ToUserName'],time(),$text);
        echo $resultStr;
        exit;
    }
    
    /**
     * 发送客服消息
     * @param string $openid 用户openid
     * @param mixed $content 文本内容或图文数组
     * @param string $type news,text
     */
    public static function sendCustomMessage($openid,$content,$type='news'){
        $message = [
            'touser' => $openid,
            'msgtype' => $type,
        ];
        switch ($type){
            case 'text':
                $message['text'] = ['content'=>$content];
                break;
            case 'news':
                $message['news'] = ['articles' => $content];
                break;
        }
        $token = self::getAccessToken();
        $res = false;
        if($token){
            $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$token;
            $message = json_encode($message,JSON_UNESCAPED_UNICODE);
            $re = myHttp::postData($url,$message);
            if(isset($re['errcode']) && $re['errcode'] == 0){
                $res = true;
            }
        }
        return $res;
    }
    
    
    /**
     * 获取accessToken
     * @return mixed
     */
    private static function getAccessToken(){
        $key = self::$config['appid'].'_access_token';
        $token = cache($key);
        if(!$token){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.self::$config['appid'].'&secret='.self::$config['appsecret'];
            $res = myHttp::getData($url);
            if(isset($res['access_token']) && $res['access_token']){
                $token = $res['access_token'];
                cache($key,$token,3600);
            }
        }
        return $token;
    }
    
    /**
     * 获取微信ticket  */
    private static function getJsTicket(){
    	$key = self::$config['appid'].'_ticket';
    	$ticket = cache($key);
    	if (empty($ticket)){
    		$token = self::getAccessToken();
    		if($token){
    			$url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi",$token);
    			$result = myHttp::getData($url);
    			if(isset($result['ticket'])){
    				$ticket = $result['ticket'];
    				cache($key,$ticket,3600);
    			}
    		}
    	}
    	return $ticket;
    }
    
    /**
     * 获取微信js配置     */
    public static function getJsConfig(){
    	$ticket = self::getJsTicket();
    	$result = [];
    	if($ticket){
    		$url = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    		$noncestr = 'kcbook';
    		$timestamp = time();
    		$appid = self::$config['appid'];
    		$string = 'jsapi_ticket='.$ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
    		$signature = sha1($string);
    		$result = ['noncestr'=>$noncestr,'timestamp'=>$timestamp,'signature'=>$signature,'ticket'=>$ticket,'appid'=>$appid,'url'=>$url];
    	}
    	return $result;
    }
    
    /**
     *	将数组转换为xml
     *	@param array $data	要转换的数组
     *	@param bool $root 	是否要根节点
     *	@return string 		xml字符串
     *	@link http://www.cnblogs.com/dragondean/p/php-array2xml.html
     */
    private static function arr2xml($data, $root = true){
        $str = "";
        if($root){$str .= "<xml>";}
        foreach($data as $key => $val){
            $key = preg_replace('/\[\d*\]/', '', $key);
            if(is_array($val)){
                $child = $this->arr2xml($val, false);
                $str .= "<$key>$child</$key>";
            }else{
                $str.= "<$key><![CDATA[$val]]></$key>";
            }
        }
        if($root){$str .= "</xml>";}
        return $str;
    }
    
}