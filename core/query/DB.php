<?php
namespace core\query;
use core\db\Mypdo;

/**
* 
*/
class DB
{
	static $Dbquery;
	public $table;
	public $database = [];
	protected static $_instance = null;

	public $strfield;//字段
	public $strwhere;//查询条件
	public $strorder;//排序
	public $strgroup;//分组
	public $strlimit;//数据界限
	static $update;//更新时数据库字段

	public $alias; //别名

	public $join = []; //连表查询

	public $union = []; //联合查询

	public $debug = false; //调试模式

	private function __construct($table=null,$databaseselection=null,$dbname = null)
	{
		if($table==null && $databaseselection==null)
		{
			return false;
		}elseif ($table && $databaseselection==null) 
		{
			$this->database = config('database');
		}elseif ($table && $databaseselection) 
		{
			$this->database = $databaseselection ? config($databaseselection) : config('database');
		}
		$this->table  = $table;

		$this->database['dbname'] = $dbname ? $dbname : $this->database['dbname'];//自定义库

		//更新时数据库配置
		self::$update['dbname'] = $this->database['dbname'];
		self::$update['table']  = $table;

		return $this->getconnect();
	}

	/**
	 * [getconnect 获取连接对象]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-08
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function getconnect()
	{
		if($this->database['type'] == 'mysql')
		{
			self::$Dbquery = MyPDO::getInstance($this->database['hostname'],$this->database['hostport'], $this->database['username'], $this->database['password'],$this->database['dbname'], $this->database['charset']);
			return self::$Dbquery;
		}
	}

	/**
	 * [query 执行sql]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @param    [type]     $sql [description]
	 * @return   [type]          [description]
	 */
	public function query($sql,$queryMode = 'All')
	{
		return self::$Dbquery->query($sql,$queryMode);
	}

	/**
	 * [two_dimensional 是否二维数组]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $data [description]
	 * @return  [type]                [是:true,不是:false]
	 */
	public function two_dimensional($data=null)
	{
		if(!$data) return false;

		$all = false;
		foreach ($data as $key => $value) 
		{
			if(is_array($value))
			{
				$all = true;
			}
			break;
		}

		return $all;
	}

	/**
	 * [field 限制字段]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function field($field = null)
	{
		if($field)
		{
			if(is_array($field))
			{
				$field = implode(',', $field);
			}
		}else{
			$field = '*';
		}

		$this->strfield = $field;
		return $this;
	}

	/**
	 * [where 条件]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function where($where = null)
	{
		if(empty($where))
		{
			$this->strwhere = '';
		}else{
			if(is_string($where))
			{
				$this->strwhere = 'where '.$where;
			}elseif (is_array($where)) 
			{
				$wherearray = array();
				foreach ($where as $key => $value) 
				{
					if(is_array($value))
					{
						$value[2] = $this->field_replace($value[2]);
						$wherearray[] = '(`'.$value[0].'` '.$value[1].' '.$value[2].')';
					}else{
						$value = $this->field_replace($value);
					    $wherearray[] = '`'.$key.'` = '.$value;
					}  
				}
				$this->strwhere = 'where '.implode(' and ', $wherearray);
			}
		}
		return $this;
	}

	/**
	 * [field_replace 条件替换过滤]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2019-04-17
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	private function field_replace($value = null)
	{
		if(is_array($value))
		{
			foreach ($value as $k => $v) 
			{
				$value[$k] = str_replace("'", "\'", $v);
				$value[$k] = str_replace('"', '\"', $v);
				$value[$k] = is_int($v) ||  is_float($v) ? $v :  '"'.$v.'"';
			}
		}else{
			$value = str_replace("'", "\'", $value);
			$value = str_replace('"', '\"', $value);
			$value = is_int($value) ||  is_float($value) ? $value :  '"'.$value.'"';
		}
		
		return $value;
	}

	/**
	 * [group 分组]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function group($group=null)
	{
		if(empty($group))
		{
			$this->strgroup = '';
		}else{
			if(is_array($group))
			{
				$this->strgroup = ' group by '.implode(',', $group);
			}else{
				$this->strgroup = ' group by '.$group;
			}
		}
		return $this;
	}

	/**
	 * [order 排序]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function order($order=null)
	{
		if(empty($order))
		{
			$this->strorder = '';
		}else{
			if(is_array($order))
			{
				$orders = array();
				foreach ($order as $key => $value) 
				{
					$orders[]= $key.' '.$value;
				}

				$this->strorder = ' order by '.implode(',', $orders);
			}else{
				$this->strorder = ' order by '.$order;
			}
		}
		return $this;
	}

	/**
	 * [limit 数据界限]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @param    [type]     $limit [description]
	 * @return   [type]            [description]
	 */
	public function limit($limit=null)
	{
		if(empty($limit))
		{
			$this->strlimit = '';
		}else{
			if(is_array($limit))
			{
				$this->strlimit = ' limit '.implode(',', $limit);
			}else{
				$this->strlimit = ' limit '.$limit;
			}
		}
		return $this;
	}

