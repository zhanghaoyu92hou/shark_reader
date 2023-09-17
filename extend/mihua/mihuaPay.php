<?php
namespace mihua;


class mihuaPay {
	
	public static $config;
	public static function doPay($params){
		$data = [
			'merAccount'=>self::$config['merAccount'],
			'merNo'=>self::$config['merNo'],
			'amount'=>$params['total_fee']*100,
			'product'=>$params['body'],
			'payWay'=>'WEIXIN',
			'payType'=>'JSAPI_WEIXIN',
			'userIp'=>$params['ip'],
			'orderId'=>$params['out_trade_no'],
			'time'=>time(),
			'returnUrl'=>self::$config['back_url'],
			'notifyUrl'=>self::$config['notify_url'],
			'openId'=>$params['openid'],
			'attach'=> $params['attach']
		];
		$data['sign'] = self::getSign($data, self::$config['privateKey']);
		$encode_data = self::encryptData($data, self::$config['privateKey']);
		$url = 'https://pay.mihuajinfu.com/paygateway/mbpay/order/v1';
		$post_data = [
			'merAccount' => self::$config['merAccount'],
			'data' => $encode_data
		];
		$res = self::httpPost($url,$post_data);
		if(isset($res['code']) && $res['code'] === '000000'){
			return $res['data']['payUrl'];
		}else{
			return false;
		}
	}
	
	//创建签名
	private static function getSign($params,$signKey)
	{
		ksort($params);
		$data = "";
		foreach ($params as $key => $value) {
			$data .= $value;
		}
		$sign = strtoupper(md5($data.$signKey));
		return $sign;
	}
	
	//加密数据
	public static function encryptData($params)
	{
		ksort($params);
		$data = KeyWorker::encrypt(json_encode($params),self::$config['privateKey'],1);
		return $data;
	}
	
	//解密数据
	public static function decryptData($params)
	{
		$data = KeyWorker::decrypt($params, self::$config['publicKey'],1);
		return $data;
	}
	
	//验证签名
	public static function checkSign($params,$signKey) //验签
	{
		ksort($params);
		$psign = "";
		$data = "";
		foreach ($params as $key => $value) {
			if($key == "sign") {
				$psign = $value;
			} else {
				$data .= $value;
			}
		}
		$sign = strtoupper(md5($data.$signKey));
		if($psign == $sign) {
			return true;
		} else {
			return false;
		};
	}
	
	/**
	 * 模拟POST提交
	 * @author 四川挚梦科技有限公司
	 * @date 2018-04-26
	 */
	public static function httpPost($url, $postfields = null)
	{
		$ci = curl_init();
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE);
		curl_setopt($ci, CURLOPT_TIMEOUT, 30);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ci, CURLOPT_POST, true);
		curl_setopt($ci, CURLOPT_POSTFIELDS, http_build_query($postfields));
		$headers = array('application/x-www-form-urlencoded;charset=utf-8');
		curl_setopt($ci, CURLOPT_URL, $url);
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLINFO_HEADER_OUT, true);
		$response = curl_exec($ci);
		curl_close($ci);
		$response = (array)json_decode($response,true);
		return $response;
	}	
}

?>  