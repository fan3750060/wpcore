<?php
namespace core\db;

/**
 * memcache缓存类
 */
class Memcache{
	private $local_cache = array();
	private $m;
	private $client_type;
	protected $errors = array();
	protected static $_instance = null;
	private $HOST; 			//ip
	private $PORT; 			//端口
	private $EXPIRATION; 	//过期时间
	private $PREFIX; 		//前缀
	private $COMPRESSION; 	//是否压缩


	/**
	 * [__construct 构造方法]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $param [description]
	 */
	private function __construct($param)
	{
		$this->client_type = class_exists('Memcache') ? "Memcache" : (class_exists('Memcached') ? "Memcached" : FALSE);
		
		if($this->client_type)
		{
			$this->HOST 		= $param['hostname'];
			$this->PORT 		= $param['hostport'];
			$this->EXPIRATION 	= $param['expiration'];
			$this->PREFIX 		= $param['prefix'];
			$this->COMPRESSION 	= $param['compression'];

			// 判断引入类型
			switch($this->client_type)
			{
				case 'Memcached':
					$this->m = new \Memcached();
					break;
				case 'Memcache':
					$this->m = new \Memcache();
					// if (auto_compress_tresh){
						// $this->setcompressthreshold(auto_compress_tresh, auto_compress_savings);
					// }
					break;
			}

			$this->auto_connect();	
		}
		else
		{
			echo '['.date('Y-m-d H:i:s').']：ERROR: Failed to load Memcached or Memcache Class (∩_∩)'.PHP_EOL;
			exit;
		}
	}

	/**
     * [getInstance 单例]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $path [description]
     * @return  [type]                [description]
     */
    public static function getInstance($param=array())
    {
        if (self::$_instance === null) 
        {
            self::$_instance = new self($param);
        }

        return self::$_instance;
    }  

	
	/**
	 * @Name: auto_connect
	 * @param:none
	 * @todu 连接memcache server
	 * @return : none
	**/
	private function auto_connect()
	{
		$configServer = array(
								'host' => $this->HOST, 
								'port' => $this->PORT, 
								'weight' => 1, 
							);
		if(!$this->add_server($configServer)){
			echo 'ERROR: Could not connect to the server named '.$this->HOST;
		}else{
			//echo 'SUCCESS:Successfully connect to the server named '.MEMCACHE_HOST;	
		}
	}
	
	/**
	 * @Name: add_server
	 * @param:none
	 * @todu 连接memcache server
	 * @return : TRUE or FALSE
	**/
	public function add_server($server){
		extract($server);
		return $this->m->addServer($host, $port, $weight);
	}
	
