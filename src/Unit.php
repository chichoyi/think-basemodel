<?php
/**
 * Created by PhpStorm.
 * User: chicho
 * Date: 2018/5/4
 * Time: 18:10
 */

namespace Chichoyi\Think;


trait Unit
{
    /**
     * description 处理高级查询
     * author chicho
     * @param $model
     * @param $whereFunction
     * @return mixed
     */
    protected function handleFunction($model, $whereFunction){
        if (!count($whereFunction)) return $model;
        foreach ($whereFunction as $key => $value){
            $model = $model->{$key}(...$value);
        }
        return $model;
    }

    /**
     * description 查询条件处理
     * author chicho
     * @param $db
     * @param $where
     * @param bool $softDelete
     * @return mixed
     */
    protected function handleWhere($db, $where, $softDelete = true){
        //处理字符串条件
        if (is_string($where)){
            $db = $db->where($where);
            if ($softDelete && config('basemodel.soft_delete')){
                return $db->where($this->getDeleteColumn(), 1);
            }else{
                return $db;
            }
        }

        if ($softDelete && config('basemodel.soft_delete')) $where[$this->getDeleteColumn()] = 1;
        if (count($where)){
            foreach ($where as $key => $value){
                if (is_array($value)){
                    $db = $db->where($key, $value[0], $value[1]);
                }else{
                    $db = $db->where($key, $value);
                }
            }
        }

        return $db;
    }

    /**
     * description 处理查询得到的结果
     * author chicho
     * @param $data
     * @return array
     */
    protected function handleSelect($data){
        if (count($data) && $this->retFormat()){
            if (!is_object($data[0]) && count($data) == count($data, true))
                return $data->toArray();
            $tmpArr = [];
            foreach ($data as $key => $value)
                $tmpArr[$key] = $value->toArray();
            $data = $tmpArr;
        }
        return $data;
    }

    /**
     * description 获取删除标志
     * author chicho
     * @return bool
     */
    protected function getDeleteColumn(){
        if(isset($this->softDelete))
            return $this->softDelete;
        return 'soft_delete';
    }

    /**
     * description 定义返回格式是否为数组
     * author chicho
     * @return mixed
     */
    protected function retFormat(){
        return config('basemodel.ret_array_format');
    }

    /**
     * description 获取主键id
     * author chicho
     * @return string
     */
    protected function getPrimaryId(){
        return isset($this->primaryId) ? $this->primaryId : 'id';
    }

}