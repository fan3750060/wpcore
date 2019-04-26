<?php
namespace core;

/**
 * 
 */
class Work
{
	/**
	 * [run 创建进程]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-05-08
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $param [description]
	 * @return  [type]                 [description]
	 */
	public static function run($param=null)
	{
		// 必须加载扩展
		if(!function_exists("pcntl_fork")) die('['.date('Y-m-d H:i:s').']：pcntl extention is must !');

		if(!$param) die('['.date('Y-m-d H:i:s').']：Parameter cannot be empty!'.PHP_EOL);

		pcntl_signal(SIGCHLD, SIG_IGN); //如果父进程不关心子进程什么时候结束,子进程结束后，内核会回收。
		foreach ($param as $value) 
		{
		    $pid = pcntl_fork();
		    switch ($pid) 
		    {
		        case -1:
		            //错误处理：创建子进程失败时返回-1.
		            die('could not fork');
		            break;

		        case 0:
		            //子进程得到的$pid为0, 所以这里是子进程执行的逻辑。
		            // pcntl_exec(config('php_path'),$value);
		            $namespaceclass = '\app\\'.$value['controller'];
		            $newObj = new $namespaceclass();

		            call_user_func_array([$newObj,$value['action']],[$value['param']]);

		            exit(0); // 这里exit掉，避免worker继续执行下面的代码而造成一些问题
		            break;

		        default:
		            //父进程会得到子进程号，所以这里是父进程执行的逻辑
		            pcntl_wait($status, WNOHANG); // pcntl_wait会阻塞，例如直到一个子进程exit
		            // 或者 pcntl_waitpid($pid, $status, WNOHANG); // WNOHANG:即使没有子进程exit，也会立即返回
		            break;
		    }
		}
	}
}