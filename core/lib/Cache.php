<?php
namespace core\lib;
use core\db\FileCache;
use core\db\Memcache;
use core\db\Redis;

/**
 * 缓存管理
 */
class Cache{

	static $drivename;     	//缓存驱动 file,memcache,redis
	static $file_config; 	//文件缓存配置
	static $mem_config;  	//memcache缓存配置
	static $redis_config;	//redis缓存配置

	/**
	 * [__construct 构造函数]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $name [description]
	 */
	private function __construct($name = null)
	{
		self::$drivename = $name;

		switch ($name) 
		{
			case 'redis':
				if(self::$redis_config == false)
				{
					self::$redis_config = config('redis');
				}
				break;
			
			case 'memcache':
				if(self::$mem_config == false)
				{
					self::$mem_config   = config('memcache');
				}
				break;

			default:
				if(self::$file_config == false)
				{
					self::$file_config  = config('cache');
				}
				break;
		}
	}

	/**
	 * [drive 驱动]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   string          $name [description]
	 * @return  [type]                [description]
	 */
	public static function drive($name = 'file')
	{
		return new self($name);
	}

	/**
	 * [has 判断是否存在]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  boolean              [description]
	 */
	public function has($key=null)
	{
		if(!$key) return false;

		if(self::$drivename == 'file')
		{
			$result = FileCache::getInstance(self::$file_config['path'])->get($key);
			return $result ? true : false;
		}elseif(self::$drivename == 'memcache')
		{
			$result = Memcache::getInstance(self::$mem_config)->get($key);
			return $result ? true : false;
		}elseif(self::$drivename == 'redis')
		{
			return Redis::getInstance(self::$redis_config)->has($key);
		}
	}

	/**
	 * [set 设置缓存]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key   [description]
	 * @param   string          $value [description]
	 * @param   integer         $time  [description]
	 */
	public function set($key=null,$value='',$time=0)
	{
		if(!$key) return false;

		if(self::$drivename == 'file')
		{
			return FileCache::getInstance(self::$file_config['path'])->set($key,$value,$time);
		}elseif(self::$drivename == 'memcache')
		{
			return Memcache::getInstance(self::$mem_config)->set($key,$value,$time);
		}elseif(self::$drivename == 'redis')
		{
			return Redis::getInstance(self::$redis_config)->set($key,$value,$time);
		}
	}

	/**
	 * [get 获取缓存]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  [type]               [description]
	 */
	public function get($key=null)
	{
		if(!$key) return false;

		if(self::$drivename == 'file')
		{
			return FileCache::getInstance(self::$file_config['path'])->get($key);
		}elseif(self::$drivename == 'memcache')
		{
			return Memcache::getInstance(self::$mem_config)->get($key);
		}elseif(self::$drivename == 'redis')
		{
			return Redis::getInstance(self::$redis_config)->get($key);
		}
	}

	/**
	 * [delete 删除缓存]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  [type]               [description]
	 */
	public function delete($key=null)
	{
		if(!$key) return false;

		if(self::$drivename == 'file')
		{
			return FileCache::getInstance(self::$file_config['path'])->delete($key);
		}elseif(self::$drivename == 'memcache')
		{
			return Memcache::getInstance(self::$mem_config)->del($key);
		}elseif(self::$drivename == 'redis')
		{
			return Redis::getInstance(self::$redis_config)->rm($key);
		}
	}
}