	/**
	 * [alias 别名]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function alias($name=null)
	{
		if(empty($name))
		{
			$this->alias = '';
		}else{
			$this->alias = $name;
		}
		return $this;
	}

	/**
	 * [join 连表查询]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function join($table = null,$on = null,$type='LEFT')
	{
		if(is_array($table))
		{
			foreach ($table as $k => $v) 
			{
				if((isset($v[0]) && $v[0] ) && (isset($v[1]) && $v[1]))
				{
					$this->join[] = array(
						'table' => $v[0],
						'on' 	=> $v[1],
						'type' 	=> isset($v[2]) && $v[2] ? $v[2] : $type
					);
				}
			}
		}else{
			if($table && $on)
			{
				$this->join[] = array(
					'table' => $table,
					'on' 	=> $on,
					'type' 	=> $type
				);
			}
		}
		return $this;
	}

	/**
	 * [union 联合查询]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $param [description]
	 * @return  [type]                 [description]
	 */
	public function union($param=null)
	{
		if(is_array($param))
		{
			foreach ($param as $k => $v) 
			{
				if($v)
				{
					$this->union[] = $v;
				}
			}
		}else{
			if($param)
			{
				$this->union[] = $param;
			}
		}
		return $this;
	}

	/**
	 * [debug 调试]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function debug()
	{
		$this->debug = true;
		return $this;
	}

	/**
	 * [find 查询一个]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function find()
	{
		if($this->strfield)
		{
			$strSql = 'SELECT '.$this->strfield.' FROM ';
		}else{
			$strSql = 'SELECT * FROM ';
		}

		if($this->table)
		{
			if($this->alias)
			{
				$this->table .= ' as '.$this->alias;
			}

			$strSql.= '`'.$this->table.'` ';
		}else{
			return null;
		}

		if($this->join)
		{
			foreach ($this->join as $k => $v) 
			{
				$strSql.= ' '.strtoupper($v['type']).' JOIN '.$v['table'].' ON '.$v['on'];
			}
		}

		if($this->strwhere)
		{
			$strSql.= $this->strwhere.' ';
		}

		if($this->strorder)
		{
			$strSql.= $this->strorder.' ';
		}

		$strSql.= ' limit 1';

		if(empty($this->join) && $this->union)
		{
			$strSql = '('.$strSql.') UNION ';
			$union  = array();
			foreach ($this->union as $k => $v) 
			{
				$union[] = '('.$v.')';
			}
			$strSql.= implode(' UNION ', $union);
		}

		if($this->debug)
		{
			return $strSql;
		}

		return self::$Dbquery->query($strSql,'Row');
	}

	/**
	 * [count 查询数量]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function count()
	{

		$strSql = 'SELECT count(1) as count  FROM ';

		if($this->table)
		{
			if($this->alias)
			{
				$this->table .= ' as '.$this->alias;
			}

			$strSql.= '`'.$this->table.'` ';
		}else{
			return null;
		}

		if($this->join)
		{
			foreach ($this->join as $k => $v) 
			{
				$strSql.= ' '.strtoupper($v['type']).' JOIN '.$v['table'].' ON '.$v['on'];
			}
		}

		if($this->strwhere)
		{
			$strSql.= $this->strwhere.' ';
		}

		if($this->strorder)
		{
			$strSql.= $this->strorder.' ';
		}

		if(empty($this->join) && $this->union)
		{
			$strSql = '('.$strSql.') UNION ';
			$union  = array();
			foreach ($this->union as $k => $v) 
			{
				$union[] = '('.$v.')';
			}
			$strSql.= implode(' UNION ', $union);
		}

		if($this->debug)
		{
			return $strSql;
		}

		return (int)self::$Dbquery->query($strSql,'Row')['count'];
	}

	/**
	 * [select 查询集合]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	public function select()
	{
		if($this->strfield)
		{
			$strSql = 'SELECT '.$this->strfield.' FROM ';
		}else{
			$strSql = 'SELECT * FROM ';
		}

		if($this->table)
		{
			if($this->alias)
			{
				$this->table .= ' as '.$this->alias;
			}

			$strSql.= '`'.$this->table.'` ';
		}else{
			return false;
		}

		if($this->join)
		{
			foreach ($this->join as $k => $v) 
			{
				$strSql.= ' '.strtoupper($v['type']).' JOIN '.$v['table'].' ON '.$v['on'];
			}
		}

		if($this->strwhere)
		{
			$strSql.= $this->strwhere.' ';
		}

		if($this->strgroup)
		{
			$strSql.= $this->strgroup.' ';
		}

		if($this->strorder)
		{
			$strSql.= $this->strorder.' ';
		}

		if($this->strlimit)
		{
			$strSql.= $this->strlimit.' ';
		}

		if(empty($this->join) && $this->union)
		{
			$strSql = '('.$strSql.') UNION ';
			$union  = array();
			foreach ($this->union as $k => $v) 
			{
				$union[] = '('.$v.')';
			}
			$strSql.= implode(' UNION ', $union);
		}

		if($this->debug)
		{
			return $strSql;
		}

		return self::$Dbquery->query($strSql,'All');
	}

	/**
	 * [insert 插入数据(可插入多条)]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-12
	 * ------------------------------------------------------------------------------
	 * @param    [type]     $data [一维数组为单条插入,二维数组为多条插入]
	 * @return   [type]           [单条插入返回最后id,多条插入返回id集合]
	 */
	public function insert($data=null)
	{
		if(empty($data) || is_array($data) == false) return false;

		//是否二维数组
		if($this->two_dimensional($data))
		{
			$strSql = 'INSERT INTO `'.$this->table.'` (`'.implode('`,`', array_keys($data[0])).'`) VALUES ';
			$values = array();
			foreach ($data as $key => $value) 
			{
				$value = $this->field_replace($value);
				$values[] = '('.implode(',', $value).')';
			}

			$strSql.= implode(',',$values);
		}else{
			$value = [];
			foreach ($data as $key => $v) {
				$value[] = $this->field_replace($v);
			}

			$strSql = 'INSERT INTO `'.$this->table.'` (`'.implode('`,`', array_keys($data)).'`) VALUES ('.implode(',', $value).')';
		}

		if($this->debug)
		{
			return $strSql;
		}

		if(self::$Dbquery->execSql($strSql))
		{
			if($this->two_dimensional($data))
			{
				$ids = array();
				$num = self::$Dbquery->lastInsertId();
				for($i=0;$i<count($data);$i++)
				{
					$ids[] = $num+$i;
				}
				return $ids;
			}else{
				return self::$Dbquery->lastInsertId();
			}
		}else{
			return false;
		}
	}

