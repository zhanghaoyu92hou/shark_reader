<?php
namespace mihua;

class KeyWorker{
	
	private static $key;
	private static $isPrivate;
	private static $keyFormat;
	private static $keyProvider;

	//加密
	public static function encrypt($data,$key,$keyformat){
		self::$key = $key;
		self::$keyFormat = $keyformat;
		self::_makesure_provider();
		$encrypted = '';
		$r = null;
		if(self::$isPrivate){
			foreach (str_split($data, 117) as $chunk) {
				$r = openssl_private_encrypt($chunk, $encryptData, self::$keyProvider,OPENSSL_PKCS1_PADDING);
				$encrypted .= $encryptData;
			}
		}else{
			foreach (str_split($data, 117) as $chunk) {
				$r = openssl_public_encrypt($chunk, $encryptData, self::$keyProvider,OPENSSL_PKCS1_PADDING);
				$encrypted .= $encryptData;
			}
		}
		return $r?$data = base64_encode($encrypted):null;
	}
	
	//解密
	public static function decrypt($data,$key,$keyformat){
		self::$key = $key;
		self::$keyFormat = $keyformat;
		self::_makesure_provider();
		$data = base64_decode($data);
		$crypto = '';
		foreach (str_split($data,128) as $chunk) {
			if(self::$isPrivate){
				$r= openssl_private_decrypt($chunk,$decrypted,self::$keyProvider,OPENSSL_PKCS1_PADDING);
			}
			else{
				$r= openssl_public_decrypt($chunk,$decrypted,self::$keyProvider,OPENSSL_PKCS1_PADDING);
			}
			$crypto .= $decrypted;
		}
		return $crypto;
	}

	//构建相关参数
	private static function _makesure_provider(){
	    if(self::$keyProvider == null){
    	    self::$isPrivate = strlen(self::$key)>500;
    	    if(self::$keyFormat == 1){
    	    	self::$key = chunk_split(self::$key,64,"\r\n");
    	    	if(self::$isPrivate){
    	    		self::$key = "-----BEGIN PRIVATE KEY-----\r\n".self::$key."-----END PRIVATE KEY-----";
    	    	}else{
    	    		self::$key = "-----BEGIN PUBLIC KEY-----\r\n".self::$key."-----END PUBLIC KEY-----";
    	    	}
    	    }
    		self::$keyProvider = self::$isPrivate ? openssl_pkey_get_private(self::$key) : openssl_pkey_get_public(self::$key);
	    }
	}
}
?>