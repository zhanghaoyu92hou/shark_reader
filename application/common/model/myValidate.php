<?php
namespace app\common\model;

use think\Validate;

class myValidate{
    
    /**
     * 处理参数验证
     * @param array $rules 规则
     * @param mixed $field 接收字段
     * @param string $type 提交方式
     * @return mixed|array
     */
    public static function getData($rules,$field,$type='post'){
        $field = is_array($field) ? $field : explode(',', $field);
        $type = strtolower($type);
        $request = [];
        switch ($type){
            case 'post':
                $request = myRequest::post($field);
                break;
            case 'get':
                $request = myRequest::get($field);
                break;
        }
        if(empty($request)){
            res_return('未检测到数据');
        }else{
            $rule = self::getValidateParam($rules,$field);
            $result = self::doValidateData($field,$request,$rule['rule'], $rule['msg']);
            if(count($field) == 1){
                $data = $result[$field[0]];
            }else{
                $data = $result;
            }
            return $data;
        }
    }
    
    /**
     * 数据验证
     * @param array $field 需验证字段
     * @param array $data 需验证数据
     * @param array $rules 验证规则
     * @param array $msgs 验证不通过提示错误信息
     * @return array 验证通过的数据
     */
    private static function doValidateData($field,$data,$rules,$msgs){
        $validate = Validate::make($rules,$msgs);
        $res = $validate->check($data);
        if(!$res){
            res_return($validate->getError());
        }
        $result = [];
        foreach ($field as $v){
            if(array_key_exists($v, $data)){
                $result[$v] = $data[$v];
            }else{
                $result[$v] = '';
            }
        }
        return $result;
    }
    
    /**
     * 重组验证参数
     * @param array 验证规则
     * @return array 验证参数和错误信息
     */
    private static function getValidateParam($rules,$field){
        $rule = $msg = array();
        foreach ($field as $v){
            if(array_key_exists($v, $rules)){
                $rule[$v] = $rules[$v][0];
                $rules_msg = $rules[$v][1];
                foreach ($rules_msg as $k=>$val){
                    $key = $v.'.'.$k;
                    $msg[$key] = $val;
                }
            }
        }
        $res = ['rule'=>$rule,'msg'=>$msg];
        return $res;
    }
}