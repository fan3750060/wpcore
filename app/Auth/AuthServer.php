<?php
namespace app\Auth;

use app\Auth\Connection;
use app\Auth\Message;
use app\Auth\MessageCache;
use app\Common\Checksystem;
use core\Work;

/**
 * auth server
 */
class AuthServer
{
    public $active;
    public $ServerConfig;

    /**
     * [start 开始]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function start()
    {
        Checksystem::check();

        $str = "

 pppp          ppppppppppp         pppppp      pppppp    ppppppppp   ppppppppp  
  ppp   ppp   ppp ppp   ppp       ppp  ppp    ppp  pppp  ppp   pppp  ppp        
  ppp   pppp  ppp ppp    ppp     ppp    ppp  ppp    pppp ppp    ppp  ppp        
  ppp  ppppp  ppp ppp    ppp    ppp     ppp ppp      ppp ppp    ppp  ppp        
   ppp ppppp  ppp ppp    ppp    ppp         ppp      ppp ppp    ppp  ppp        
   ppp pp pp ppp  ppp   ppp     ppp         ppp      ppp ppp   pppp  ppppppppp  
   ppp pp pppppp  pppppppp      ppp         ppp      ppp pppppppp    ppp        
   pppppp pppppp  ppp           ppp         ppp      ppp ppp  pppp   ppp        
    ppppp  pppp   ppp           ppp     ppp ppp      ppp ppp   ppp   ppp        
    pppp   pppp   ppp            ppp    ppp  ppp    pppp ppp    ppp  ppp        
    pppp   pppp   ppp             ppp  pppp   ppp  pppp  ppp    ppp  ppp        
    pppp   pppp   ppp              pppppp      pppppp    ppp    pppp pppppppppp 
        ";
        AUTH_LOG($str);
        AUTH_LOG('AuthServer version 1.0.1');
        AUTH_LOG('author by.fan <fan3750060@163.com>');
        AUTH_LOG('Gameversion: ' . config('Gameversion'));
        AUTH_LOG('bind server port:' . config('LogonServer.Address') . ' ' . config('LogonServer.Port'));

        // 初始状态
        $this->active = true;

        //开启命令进程(windows不进行线程操作)
        if (strpos(strtoupper(PHP_OS), 'WIN') == false) {
            $param[] = ['controller' => 'Command', 'action' => 'run', 'param' => []];
            Work::run($param);
        }

        //开启socket
        $this->runAuthServer();
    }

    /**
     * [runAuthServer 运行服务器]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function runAuthServer()
    {
        if ($this->active) {
            $this->listen('LogonServer'); //开启监听
        } else {
            AUTH_LOG('Error: Did not start the service according to the process...');
        }
    }

    /**
     * [listen 开启服务]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function listen($config = null)
    {
        $this->ServerConfig = config($config);

        $this->serv = new \swoole_server("0.0.0.0", $this->ServerConfig['Port']);

        $this->serv->set(array(
            'worker_num'               => 4,
//                 'daemonize' => true, // 是否作为守护进程
            'max_request'              => 10000,
            'dispatch_mode'            => 2,
            'debug_mode'               => 1,
            'task_worker_num'          => 2,
            'open_cpu_affinity'        => 1,
            'heartbeat_check_interval' => 60 * 1, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            // 'log_file'                 => RUNTIME_PATH . 'swoole.log',
            // 'open_eof_check' => true, //打开EOF检测
            'package_eof'              => "###", //设置EOF
            // 'open_eof_split'=>true, //是否分包
            'package_max_length'       => 1024,
        ));

        $this->serv->on('Start', array(
            $this, 'onStart',
        ));
        $this->serv->on('Connect', array(
            $this, 'onConnect',
        ));
        $this->serv->on('Receive', array(
            $this, 'onReceive',
        ));
        $this->serv->on('Close', array(
            $this, 'onClose',
        ));
        $this->serv->on('WorkerStart', array(
            $this, 'onWorkerStart',
        ));
        // $this->serv->on('Timer', array(
        //     $this,'onTimer',
        // ));
        $this->serv->on('Task', array(
            $this, 'onTask',
        ));
        $this->serv->on('Finish', array(
            $this, 'onFinish',
        ));

        // 创建消息缓存table
        (new MessageCache())->createDataCacheTable();

        $connectionCls = new Connection();
        $connectionCls->createConnectorTable();
        $connectionCls->createCheckTable();

        $this->serv->start();
    }

    /**
     * Server启动在主进程的主线程回调此函数
     *
     * @param unknown $serv
     */
    public function onStart($serv)
    {
        // 设置进程名称
        @cli_set_process_title("swoole_im_master");
        AUTH_LOG("Start");
    }

