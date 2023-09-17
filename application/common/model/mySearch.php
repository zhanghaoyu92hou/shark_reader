<?php
namespace app\common\model;
use think\facade\Request;
use think\Validate;

class mySearch{
    
    //重组配置
    private static $config_init = [];
    
    //查询条件
    private static $where = [];
    
    //接收到的数据
    private static $request = [];
    
    //检索字段
    private static $field = [
        'eq',
        'like',
        'between'
    ];
    
    /**
     * 搜索入口
     * @param array $config 搜索配置
     * @param string $method 获取数据方式获取直接赋值数据
     * @return array 搜索条件
     */
    public static function getWhere($config,$method="get"){
        //赋值默认条件
        if(isset($config['default']) && is_array($config['default'])){
            self::$where = $config['default'];
            unset($config['default']);
        }
        $data = [];
        if(is_array($method)){
            $data = $method;
        }else{
            switch ($method){
                case 'get':
                    $data = myRequest::get();
                    break;
                case 'post':
                    $data = myRequest::post();
                    break;
            }
        }
        if($data){
            //判断是否有验证规则
            if(isset($config['rules']) && is_array($config['rules'])){
                $validate = Validate::make($config['rules']);
                $res = $validate->check($data);
                if(!$res){
                    res_return('搜索数据非法');
                }
                unset($config['rules']);
            }
            self::$request = $data;
            self::initConfig($config);
            self::buildWhere();
        }
        return self::$where;
    }
    
    /**
     * 处理查询配置
     * @param array $config
     */
    private static function initConfig($config){
        $init = [];
        if ($config && is_array($config)){
            //构建查询配置
            $field = self::$field;
            foreach ($field as $v){
                $res = self::makeAllConfig($v, $config);
                if($res){
                    $init = array_merge($init,$res);
                }
            }
            self::$config_init = $init;
        }
    }
    
    /**
     * 重组整理条件配置
     * @param string $key
     * @param array $config
     * @return mixed[]
     */
    private static function makeAllConfig($key,$config){
        $init = [];
        if(array_key_exists($key, $config) && $config[$key]){
            $data = self::$request;
            $cur = $config[$key];
            $option = is_array($cur) ? $cur : explode(',', $cur);
            foreach ($option as $v){
                $field = $v;
                $arr = explode(':', $v);
                $other = '';
                if(count($arr) == 2){
                    $data_key = $arr[0];
                    if(in_array($key, ['like','between'])){
                        $specs = explode('%', $arr[1]);
                        if(count($specs) == 2){
                            $field = $specs[0];
                            $other = $specs[1];
                        }else{
                            $field = $arr[1];
                        }
                    }else{
                        $field = $arr[1];
                    }
                }else{
                    $data_key = $v;
                    if(in_array($key, ['like','between'])){
                        $specs = explode('%', $v);
                        if(count($specs) == 2){
                            $data_key = $specs[0];
                            $field = $specs[0];
                            $other = $specs[1];
                        }
                    }
                }
                if(array_key_exists($data_key, $data) && strlen($data[$data_key]) > 0){
                    $one = [
                        'flag' => $key,
                        'field' => $field,
                        'value' => $data[$data_key],
                        'other' => $other
                    ];
                    $init[] = $one;
                }
            }
        }
        return $init;
    }
    
    //构建查询条件
    private static function buildWhere(){
        $init = self::$config_init;
        if($init){
            foreach ($init as $v){
                switch ($v['flag']){
                    case 'eq':
                        $sym = '=';
                        $value = $v['value'];
                        break;
                    case 'like':
                        $sym = 'like';
                        if($v['other']){
                            $value = '%'.$v['other'].$v['value'].$v['other'].'%';
                        }else{
                            $value = '%'.$v['value'].'%';
                        }
                        break;
                    case 'between':
                        $sym = 'between';
                        $exp = explode('~', $v['value']);
                        if(count($exp) != 2){
                            continue;
                        }
                        $start_time = strtotime(trim($exp[0]));
                        $end_time = strtotime(trim($exp[1]));
                        switch ($v['other']){
                            case 'date':
                                $start_date = date('Y-m-d',$start_time);
                                $end_date = date('Y-m-d',$end_time);
                                if($start_date == $end_date){
                                    $sym = '=';
                                    $value = $start_date;
                                }else{
                                    $value = [$start_date,$end_date];
                                }
                                break;
                            case 'datetime':
                                $start_date = date('Y-m-d H:i:s',$start_time);
                                $end_date = date('Y-m-d H:i:s',$end_time);
                                if($start_date == $end_date){
                                    $sym = '=';
                                    $value = $start_date;
                                }else{
                                    $value = [$start_date,$end_date];
                                }
                                break;
                            default:
                                $value = [$start_time,$end_time];
                                break;
                        }
                        break;
                }
                self::setWhere($v['field'], $sym, $value);
            }
        }
    }
    
    /**
     * 保存条件
     * @param string $field
     * @param string $sym
     * @param string $value
     */
    private static function setWhere($field,$sym,$value){
        $where = self::$where;
        if($where){
            foreach ($where as $k=>$v){
                if($v[0] == $field){
                    unset($where[$k]);
                }
            }
        }
        $where[] = [$field,$sym,$value];
        $where = array_values($where);
        self::$where = $where;
    }
}