	/**
	 * @Name: add_server
	 * @todu 添加
	 * @param:$key key
	 * @param:$value 值
	 * @param:$expiration 过期时间
	 * @return : TRUE or FALSE
	**/
	public function add($key = NULL, $value = NULL, $expiration = 0)
	{
		if(is_null($expiration)){
			$expiration = $this->EXPIRATION;
		}
		if(is_array($key))
		{
			foreach($key as $multi){
				if(!isset($multi['expiration']) || $multi['expiration'] == ''){
					$multi['expiration'] = $this->EXPIRATION;
				}
				$this->add($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
			}
		}else{
			$this->local_cache[$this->key_name($key)] = $value;
			switch($this->client_type){
				case 'Memcache':
					$add_status = $this->m->add($this->key_name($key), $value, $this->COMPRESSION, $expiration);
					break;
					
				default:
				case 'Memcached':
					$add_status = $this->m->add($this->key_name($key), $value, $expiration);
					break;
			}
			
			return $add_status;
		}
	}
	
	/**
	 * @Name   与add类似,但服务器有此键值时仍可写入替换
	 * @param  $key key
	 * @param  $value 值
	 * @param  $expiration 过期时间
	 * @return TRUE or FALSE
	**/
	public function set($key = NULL, $value = NULL, $expiration = NULL)
	{
		if(is_null($expiration)){
			$expiration = $this->EXPIRATION;
		}
		if(is_array($key))
		{
			foreach($key as $multi){
				if(!isset($multi['expiration']) || $multi['expiration'] == ''){
					$multi['expiration'] = $this->config['config']['expiration'];
				}
				$this->set($this->key_name($multi['key']), $multi['value'], $multi['expiration']);
			}
		}else{
			$this->local_cache[$this->key_name($key)] = $value;
			switch($this->client_type){
				case 'Memcache':
					$add_status = $this->m->set($this->key_name($key), $value, $this->COMPRESSION, $expiration);
					break;
				case 'Memcached':
					$add_status = $this->m->set($this->key_name($key), $value, $expiration);
					break;
			}
			return $add_status;
		}
	}
	
	/**
	 * @Name   get 根据键名获取值
	 * @param  $key key
	 * @return array OR json object OR string...
	**/
	public function get($key = NULL)
	{
		if($this->m)
		{
			if(isset($this->local_cache[$this->key_name($key)]))
			{
				return $this->local_cache[$this->key_name($key)];
			}
			if(is_null($key)){
				$this->errors[] = 'The key value cannot be NULL';
				return FALSE;
			}
			
			if(is_array($key)){
				foreach($key as $n=>$k){
					$key[$n] = $this->key_name($k);
				}
				return $this->m->getMulti($key);
			}else{
				return $this->m->get($this->key_name($key));
			}
		}else{
			return FALSE;
		}		
	}
	
	/**
	 * @Name   del
	 * @param  $key key
	 * @param  $expiration 服务端等待删除该元素的总时间
	 * @return true OR false
	**/
	public function del($key, $expiration = NULL)
	{
		if(is_null($key))
		{
			$this->errors[] = 'The key value cannot be NULL';
			return FALSE;
		}
		
		if(is_null($expiration))
		{
			$expiration = $this->EXPIRATION;
		}
		
		if(is_array($key))
		{
			foreach($key as $multi)
			{
				$this->delete($multi, $expiration);
			}
		}
		else
		{
			unset($this->local_cache[$this->key_name($key)]);
			return $this->m->delete($this->key_name($key), $expiration);
		}
	}

	/**
	 * [delsession 删除session共享]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2017-12-29
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key [description]
	 * @return  [type]               [description]
	 */
	public function delsession($key,$expiration = NULL)
	{
		if(is_null($expiration))
		{
			$expiration = $this->EXPIRATION;
		}
		
		return $this->m->delete($key, $expiration);
	}
	
	/**
	 * @Name   replace
	 * @param  $key 要替换的key
	 * @param  $value 要替换的value
	 * @param  $expiration 到期时间
	 * @return none
	**/
	public function replace($key = NULL, $value = NULL, $expiration = NULL)
	{
		if(is_null($expiration)){
			$expiration = $this->EXPIRATION;
		}
		if(is_array($key)){
			foreach($key as $multi)	{
				if(!isset($multi['expiration']) || $multi['expiration'] == ''){
					$multi['expiration'] = $this->config['config']['expiration'];
				}
				$this->replace($multi['key'], $multi['value'], $multi['expiration']);
			}
		}else{
			$this->local_cache[$this->key_name($key)] = $value;
			
			switch($this->client_type){
				case 'Memcache':
					$replace_status = $this->m->replace($this->key_name($key), $value, $this->COMPRESSION, $expiration);
					break;
				case 'Memcached':
					$replace_status = $this->m->replace($this->key_name($key), $value, $expiration);
					break;
			}
			
			return $replace_status;
		}
	}
	
	/**
	 * @Name   replace 清空所有缓存
	 * @return none
	**/
	public function flush()
	{
		return $this->m->flush();
	}
	
	/**
	 * @Name   获取服务器池中所有服务器的版本信息
	**/
	public function getversion()
	{
		return $this->m->getVersion();
	}
	
	
	/**
	 * @Name   获取服务器池的统计信息
	**/
	public function getstats($type="items")
	{
		switch($this->client_type)
		{
			case 'Memcache':
				$stats = $this->m->getStats($type);
				break;
			
			default:
			case 'Memcached':
				$stats = $this->m->getStats();
				break;
		}
		return $stats;
	}
	
	/**
	 * @Name: 开启大值自动压缩
	 * @param:$tresh 控制多大值进行自动压缩的阈值。
	 * @param:$savings 指定经过压缩实际存储的值的压缩率，值必须在0和1之间。默认值0.2表示20%压缩率。
	 * @return : true OR false
	**/
	public function setcompressthreshold($tresh, $savings=0.2)
	{
		switch($this->client_type)
		{
			case 'Memcache':
				$setcompressthreshold_status = $this->m->setCompressThreshold($tresh, $savings=0.2);
				break;
				
			default:
				$setcompressthreshold_status = TRUE;
				break;
		}
		return $setcompressthreshold_status;
	}
	
	/**
	 * @Name: 
	 * @param:$key key
	 * @return : md5 string
	**/
	private function key_name($key)
	{
		return $this->PREFIX.$key;
	}
	
	/**
	 * @Name: 向已存在元素后追加数据
	 * @param:$key key
	 * @param:$value value
	 * @return : true OR false
	**/
	public function append($key = NULL, $value = NULL)
	{


//		if(is_array($key))
//		{
//			foreach($key as $multi)
//			{
//
//				$this->append($multi['key'], $multi['value']);
//			}
//		}
//		else
//		{
			$this->local_cache[$this->key_name($key)] = $value;
			
			switch($this->client_type)
			{
				case 'Memcache':
					$append_status = $this->m->append($this->key_name($key), $value);
					break;
				
				default:
				case 'Memcached':
					$append_status = $this->m->append($this->key_name($key), $value);
					break;
			}
			
			return $append_status;
//		}
	}//END append


}// END class
?>