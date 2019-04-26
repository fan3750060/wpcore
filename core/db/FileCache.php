<?php
namespace core\db;

/**
 * 文件缓存管理
 */
class FileCache
{  
     /**
     *字符串是每个缓存键前缀的字符串。 当您存储时，这是需要的
     *为了避免不同的应用程序在相同的[[cachePath]]下缓存数据
     *冲突。
     *
     *为确保互操作性，只能使用字母数字字符。
     */
    public $keyPrefix = '';  

    /** 
     * @var 将目录串起来存储缓存文件。 你可以在这里使用路径别名。
     *如果没有设置，它将使用应用程序运行时路径下的“cache”子目录。
     */  
    public $cachePath = './cache';  

    /** 
     * @var 字符串缓存文件后缀。 默认为'.bin'。
     */  
    public $cacheFileSuffix = '.bin';  

    /** 
    * @var 整数子目录的级别来存储缓存文件。 默认为1。
    * 如果系统有大量缓存文件（例如一百万），则可以使用更大的值
    *（通常不大于3）。 使用子目录主要是保证文件系统
    * 没有超过一个文件太多的单个目录。
    */  
    public $directoryLevel = 1;  


    /** 
     * @var 整数应执行垃圾收集（GC）的概率（百万分率）
      *在缓存中存储一段数据时。 默认为10，意味着0.001％的几率。
      *这个数字应该在0到1000000之间。值0意味着根本不会执行GC。
     */  
    public $gcProbability = 1000;  

    /** 
     * @var 整数为新创建的缓存文件设置的权限。
      *这个值将被PHP chmod（）函数使用。 没有umask将被应用。
      *如果未设置，权限将由当前环境决定。
     */  
    public $fileMode;  

    /** 
     * @var 整数为新创建的目录设置的权限。
      *这个值将被PHP chmod（）函数使用。 没有umask将被应用。
      *默认为0775，表示目录是可读写的所有者和组，
      *但只读其他用户。
     */  
    public $dirMode = 0775;  
    

    protected static $_instance = null;

    /** 
     * 初始化 
    */  
    private function __construct($path)  
    {  
        $this->cachePath = $path ? $path : $this->cachePath;
    }  

     /**
     * 防止克隆
     * 
     */
    private function __clone(){}
    
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
    public static function getInstance($path=null)
    {
        if (self::$_instance === null) 
        {
            self::$_instance = new self($path);
        }

        return self::$_instance;
    }  
  
    /**
     * [set 设置缓存]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key      [标识键]
     * @param   [type]          $value    [要缓存的值]
     * @param   integer         $duration [持续时间缓存值将过期的秒数。 0表示永不过期。通过[[get（）]]获取缓存中的相应值时将失效。]
     */
    public function set($key, $value, $duration = 0)  
    {  
        $value = serialize([$value]);  
        $key = $this->buildKey($key);  
        return $this->setValue($key, $value, $duration);  
    }  
  
    /**
     * [get 获取缓存]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [标识键]
     * @return  [type]               [混合存储在缓存中的值，如果值不在缓存中，则返回false，过期，或与缓存数据关联的依赖项已更改。]
     */
    public function get($key)  
    {  
        $key = $this->buildKey($key);  
        $value = $this->getValue($key);  
        if ($value === false) {  
            return $value;  
        } else {  
            $value = unserialize($value);  
        }  
        if (is_array($value)) {  
            return $value[0];  
        } else {  
            return false;  
        }  
    }  
  
    /**
     * [delete 删除缓存]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [标识键]
     * @return  [type]               [如果在删除过程中没有发生错误，则返回布尔值]
     */
    public function delete($key)  
    {  
        $key = $this->buildKey($key);  
  
        return $this->deleteValue($key);  
    }  

    /**
     * [buildKey 构建规范化的标识键]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [要规范化的标识键]
     * @return  [type]               [返回生成的缓存键字符串]
     */
    public function buildKey($key)
    {
        if (is_string($key)) 
        {
            $key = ctype_alnum($key) && mb_strlen($key, '8bit') <= 32 ? $key : md5($key);
        } else {
            $key = md5(json_encode($key,JSON_NUMERIC_CHECK));
        }
  
        return $this->keyPrefix.$key;
    }

