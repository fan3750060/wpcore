<?php
namespace core\lib;
use core\Config;

class Session
{
	static $initialization;
	static $_instance;

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
		$config = Config::get('session');

		if(isset($config['use_trans_sid'])) 
		{
            ini_set('session.use_trans_sid', $config['use_trans_sid'] ? 1 : 0);
        }

        if(isset($config['var_session_id']) && isset($_REQUEST[$config['var_session_id']])) 
        {
            session_id($_REQUEST[$config['var_session_id']]);
        } elseif(isset($config['id']) && !empty($config['id'])) {
            session_id($config['id']);
        }

        if(isset($config['name'])) 
        {
            session_name($config['name']);
        }

        if(isset($config['path'])) 
        {
            session_save_path($config['path']);
        }

        if(isset($config['domain'])) 
        {
            ini_set('session.cookie_domain', $config['domain']);
        }

        if(isset($config['expire'])) 
        {
            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);
        }

        if(isset($config['secure'])) 
        {
            ini_set('session.cookie_secure', $config['secure']);
        }

        if(isset($config['httponly'])) 
        {
            ini_set('session.cookie_httponly', $config['httponly']);
        }

        if(isset($config['use_cookies'])) 
        {
            ini_set('session.use_cookies', $config['use_cookies'] ? 1 : 0);
        }

        if(isset($config['cache_limiter'])) 
        {
            session_cache_limiter($config['cache_limiter']);
        }

        if(isset($config['cache_expire'])) 
        {
            session_cache_expire($config['cache_expire']);
        }

        //选择驱动方式
        if(isset($config['type']) && $config['type'] == 'memcache')
        {
        	if (!extension_loaded('memcache')) 
        	{
	            echo 'not support:memcache';
	        }else{
	        	if(isset($config['hostname']) == false)
	        	{
	        		echo 'Config : not fund hostname';
	        	}elseif(isset($config['hostport']) == false)
	        	{
	        		echo 'Config : not fund hostport';
	        	}else{
	        		ini_set("session.save_handler", "memcache");
					ini_set("session.save_path", "tcp://".$config['hostname'].":".$config['hostport']);
	        	}
	        }
        }elseif(isset($config['type']) && $config['type'] == 'memcached')
        {
        	if (!extension_loaded('memcached')) 
        	{
	            echo 'not support:memcached';
	        }else{
	        	if(isset($config['hostname']) == false)
	        	{
	        		echo 'Config : not fund hostname';
	        	}elseif(isset($config['hostport']) == false)
	        	{
	        		echo 'Config : not fund hostport';
	        	}else{
	        		ini_set("session.save_handler", "memcached");
					ini_set("session.save_path", $config['hostname'].":".$config['hostport']);
	        	}
	        }
        }elseif(isset($config['type']) && $config['type'] == 'redis')
        {
        	if (!extension_loaded('redis')) 
        	{
	            echo 'not support:redis';
	        }else{
	        	if(isset($config['hostname']) == false)
	        	{
	        		echo 'Config : not fund hostname';
	        	}elseif(isset($config['hostport']) == false)
	        	{
	        		echo 'Config : not fund hostport';
	        	}else{
	        		$requirepass = isset($config['requirepass']) && $config['requirepass'] ? '?auth='.$config['requirepass'] : '';
	        		ini_set("session.save_handler", "redis");
					ini_set("session.save_path", "tcp://".$config['hostname'].":".$config['hostport'].$requirepass);
	        	}
	        }
        }

    	//判断是否需要启动session
		if(session_status() !== PHP_SESSION_ACTIVE)
		{
			session_start();
			self::$initialization = true;
		}
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
	 * [all 获取所有session]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function all()
	{
		if(self::$_instance === null) 
		{
            self::$_instance = new self();
        }

        return $_SESSION;
	}

	/**
	 * [set 设置session]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key   [description]
	 * @param   string          $value [description]
	 */
	public function set($key = null,$value = '')
	{
		if(!$key) return false;

		if(empty(self::$initialization))
		{
			self::boot();
		}

		$_SESSION[$key] = $value;

		if(isset($_SESSION[$key]) || $value === '' || $value === null)
		{
			return true;
		}else{
			return false;
		}
		
	}

	/**
	 * [get 获取session]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
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

		if(isset($_SESSION[$key]))
		{
			return $_SESSION[$key];
		}else{
			return false;
		}
	}

	/**
	 * [delete 删除session]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
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

		if(isset($_SESSION[$key]))
		{
			unset($_SESSION[$key]);
		}

		return true;
	}

	/**
	 * [clear 清空session]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-04
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function clear()
	{
		if(empty(self::$initialization))
		{
			self::boot();
		}
		session_unset();
		session_destroy();
		return true;
	}
}