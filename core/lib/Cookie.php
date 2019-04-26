<?php
namespace core\lib;
use core\Config;

class Cookie
{
	static $initialization;
	static $_instance;
	static $config;

	/**
	 * [__construct description]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 */
	private function __construct()
	{
		if(!self::$initialization)
		{
			$this->initialization();
		}
	}

	/**
	 * [initialization 初始化]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 */
	protected function initialization()
	{
		//加载配置
		self::$config = $config = Config::get('cookie');

		if(isset($config['httponly']) && $config['httponly']) 
		{
            ini_set('session.cookie_httponly',1);
        }

        self::$initialization = true;
	}

	/**
	 * [boot 启动]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public static function boot()
	{
		if(self::$_instance === null) 
		{
            self::$_instance = new self();
        }

        return self::$_instance;
	}

	/**
	 * [set 设置cookie]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key   [参数名称]
	 * @param   [type]          $value [值]
	 * @param   integer         $time  [过期时间]
	 */
	public function set($key = null , $value = null , $time = '_null')
	{
		if(!$key) return false;

		if(empty(self::$initialization))
		{
			self::boot();
		}

		$value  = $this->endecode($value);
		$expire = $time != '_null' && is_int($time) ? $_SERVER['REQUEST_TIME'] + intval($time) : intval(self::$config['expire']);
	
		$expire = $expire === 0 ? time() + 10086*10010 : $expire;

		if (self::$config['setcookie']) {

			if(is_array($value))
			{
				$value = '[ARRAY_COOKIE]'.json_encode($value);
			}

            setcookie($key, $value, $expire, self::$config['path'], self::$config['domain'], self::$config['secure'], self::$config['httponly']);
        }

        $_COOKIE[$key] = $value;

        if(isset($_COOKIE[$key]))
        {
        	return true;
        }else{
        	return false;
        }
	}

	/**
	 * [get 获取cookie]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  [type]               [description]
	 */
	public function get($key = null)
	{
		if(!$key) return false;

		if(empty(self::$initialization))
		{
			self::boot();
		}

		if(isset($_COOKIE[$key]))
		{
			$value = $_COOKIE[$key];

			if (0 === strpos($value, '[ARRAY_COOKIE]'))
			{
				$value = substr($value, 14);
				$value = json_decode($value,true);
			}

			return $this->endecode($value,'decode');
		}else{
			return false;
		}
	}

	/**
	 * [delete 删除cookie]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  [type]               [description]
	 */
	public function delete($key = null)
	{
		if(!$key) return false;

		if(empty(self::$initialization))
		{
			self::boot();
		}

		if(isset($_COOKIE[$key]))
		{
			if (self::$config['setcookie']) 
			{
	            setcookie($key, '', $_SERVER['REQUEST_TIME'] - 3600, self::$config['path'], self::$config['domain'], self::$config['secure'], self::$config['httponly']);
	        }
	        unset($_COOKIE[$key]);
		}
			
		return true;
	}

	/**
	 * [all 获取所有cookie]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function all()
	{
		if(empty(self::$initialization))
		{
			self::boot();
		}

		$result = array();
		foreach ($_COOKIE as $k => $v) 
		{
			if (0 === strpos($v, '[ARRAY_COOKIE]'))
			{
				$v = substr($v, 14);
				$v = json_decode($v,true);
			}

			$result[$k] = $this->endecode($v,'decode');
		}

		return $result;
	}

	/**
	 * [clear 清空cookie]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function clear()
	{
		if(empty(self::$initialization))
		{
			self::boot();
		}

		foreach ($_COOKIE as $k => $v) 
		{
            if (self::$config['setcookie']) 
            {
                setcookie($k, '', $_SERVER['REQUEST_TIME'] - 3600, self::$config['path'], self::$config['domain'], self::$config['secure'], self::$config['httponly']);
            }

            unset($_COOKIE[$k]);
        }

        return true;
	}

	/**
	 * [endecode 转码]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-05
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $param [description]
	 * @return  [type]                 [description]
	 */
	public function endecode($param = null,$type = 'encode')
	{
		if(is_array($param))
		{
			foreach ($param as $k => $v) 
			{
				$param[$k] = $type == 'decode' ? urldecode($v) : urlencode($v);
			}
		}else{
			$param = $type == 'decode' ? urldecode($param) : urlencode($param);
		}

		return $param;
	}
}