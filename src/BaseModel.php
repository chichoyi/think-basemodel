<?php
/**
 * Created by PhpStorm.
 * User: chicho
 * Date: 2018/4/25
 * Time: 9:34
 */
namespace Chichoyi\Think;

use think\Db;

trait BaseModel
{
    use Unit;

    /**
     * description 添加数据
     * author chicho
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public function add(array $data)
    {
        if (count($data) == count($data, true)){
            $this->data($data)->save();
            if (isset($this->primaryId))
                return $this->{$this->primaryId};
            else
                return $this->id;
        }
        if ($this->retFormat())
            return count($this->saveAll($data));
        return $this->saveAll($data);
    }

    /**
     * description 通过主键id删除
     * author chicho
     * @param $id
     * @return mixed|string
     */
    public function delById($id){
        if (!config('basemodel.soft_delete')) return '未开启删除标志位';
        return $this->putById($id, [$this->getDeleteColumn() => 0]);
    }

    /**
     * description 通过条件删除
     * author chicho
     * @param $where
     * @param array $whereFunction
     * @return mixed|string
     */
    public function delByField($where, array $whereFunction = []){
        if (!config('basemodel.soft_delete')) return '未开启删除标志位';
        return $this->put($where, [$this->getDeleteColumn() => 0], true, $whereFunction);
    }

    /**
     * description 通过主键修改
     * author chicho
     * @param $id
     * @param $data
     * @param bool $softDelete
     * @param array $whereFunction
     * @return mixed
     */
    public function putById($id, $data, $softDelete = true, array $whereFunction = []){
        return $this->put([$this->getPrimaryId() => $id], $data, $softDelete, $whereFunction);
    }

    /**
     * description 更新数据
     * author chicho
     * @param $where
     * @param $data
     * @param bool $softDelete
     * @param array $whereFunction
     * @return mixed
     */
    public function put($where, $data, $softDelete = true, array $whereFunction = []){
        if ($this->autoWriteTimestamp && $this->updateTime)
            $data[$this->updateTime] = time();
        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        return $model->update($data);
    }

    /**
     * description 获取字段
     * author chicho
     * @param array $except_fields
     * @return array
     */
    public function getField($except_fields = []){
        $fields = Db::getTableInfo($this->table, 'fields');
        if (count($except_fields))
            $fields = array_diff($fields, $except_fields);
        return $fields;
    }

    /**
     * description 通过条件查询
     * author chicho
     * @param $where
     * @param string $field
     * @param array $orderBy
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getByField($where, $field = '*', $orderBy = [], $softDelete = true, array $whereFunction = []){
        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        if (count($orderBy)) $model->order($orderBy);
        $result = $model->field($field)->select();
        return $this->handleSelect($result);
    }

    /**
     * description 根据字段查询一条
     * author chicho
     * @param $where
     * @param string $field
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getOneByField($where, $field = '*', $softDelete = true, array $whereFunction = []){
        $result = $this->getByField($where, $field, [], $softDelete, $whereFunction);
        if (count($result))
            return $result[0];
        return $result;
    }

    /**
     * description 使用主键id查询
     * author chicho
     * @param $id
     * @param string $field
     * @param bool $softDelete
     * @return array
     */
    public function getById($id, $field = '*', $softDelete = true){
        $result = $this->getByField([$this->getPrimaryId() => $id], $field, [], $softDelete);
        if (count($result))
            return $result[0];
        return $result;
    }

    /**
     * description 分页列表
     * author chicho
     * @param $page
     * @param $per_num
     * @param array $where
     * @param string $field
     * @param array $orderBy
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getListWithPage($page, $per_num, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = []){
        $model_count = $this->handleWhere($this, $where, $softDelete);
        $model_count = $this->handleFunction($model_count, $whereFunction);
        if (count($orderBy)) $model_count->order($orderBy);
        $totalRecord = $model_count->count();

        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        if (count($orderBy)) $model->order($orderBy);
        $list = $model->field($field)->page($page.','.$per_num)->select();

        return [
            'list' => $this->handleSelect($list),
            'total_page' => intval(ceil($totalRecord / $per_num)),
            'total_record' => $totalRecord,
            'page' => $page,
            'limit' => $per_num
        ];
    }

    /**
     * description 获取列表关联
     * author chicho
     * @param $joinTable
     * @param array $where
     * @param string $field
     * @param array $orderBy
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getListJoinTable($joinTable, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = []){
        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        if (!empty($orderBy)) $model->order($orderBy);
        $list = $model->with($joinTable)->field($field)->select();
        return $this->handleSelect($list);
    }

    /**
     * description 关联模型的分页列表
     * author chicho
     * @param $page
     * @param $per_num
     * @param $joinTable
     * @param array $where
     * @param string $field
     * @param array $orderBy
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getListJoinTableWithPage($page, $per_num, $joinTable, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = []){

        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        if (!empty($orderBy)) $model->order($orderBy);
        $list = $model->with($joinTable)->page($page.','.$per_num)->field($field)->select();

        $modelCount = $this->handleWhere($this, $where, $softDelete);
        $totalRecord = $this->handleFunction($modelCount, $whereFunction)->count();
        return [
            'list' => $this->handleSelect($list),
            'total_page' => intval(ceil($totalRecord / $per_num)),
            'total_record' => $totalRecord,
            'page' => $page,
            'limit' => $per_num
        ];
    }

    /**
     * description 关联模型的数据
     * author chicho
     * @param $where
     * @param $joinTable
     * @param string $field
     * @param bool $softDelete
     * @param array $whereFunction
     * @return array
     */
    public function getOneJoinTable($where, $joinTable, $field = '*', $softDelete = true, $whereFunction = []){
        $model = $this->handleWhere($this, $where, $softDelete);
        $model = $this->handleFunction($model, $whereFunction);
        $model = $model->field($field);
        $data = $model->with($joinTable)->find();
        return $this->handleSelect($data);
    }


}