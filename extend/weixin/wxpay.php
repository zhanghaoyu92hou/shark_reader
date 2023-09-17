<?php
namespace weixin;

class wxpay{
    
    public static $config;
    
    /**
     * 统一下单配置参数
     * @param unknown $params
     * @return unknown|boolean
     */
    public static function getPayMsg($params){
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $onoce_str = self::createNoncestr();
        $data = [
            'appid' => self::$config['APPID'],
            'mch_id' => self::$config['MCHID'],
            'notify_url' => self::$config['notify_url'],
            'total_fee' => $params['total_fee']*100,
            'attach' => $params['attach'],
            'trade_type' => $params['trade_type'],
            'body' => $params['body'],
            'out_trade_no' => $params['out_trade_no'],
            'nonce_str' => self::createNoncestr(),
            'spbill_create_ip' => self::get_client_ip(),
        ];
        if($params['trade_type'] == 'JSAPI'){
            if(isset($params['openid'])){
                $data['openid'] = $params['openid'];
            }
        }
        $sign = self::getSign($data,false);
        $data["sign"] = $sign;
        $xml = self::arrayToXml($data);
        $response = self::postXmlCurl($xml, $url);
        $response = self::xmlToArray($response);
        $result = false;
        if(isset($response['prepay_id'])){
            switch ($params['trade_type']){
                case 'JSAPI':
                    $result = self::getJsOrder($response['prepay_id']);
                    break;
                case 'MWEB':
                    $result = isset($response['mweb_url']) ? $response['mweb_url'] : false;
                    break;
                case 'NATIVE':
                    $result = isset($response['code_url']) ? $response['code_url'] : false;
                    break;
                case 'APP':
                    $result = self::getAppOrder($response['prepay_id']);
                    break;
            }
        }
        return $result;
    }
    
    /**
     * 拼接公众号支付所需参数
     * @param string $prepayId
     * @return array
     */
    private static function getJsOrder($prepayId){
    	$data = [
    		'appId' => self::$config['APPID'],
    		'nonceStr' => self::createNoncestr(),
    		'package' => "prepay_id=".$prepayId,
    		'timeStamp' => time().'',
    		'signType' => 'MD5'
    	];
        $sign = self::getSign($data, false);
        $data["paySign"] = $sign;
        return $data;
    }
    
    /**
     * 拼接App支付所需参数
     * @param unknown $prepayId
     * @return unknown
     */
    private static function getAppOrder($prepayId){
        $data["appid"] = self::$config["APPID"];
        $data["noncestr"] = self::createNoncestr();
        $data["package"] = "Sign=WXPay";
        $data["partnerid"] = self::$config['MCHID'];
        $data["prepayid"] = $prepayId;
        $data["timestamp"] = time();
        $sign = self::getSign($data, false);
        $data["sign"] = $sign;
        return $data;
    }
    
    /**
     * 生成签名
     * @param array $Obj
     * @return string
     */
    public static function getSign($Obj){
        $Parameters = array();
        foreach ($Obj as $k => $v){
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = self::formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".self::$config['APIKEY'];
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    
    
    /**
     *  作用：产生随机字符串，不长于32位
     */
    private static function createNoncestr( $length = 32 ){
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
    
    
    //数组转xml
    private static function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val){
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    
    
    /**
     *  作用：将xml转为array
     */
    private static function xmlToArray($xml){
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }
    
    
    /**
     *  作用：以post方式提交xml到对应的接口url
     */
    private static function postXmlCurl($xml,$url,$second=30){
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        
        if($data){
            curl_close($ch);
            return $data;
        }
    }
    
    
    /*
     获取当前服务器的IP
     */
    private static function get_client_ip(){
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
    
    
    /**
     *  作用：格式化参数，签名过程需要使用
     */
    private static function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v){
            if($urlencode){
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0){
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
}