    /**
     * [setValue 根据键设置缓存]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key      [标识键]
     * @param   [type]          $value    [要缓存的值]
     * @param   [type]          $duration [缓存的值将过期的秒数。 0表示永不过期]
     * 布尔值 如果值成功存储到缓存中，则为true，否则为false
     */
    protected function setValue($key, $value, $duration)
    {
        $this->gc();
        $cacheFile = $this->getCacheFile($key);  
        if ($this->directoryLevel > 0) {  
            @mkdir(dirname($cacheFile), $this->dirMode, true);  
        }  
        if (@file_put_contents($cacheFile, $value, LOCK_EX) !== false) {  
            if ($this->fileMode !== null) {  
                @chmod($cacheFile, $this->fileMode);  
            }  
            if ($duration <= 0) {  
                $duration = 31536000; // 1 year  
            }  
  
            return @touch($cacheFile, $duration + time());  
        } else {  
            $error = error_get_last();  
            echo "Unable to write cache file '{$cacheFile}': {$error['message']}", __METHOD__;  
            return false;  
        }  
    }

    /**
     * [getValue 使用指定键获取缓存]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [标识键]
     * @return  [type]               [string | boolean存储在缓存中的值，如果值不在缓存中或过期，则返回false。]
     */
    protected function getValue($key)  
    {  
        $this->gc();
        $cacheFile = $this->getCacheFile($key);
        if (@filemtime($cacheFile) > time()) {  
            $fp = @fopen($cacheFile, 'r');  
            if ($fp !== false) {  
                @flock($fp, LOCK_SH);  
                $cacheValue = @stream_get_contents($fp);  
                @flock($fp, LOCK_UN);  
                @fclose($fp);  
                return $cacheValue;  
            }  
        }  
  
        return false;  
    }  
  
    /**
     * [deleteValue 删除指定键的值]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [标识键]
     * @return  [type]               [如果在删除过程中没有发生错误，则返回布尔值]
     */
    protected function deleteValue($key)  
    {  
        $cacheFile = $this->getCacheFile($key);  
  
        return @unlink($cacheFile);  
    }  
  
    /**
     * [getCacheFile 返回给定缓存键的缓存文件路径。]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $key [标识键]
     * @return  [type]               [返回字符串缓存文件路径]
     */
    protected function getCacheFile($key)  
    {  
        if ($this->directoryLevel > 0)
        {
            $base = $this->cachePath;  
            for ($i = 0; $i < $this->directoryLevel; ++$i) 
            {

                if (($prefix = substr($key, $i + $i, 2)) !== false) 
                {
                    $base .= DIRECTORY_SEPARATOR . $prefix;  
                }
            }
            return $base . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;  
        } else {  
            return $this->cachePath . DIRECTORY_SEPARATOR . $key . $this->cacheFileSuffix;  
        }  
    }

    /**
     * [gc 删除过期的缓存文件。]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   boolean         $force       [是否强制执行垃圾回收。默认为false，表示实际的删除发生在[[gcProbability]]指定的概率上。]
     * @param   boolean         $expiredOnly [是否仅删除过期的缓存文件。]
     * @return  [type]                       [如果为false，[[cachePath]]下的所有缓存文件将被删除]
     */
    public function gc($force = false, $expiredOnly = true)  
    {  
        if ($force || mt_rand(0, 1000000) < $this->gcProbability) 
        {  
            $this->gcRecursive($this->cachePath, $expiredOnly);  
        }  
    }

    /**
     * [gcRecursive 递归删除目录下的过期缓存文件。此方法主要由[[gc（）]]使用]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   [type]          $path        [用于删除过期缓存文件的目录。]
     * @param   [type]          $expiredOnly [是否只删除过期的缓存文件。 如果为false，则为全部文件在$ path下将被删除。]
     */
    protected function gcRecursive($path, $expiredOnly)  
    {  

        if (($handle = @opendir($path)) !== false) {  
            while (($file = @readdir($handle)) !== false) 
            {
                if ($file[0] === '.') 
                {
                    continue;  
                }  
                $fullPath = $path . DIRECTORY_SEPARATOR . $file;
                if (is_dir($fullPath)) {  
                    $this->gcRecursive($fullPath, $expiredOnly);  
                    if (!$expiredOnly) {  
                        if (!@rmdir($fullPath)) {  
                            $error = error_get_last();  
                            echo "Unable to remove directory '{$fullPath}': {$error['message']}", __METHOD__;  
                        }  
                    }  
                } elseif (!$expiredOnly || $expiredOnly && @filemtime($fullPath) < time()) {  
                    if (!@unlink($fullPath)) {  
                        $error = error_get_last();  
                        echo "Unable to remove file '{$fullPath}': {$error['message']}", __METHOD__;  
                    }  
                }  
            }  
            closedir($handle);  
        }  
    }  
}  