    /**
     * 有新的连接进入时，在worker进程中回调
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param int $from_id
     */
    public function onConnect($serv, $fd, $from_id)
    {
        AUTH_LOG("Client {$fd} connect");

        // 将当前连接用户添加到连接池和待检池
        $connectionCls = new Connection();
        $connectionCls->saveConnector($fd);
        $connectionCls->saveCheckConnector($fd);
    }

    /**
     * 接收到数据时回调此函数，发生在worker进程中
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param int $from_id
     * @param var $data
     */
    public function onReceive($serv, $fd, $from_id, $data)
    {
        AUTH_LOG("Get Message From Client {$fd}");

        (new Connection())->update_checkTable($fd);

        // send a task to task worker.
        $param = array(
            'fd'   => $fd,
            'data' => base64_encode($data),
        );

        $serv->task(json_encode($param, JSON_UNESCAPED_UNICODE));
        AUTH_LOG("Continue Handle Worker");
    }

    /**
     * TCP客户端连接关闭后，在worker进程中回调此函数
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param int $from_id
     */
    public function onClose($serv, $fd, $from_id)
    {
        // 将连接从连接池中移除
        (new Connection())->removeConnector($fd);
        AUTH_LOG("Client {$fd} close connection\n");
    }

    /**
     * 在task_worker进程内被调用。
     * worker进程可以使用swoole_server_task函数向task_worker进程投递新的任务。
     * 当前的Task进程在调用onTask回调函数时会将进程状态切换为忙碌，这时将不再接收新的Task，
     * 当onTask函数返回时会将进程状态切换为空闲然后继续接收新的Task
     *
     * @param swoole_server $serv
     * @param int $task_id
     * @param int $from_id
     * @param
     *            json string $param
     * @return string
     */
    public function onTask($serv, $task_id, $from_id, $param)
    {
        AUTH_LOG("This Task {$task_id} from Worker {$from_id}");
        $paramArr = json_decode($param, true);
        $fd       = $paramArr['fd'];
        $data     = base64_decode($paramArr['data']);

        (new Message())->serverreceive($serv, $fd, $data);
        return "Task {$task_id}'s result";
    }

    /**
     * 当worker进程投递的任务在task_worker中完成时，
     * task进程会通过swoole_server->finish()方法将任务处理的结果发送给worker进程
     *
     * @param swoole_server $serv
     * @param int $task_id
     * @param string $data
     */
    public function onFinish($serv, $task_id, $data)
    {
        AUTH_LOG("Task {$task_id} finish");
        AUTH_LOG("Result: {$data}");
    }

    /**
     * 此事件在worker进程/task进程启动时发生
     *
     * @param swoole_server $serv
     * @param int $worker_id
     */
    public function onWorkerStart($serv, $worker_id)
    {
        AUTH_LOG("onWorkerStart");

        // 只有当worker_id为0时才添加定时器,避免重复添加
        if ($worker_id == 0) {
            $connectionCls = new Connection();

            // 清除数据
            $connectionCls->clearData();
            AUTH_LOG("clear data finished");

            // 在Worker进程开启时绑定定时器
            // 低于1.8.0版本task进程不能使用tick/after定时器，所以需要使用$serv->taskworker进行判断
            if (!$serv->taskworker) {
                $serv->tick(5000, function ($id) {
                    $this->tickerEvent($this->serv);
                });
            } else {
                $serv->addtimer(5000);
            }
            AUTH_LOG("start timer finished");
        }
    }

    /**
     * 定时任务
     *
     * @param swoole_server $serv
     */
    private function tickerEvent($serv)
    {
        (new Connection())->clearInvalidConnection($serv);
    }
}
