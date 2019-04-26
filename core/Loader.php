<?php
class Loader
{
    /* 路径映射 */
    public static $vendorMap;

    /**
     * [autoload 自动加载器]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2017-12-06
     * ------------------------------------------------------------------------------
     * @param    [type]     $class [description]
     * @return   [type]            [description]
     */
    public static function autoload($class)
    {
    	self::getmap();
        $file = self::findFile($class);
        if (file_exists($file)) {
            self::includeFile($file);
        }
    }

    /**
     * [getmap 加载映射]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2017-12-06
     * ------------------------------------------------------------------------------
     * @return   [type]     [description]
     */
    private static function getmap()
    {
    	self::$vendorMap = array(
            'app' => __DIR__.DIRECTORY_SEPARATOR.'../app',
	    	'core' => __DIR__.DIRECTORY_SEPARATOR.'../core',
	    );
    }

    /**
     * [findFile 解析文件路径]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2017-12-06
     * ------------------------------------------------------------------------------
     * @param    [type]     $class [description]
     * @return   [type]            [description]
     */
    private static function findFile($class)
    {
        $vendor = substr($class, 0, strpos($class, '\\')); // 顶级命名空间

        $vendorDir = self::$vendorMap[$vendor]; // 文件基目录

        $filePath = substr($class, strlen($vendor)) . '.php'; // 文件相对路径

        return strtr($vendorDir . $filePath, '\\', DIRECTORY_SEPARATOR); // 文件标准路径
    }

    /**
     * [includeFile 引入文件]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2017-12-06
     * ------------------------------------------------------------------------------
     * @param    [type]     $file [description]
     * @return   [type]           [description]
     */
    private static function includeFile($file)
    {
        try {  
            if (!file_exists($file)) {  
                throw new Exception('controller ' . $controller . ' is not exists!');  
                return;  
            }  
            include($file);  
        } catch (Exception $e) {  
            echo $e; //展示错误结果  
            return;  
        }
    }
}