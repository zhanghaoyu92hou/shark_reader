<?php
namespace app\index\model;
use think\Db;
class Common{
    
    /**
     * 查询列表
     * @param string $table 数据表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return []
     */
    public static function getList($table,$where,$field='*'){
        $list = Db::name($table)->where($where)->field($field)->order('id','desc')->select();
        return $list;
    }
    
    /**
     * 查询分页列表
     * @param string $table 数据表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return []
     */
    public static function getPageList($table,$where,$field,$pages){
        $list = Db::name($table)->where($where)->field($field)->page($pages['page'],$pages['limit'])->order('id','desc')->select();
        $count = 0;
        if($list){
            $count = Db::name($table)->where($where)->count();
        }
        return ['data'=>$list,'count'=>$count];
    }
    
    /**
     * 获取指定的一条记录
     * @param string $table 查询数据表
     * @param array $where 查询条件
     * @param string $field 查询字段
     * @return array|NULL
     */
    public static function getCur($table,$where,$field='*'){
        $cur = Db::name($table)->where($where)->field($field)->find();
        return $cur;
    }
    
    /**
     * 获取指定的一条记录
     * @param string $table 查询数据表
     * @param array $id 主键
     * @param string $field 查询字段
     * @return array|NULL
     */
    public static function getById($table,$id,$field='*'){
        $cur = Db::name($table)->where('id','=',$id)->field($field)->find();
        return $cur;
    }
    
    /**
     * 修改字段值
     * @param string $table 数据表
     * @param array $where 条件
     * @param string $field 更改字段
     * @param string $value 值
     * @return boolean
     */
    public static function setField($table,$where,$field,$value){
        $flag = false;
        $re = Db::name($table)->where($where)->setField($field,$value);
        if($re !== false){
            $flag = true;
        }
        return $flag;
    }
    
    /**
     * 修改数据
     * @param string $table 数据表
     * @param array $where 条件
     * @param array $data 保存数据
     * @return boolean
     */
    public static function save($table,$where,$data){
        $flag = false;
        $re = Db::name($table)->where($where)->update($data);
        if($re !== false){
            $flag = true;
        }
        return $flag;
    }
    
    /**
     * 修改数据
     * @param string $table 数据表
     * @param array $data 保存数据
     * @return boolean
     */
    public static function saveIdData($table,$data){
        $id = $data['id'];
        unset($data['id']);
        $flag = false;
        $re = Db::name($table)->where('id','=',$id)->update($data);
        if($re !== false){
            $flag = true;
        }
        return $flag;
    }
    
    /**
     * 添加数据
     * @param string $table 数据表
     * @param array $data 新增数据
     * @return boolean
     */
    public static function add($table,$data){
        $flag = false;
        $re = Db::name($table)->insert($data);
        if($re){
            $flag = true;
        }
        return $flag;
    }
    
    //行数
    public static function getCount($table,$where){
        $count = Db::name($table)->where($where)->count();
        return $count;
    }
    
    /**
     * 删除数据
     * @param string $table 数据表
     * @param number $id 主键
     * @return boolean
     */
    public static function delById($table,$id){
        $flag = false;
        $re = Db::name($table)->where('id','=',$id)->delete();
        if($re){
            $flag = true;
        }
        return $flag;
    }
    
    /**
     * 获取新增数据所需字段
     * @param string $field 字段列表
     * @return
     */
    public static function buildArr($field){
        $arr = is_array($field) ? $field : explode(',', $field);
        $cur = [];
        foreach ($arr as $v){
            $temp = explode(':', $v);
            if(count($temp) == 1){
                $cur[$v] = '';
            }else{
                $cur[$temp[0]] = $temp[1];
            }
        }
        return $cur;
    }
}