	/**
	 * [update 更新数据]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-26
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $data [一维数组为单条更新,二维数组为多条更新,当设定where条件时,以where为条件,当没有设定where,自动验证参数是否带有主键,以主键更新]
	 * @return  [type]                [TRUE,FALSE]
	 */
	public function update($data=null)
	{
		if(empty($data) || is_array($data) == false) return false;

		//是否二维数组
		if($this->two_dimensional($data))
		{
			$result = $this->PrimaryKey();
			$return = array();

			//循环执行
			$debugsql = [];
			foreach ($data as $key => $value) 
			{
				$strSql = 'UPDATE ';

				if($this->table)
				{
					$strSql.= '`'.$this->table.'` SET ';
				}else{
					return false;
				}

				$param = array();
				foreach ($value as $k => $v) 
				{
					$v = $this->field_replace($v);
					$param[] = ' `'.$k.'` = '.$v;
				}

				$strSql.= implode(',', $param);

				if($this->strwhere)
				{
					$strSql.= ' '.$this->strwhere.' ';
				}else{
					if($result)
					{
						if(array_key_exists($result['COLUMN_NAME'],$value))
						{
							$strSql.= ' WHERE '.$result['COLUMN_NAME'].' = '.$value[$result['COLUMN_NAME']];
						}else{
							return false;
						}
					}else{
						return false;
					}
				}

				if($this->debug)
				{
					$debugsql[] = $strSql;

					if($key+1 >= count($data))
					{
						return $strSql;
					}
				}else{
					if(self::$Dbquery->execSql($strSql) === false)
					{
						return false;
					}
				}
			}

			return true;

		}else{
			$strSql = 'UPDATE ';

			if($this->table)
			{
				$strSql.= '`'.$this->table.'` SET ';
			}else{
				return false;
			}

			$param = array();
			foreach ($data as $k => $v) 
			{
				$v = $this->field_replace($v);
				$param[] = ' `'.$k.'` = '.$v;
			}

			$strSql.= implode(',', $param);

			if($this->strwhere)
			{
				$strSql.= ' '.$this->strwhere.' ';
			}else{
				$result = $this->PrimaryKey();
				if($result)
				{
					if(array_key_exists($result['COLUMN_NAME'],$data))
					{
						$strSql.= ' WHERE '.$result['COLUMN_NAME'].' = '.$data[$result['COLUMN_NAME']];
					}else{
						return false;
					}
				}else{
					return false;
				}
			}

			if($this->debug)
			{
				return $strSql;
			}

			return self::$Dbquery->execSql($strSql);
		}
	}

