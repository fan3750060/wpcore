<?php
define('EXT', '.php');
define('DS', DIRECTORY_SEPARATOR);
defined('CONF_EXT') or define('CONF_EXT', EXT); // 配置文件后缀
defined('APP_PATH') or define('APP_PATH', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
defined('CONF_PATH') or define('CONF_PATH', __DIR__.'/../config'); // 配置文件目录
defined('CORE_PATH') or define('CORE_PATH', __DIR__.'/../core'); 
defined('ROOT_SCRIPT') or define('ROOT_SCRIPT', __DIR__.'/../'); 

//载入Loader类
// require 'Loader.php';
// spl_autoload_register('Loader::autoload'); // 注册自动加载

//使用composer加载
require __DIR__.'/../vendor/autoload.php';

use core\App;
App::run();
