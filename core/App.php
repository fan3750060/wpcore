<?php
namespace core;
use core\Route;
use core\Config;
use Dotenv\Dotenv;

class App{

	/**
	 * [run 运行]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function run()
	{
		self::loadfile(); //加载默认文件
		self::loadconfig(); //加载配置文件
		Route::executeroute();//加载路由
	}

	/**
	 * [loadfile 默认加载文件]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function loadfile()
	{
		$dotenv = Dotenv::create(BASE_PATH);//加载env
        $dotenv->load();
        
		$loadfile = array(
			'common' => CORE_PATH.DIRECTORY_SEPARATOR.'Common.php',
		);

		foreach ($loadfile as $key => $value) {
			include($value);
		}
	}

	/**
	 * [loadconfig 加载配置文件]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function loadconfig()
	{
		// 加载模块配置
        Config::load(CONF_PATH. DIRECTORY_SEPARATOR . 'config' . EXT);
        Config::load(CONF_PATH. DIRECTORY_SEPARATOR . 'database' . EXT);
	}
}