	/**
	 * [delete 删除]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $data [description]
	 * @return  [type]                [description]
	 */
	public function delete($data=null)
	{
		$strSql = 'DELETE FROM ';

		if($this->table)
		{
			$strSql.= '`'.$this->table.'` ';
		}else{
			return false;
		}

		if($this->strwhere)
		{
			$strSql.= ' '.$this->strwhere.' ';
		}else{

			if(!$data) return false;

			$result = $this->PrimaryKey();
			if($result)
			{
				if(is_array($data))
				{
					$strSql.= ' WHERE '.$result['COLUMN_NAME'].' in ("'.implode('","',$data).'")';
				}else{
					$strSql.= ' WHERE '.$result['COLUMN_NAME'].' = "'.$data.'"';
				}
			}else{
				return false;
			}
		}

		if($this->debug)
		{
			return $strSql;
		}

		return self::$Dbquery->execSql($strSql);
	}

	/**
	 * [Transaction 开启事务]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 */
	public static function Transaction($dbname=null)
	{
		new self('index',$dbname);
		self::$Dbquery->beginTransaction();//开启事务
	}

	/**
	 * [Rollback 回滚]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 */
	public static function Rollback($dbname=null)
	{
		new self('index',$dbname);
		self::$Dbquery->rollback(); //回滚
	}

	/**
	 * [Commit 提交]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 */
	public static function Commit($dbname=null)
	{
		new self('index',$dbname);
		self::$Dbquery->commit(); //提交
	}

	/**
	 * [PrimaryKey 当前表主键]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-27
	 * ------------------------------------------------------------------------------
	 */
	public function PrimaryKey()
	{
		$sql = 'SELECT TABLE_SCHEMA,TABLE_NAME,COLUMN_NAME FROM INFORMATION_SCHEMA. COLUMNS WHERE TABLE_SCHEMA = "'.self::$update['dbname'].'" AND TABLE_NAME = "'.self::$update['table'].'" AND COLUMN_KEY = "PRI"';
		return self::$Dbquery->query($sql,'Row');
	}

	/**
	 * [table ]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-08
	 * ------------------------------------------------------------------------------
	 * @param    [type]     $table [description]
	 * @return   [type]            [description]
	 */
	public static function table($table=null,$databaseselection=null,$dbname=null)
	{
		return new self($table,$databaseselection,$dbname);
	}
}