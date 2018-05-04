# 前言

think-basemodel是基于 >thinkphp5.0 ORM。封装成的一个包（trait）。框架的orm是链式调用，此Basemodel内部也是链式调用orm。封装Basemodel包的目的是：

  - 方便做热点数据缓存；
  
  - 统一的方法调用；
  
  - 自动处理软删除；
  
  - 等等...
  
  - 如果你觉得这个封装包会特别影响你项目php解析文件的效率，请绕道。
    
# 安装指引

    composer require chichoyi/think-basemodel
    
## 安装配置文件
    复制文件 /vendor/chichoyi/think-basemodel/src/config/basemodel.php
    到框架指定文件夹下 /application/extra/

# 配置介绍

    //返回格式是否处理成数组
    'ret_array_format' => true,

    //是否启用软删除
    'soft_delete' => true,
    
    
# <font color="red">使用前注意</font>

<font color="red">此封装包有删除功能，“墙裂”建议每张表都加上软删除的标志字段，如下</font>

    soft_delete  tinyint  unsigned  default 1  comment "0-删除 1-正常 2-暂停"
    

# 使用示例

    namespace app\index\model;
    
    use think\Model;
    use Commaai\traits\Basemodel;
    
    class User extends Model
    {
        use BaseModel;
        
        protected $table = 'user';
        
        //开启自动维护时间戳，TP框架update方法没有自动维护更新时间，Basemodel的包有自动维护
        protected $autoWriteTimestamp = true;
        
        
        //如果表的主键不是id，可以指定主键id
        protected $primaryId = 'tp_user_id';
        
        
        //如果表的软删除字段不是soft_delete,支持自定义，但是一定要用int类型(默认值需要指定为1) 0-删除 1-正常 2-暂停 
        protected $softDelete = 'status';
        
    }
    
    //在controller里面的使用方法参见方法介绍



# 方法介绍

## 新增

### add(array $data)

    - 描述：支持单个或批量添加，成功返回主键id或添加数量
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
        //单个添加 返回主键id
        $addUser = model('index/User')->add(['username' => 'thinkphp', 'age' => 18]);
        
        //批量添加 返回添加数量
        $addUsers = model('index/User')->add([
            ['username' => 'thinkphp1', 'age' => 18],
            ['username' => 'thinkphp2', 'age' => 19]
        ]);
    }

## 删除(只支持软删除)

### delById($id)

    - 描述：通过主键id删除
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
        //传id删除
        $del = model('index/User')->delById(12);
        
    }

### delByField($where, array $whereFunction = [])

    - 描述：通过条件删除
    
    - 参数说明: $whereFunction = []
        - 默认为空,可传高级查询
        - 下同参数不再赘述
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
        //通过条件删除
        $del = model('index/User')->delByField(['name' => 'Mike']);
        
        //使用示例 删除18岁到25岁区间的用户
        $del = model('index/User')->delByField([], [
            'whereBetween' => ['age', [18, 25] ]
        ]);
        
    }
    
    
## 修改

### putById($id, $data, $softDelete = true, array $whereFunction = [])

    - 描述：通过主键id修改
    
    - 参数说明:
    
        - $softDelete = true
        
            - 与配置文件basemodel配置soft_delete的值共同起作用
            - 配置文件basemodel的soft_delte为true,方法里的$soft_delete参数表示是否对软删除的值起作用
            - 下同参数不再赘述
            
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
        //传id修改
        $putById = model('index/User')->putById(12, ['name' => 'Jams']);
        
    }

### put($where, $data, $softDelete = true, array $whereFunction = [])

    - 描述：通过条件修改
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
        //通过条件修改
        $put = model('index/User')->put(['name' => 'Mike'], ['age' => 22]);
        
    }
    
    
## 查询

### getByField($where, $field = '*', $orderBy = [], $softDelete = true, array $whereFunction = [])

    - 描述：通过条件查询
    
    - 参数说明: $orderBy = []
        - 可传数组, 可传字符串, 用法同[TP文档](https://www.kancloud.cn/manual/thinkphp5/118078)
        - 下同参数不再赘述
        
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        $list = model('index/User')->getByField(['age' => 18]);
        
    }

### getOneByField($where, $field = '*', $softDelete = true, array $whereFunction = [])

    - 描述：通过条件查询
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        $getOne = model('index/User')->getOneByField(['name' => 'Mike'], ['age' => 22]);
        
    }
    
### getById($id, $field = '*', $softDelete = true)

    - 描述：通过id查询
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        $getOne = model('index/User')->getById(12);
        
    }
    
    
### getListWithPage($page, $per_num, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = [])

    - 描述：分页列表
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        //获取user_id在1-5区间的分页列表,并且按照主键倒序排列
        $getList = model('index/User')->getListWithPage(1, 10, [], 'name,age', 'coupon_tyep_id desc' true, ['whereBetween' => ['id', [1,5]]]);
        
    }
    
### getListJoinTable($joinTable, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = [])

    - 描述：关联模型的列表
    
    - 参数说明
        - $joinTable 模型关联，可传字符串，数组
        - 下同参数不再赘述
    
    - 使用示例：
    
请先在对应模型定义关联关系

    namespace app\index\model;
    
    use think\Model;
    
    class User extends Model
    {
    
        //一个人可以有多张信用卡
        public function cards(){
            return $this->hasMany('Cards');
        }
        
    }
  
控制器调用
 
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        //列出id在1-10区间用户名下的信用卡
        $getList = model('index/User')->getListJoinTable('cards',['id' => ['<=', 10]);
        
        //比较推荐的用法如下,即关联使用数组闭包
        $getList = model('index/User')->getListJoinTable(
        [
            'cards' =>function($q){
                //可以避免全表查询
                $q->field('id,money');
                
                //可以继续嵌套关联查询
                $q->with('otherTable');
            }
            
        ],
        ['id' => ['<=', 10]
        );
        
    }
  
  
### getListJoinTableWithPage($page, $per_num, $joinTable, $where = [], $field = '*', $orderBy = [], $softDelete = true, $whereFunction = [])

    - 描述：关联模型的分页列表
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        //列出id在1-100区间用户名下的信用卡
        $getList = model('index/User')->getListJoinTable(
        1, 10,
        [
            'cards' =>function($q){
                //可以避免全表查询
                $q->field('id,money');
            }
            
        ],
        ['id' => ['<=', 100]
        );
        
    }
    
### getOneJoinTable($where, $joinTable, $field = '*', $softDelete = true, $whereFunction = [])

    - 描述：关联模型的数据
    
    - 使用示例：
    
    namespace app\index\controller;
    
    use think\controller;
    
    class User extends controller
    {
    
        //列出id为1的用户以及其下的信用卡
        $getList = model('index/User')->getOneJoinTable(
        'id=1',
        [
            'cards' =>function($q){
                //可以避免全表查询
                $q->field('id,money');
            }
            
        ],
        'id,name,age'
        );
        
    }
  
