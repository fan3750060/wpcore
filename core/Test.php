<?php
namespace core;

/**
 * 
 */
class Test
{
	/**
	 * [run 默认进程]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-05-08
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function run()
	{
		$string = <<<eof

╔╗╔╗ ╔╦╗  ╔══╗   ╔╗　　　　　
║╚╝╠═╣║╠═╗║══╬═╦╦╬╬═╦══╗
║╔╗║╩╣║║║║╠══║╠╣╔╣║║╠╗╔╝
╚╝╚╩═╩╩╩═╝╚══╩═╩╝╚╣╔╝╚╝　
                  ╚╝

The program run successfully
When you want to execute a script, be sure to have parameters and use '/' split controllers and methods!
Example: php script Index/run param1 param2 param3 ...
Version: 0.2.1
Author : fan3750060@163.com　　
　　　　　　　　　　　　　　　　　
eof;
        echo $string.PHP_EOL;
	}


}