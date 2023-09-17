<?php
namespace other;

class myHttp{
    
    //get获取数据
    public static function getData($url,$header=null,$back = 'json'){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        }
        $output = curl_exec($ch);
        $error = '';
        if (curl_errno($ch)) {
            $error = curl_error($ch);
        }
        curl_close($ch);
        $result = '';
        if($error){
            res_return($error);
        }else{
            switch ($back){
                case 'json':
                    $result = json_decode($output,true);
                    break;
                case 'xml':
                    $xmlObj = simplexml_load_string($output,'SimpleXMLElement',LIBXML_NOCDATA);
                    $xmlStr = json_encode($xmlObj);
                    $result = json_decode($xmlStr,true);
                    break;
                case 'string':
                    $result = $output;
                    break;
            }
            return $result;
        }
        
    }
    
    //post获取数据
    public static function postData($url,$data=[],$header=null,$back='json'){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        if($header){
            curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        }
        $output = curl_exec($ch);
        $error = '';
        if (curl_errno($ch)){
            $error = curl_error($ch);
        }
        curl_close($ch);
        if($error){
            res_return($error);
        }else{
            switch ($back){
                case 'json':
                    $result = json_decode($output,true);
                    break;
                case 'xml':
                    $xmlObj = simplexml_load_string($output,'SimpleXMLElement',LIBXML_NOCDATA);
                    $xmlStr = json_encode($xmlObj);
                    $result = json_decode($xmlStr,true);
                    break;
                case 'string':
                    $result = $output;
                    break;
            }
            return $result;
        }
        
    }
}