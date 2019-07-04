<?php
namespace core\db;

/**
 * mongo操作类
 */
class Mongo
{
    protected static $_instance      = null;
    protected static $instance_token = array();
    public $mongo;
    private $host;
    private $port;
    private $db;
    public $dbname;
    public $user;
    public $password;
    private $table = null;

    /**
     * [__construct 构造方法]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2016-11-15
     * ------------------------------------------------------------------------------
     * @param    [type]     $table [description]
     */
    private function __construct($mongo_host, $mongo_port, $mongo_dbname, $mongo_user, $mongo_password, $authdb)
    {
        try {
            //参数
            $this->host     = $mongo_host;
            $this->port     = $mongo_port;
            $this->dbname   = $mongo_dbname;
            $this->user     = $mongo_user;
            $this->password = $mongo_password;
            $this->authdb   = $authdb;

            $host = 'mongodb://' . $this->user . ':' . $this->password . '@' . $this->host . ':' . $this->port . '/' . $this->authdb;

            $this->mongo = new \MongoDB\Driver\Manager($host);

        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 防止克隆
     *
     */
    private function __clone()
    {}

    /**
     * Singleton instance
     *
     * @return Object
     */
    public static function getInstance($mongo_host, $mongo_port, $mongo_dbname, $mongo_user, $mongo_password, $authdb,$reconnection = false)
    {
        $token = $mongo_host . $mongo_port;

        if (array_key_exists($token, self::$instance_token) && !$reconnection) {
            if (false == (self::$instance_token[$token] instanceof self)) {
                self::$_instance = new self($mongo_host, $mongo_port, $mongo_dbname, $mongo_user, $mongo_password, $authdb);
            } else {
                self::$_instance = self::$instance_token[$token];
            }
        } else {
            self::$_instance              = new self($mongo_host, $mongo_port, $mongo_dbname, $mongo_user, $mongo_password, $authdb);
            self::$instance_token[$token] = self::$_instance;
        }

        return self::$_instance;
    }

    /**
     * [insert 插入数据]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2016-11-15
     * ------------------------------------------------------------------------------
     * @param    array      $doc [description]
     * @return   [type]          [description]
     */
    public function insert($table = null, $doc = [])
    {
        if (!$table) {
            return $this->throwError('表不能为空');
        }

        if (!$doc) {
            return false;
        }

        try {
            $bulk = new \MongoDB\Driver\BulkWrite;

            $bulk->insert($doc);

            $result = $this->mongo->executeBulkWrite($this->dbname . "." . $table, $bulk, new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000));

            $result = $result->getInsertedCount();
            return $result;
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * [remove 删除数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-06-03
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function remove($table = null, $where = [])
    {
        if (!$where) {
            return false;
        }

        try {
            $bulk = new \MongoDB\Driver\BulkWrite;
            $bulk->delete($where, ['limit' => 1]);

            $result = $this->mongo->executeBulkWrite($this->dbname . "." . $table, $bulk, new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000));
            $result = self::object2array($result);
            return $result;
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * [update 修改数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-06-03
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function update($table = null, $where = [], $data = [])
    {
        if (!$table) {
            return $this->throwError('表不能为空');
        }

        if (!$data) {
            return false;
        }

        try {
            $bulk = new \MongoDB\Driver\BulkWrite;
            $bulk->update($where, ['$set' => $data], ['multi' => false, 'upsert' => false]);

            $result = $this->mongo->executeBulkWrite($this->dbname . "." . $table, $bulk, new \MongoDB\Driver\WriteConcern(\MongoDB\Driver\WriteConcern::MAJORITY, 1000));

            $result = $result->getModifiedCount();
            return $result;
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * [find 查询]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2016-11-15
     * ------------------------------------------------------------------------------
     * @param    array      $where [description]
     * @return   [type]            [description]
     */
    public function find($table = null, $where = [])
    {
        if (!$table) {
            return $this->throwError('表不能为空');
        }

        try {

            //排序
            $options['sort'] = isset($where['sort']) && $where['sort'] ? $where['sort'] : array('id' => -1);

            $limit = isset($where['size']) && $where['size'] ? $where['size'] : 0; //返回条数

            if (isset($where['size'])) {
                unset($where['size']);
            }

            if (isset($where['start']) && $where['start']) {
                $start = $where['start'];

                unset($where['start']);

                $options['limit'] = (int) $limit;

                $options['skip'] = (int) $start;
            } else {

                if (isset($where['start'])) {
                    unset($where['start']);
                }

                $options['limit'] = (int) $limit;
            }

            $query = new \MongoDB\Driver\Query($where, $options);

            $result = $this->mongo->executeQuery($this->dbname . "." . $table, $query, new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_PRIMARY_PREFERRED));

            // $result = self::object2array($result);

            $arr = [];
            foreach ($result as $id => $val) {
                $val        = self::object2array($val);
                $arr[]      = $val;
            }
            return $arr;
        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }

    }

    /**
     * [getCount 获取长度]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2016-11-15
     * ------------------------------------------------------------------------------
     * @param    array      $where [description]
     * @return   [type]            [description]
     */
    public function getCount($table = null, $where = [])
    {
        if (!$table) {
            return $this->throwError('表不能为空');
        }

        try {
            if (isset($where['rows'])) {
                unset($where['rows']);
            }

            $arr = ['count' => $table, 'query' => $where];

            $command = new \MongoDB\Driver\Command($arr);

            $result = $this->mongo->executeCommand($this->dbname, $command, new \MongoDB\Driver\ReadPreference(\MongoDB\Driver\ReadPreference::RP_SECONDARY_PREFERRED));
   
            return $result->toArray()[0]->n;

        } catch (MongoException $e) {
            $this->throwError($e->getMessage());
        }
    }

    /**
     * 输出错误信息
     * @param $errorInfo 错误内容
     */
    public function throwError($errorInfo = '')
    {
        return $errorInfo;
    }

    /**
     * [object2array 对象转换成数组]
     * ---------------------------------------------------------
     * @Author   by.fan
     * ---------------------------------------------------------
     * @DateTime 2016-06-23
     * ---------------------------------------------------------
     * @param    [type]     $d [description]
     * @return   [type]        [description]
     */
    public static function object2array($d)
    {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }

        if (is_array($d)) {
            return array_map('self::object2array', $d);
        } else {
            return $d;
        }

    }

}
