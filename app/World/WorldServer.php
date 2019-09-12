<?php
namespace app\World;

use app\Common\Account;
use app\Common\Checksystem;
use app\World\Message;
use core\lib\Cache;

/**
 * world server
 */
class WorldServer
{
    public static $clientparam = [];

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

        $Account            = new Account();
        $realmlist          = $Account->get_realmlist();
        $this->ServerConfig = $realmlist[0];

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
        WORLD_LOG($str);
        WORLD_LOG('WorldServer version 1.0.1');
        WORLD_LOG('author by.fan <fan3750060@163.com>');
        WORLD_LOG('Gameversion: ' . config('Gameversion'));
        WORLD_LOG('bind server port:' . $this->ServerConfig['address'] . ' ' . $this->ServerConfig['port']);

        // 初始状态
        $this->active = true;

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
            $this->listen('WorldServer'); //开启监听
        } else {
            WORLD_LOG('Error: Did not start the service according to the process...');
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
        $this->serv = new \swoole_server("0.0.0.0", $this->ServerConfig['port'], SWOOLE_BASE, SWOOLE_SOCK_TCP);

        $this->serv->set(array(
            'worker_num'               => 4,
//                 'daemonize' => true, // 是否作为守护进程
            'max_request'              => 10000,
            'heartbeat_check_interval' => 60 * 60, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            // 'log_file'                 => RUNTIME_PATH . 'swoole.log',
            // 'open_eof_check' => true, //打开EOF检测
            'package_eof'              => "###", //设置EOF
            // 'open_eof_split'=>true, //是否分包
            'package_max_length'       => 4096,
        ));

        $this->serv->on('Start', array(
            $this, 'onStart',
        ));
        $this->serv->on('WorkerStart', array(
            $this, 'onWorkerStart',
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

        //清空待连接池
        Cache::drive('redis')->delete('checkconnector');
        
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
        WORLD_LOG("Start");
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
        $this->clearcache($fd);

        WORLD_LOG("Client {$fd} connect");

        WorldServer::$clientparam[$fd]['state'] = Clientstate::Init;

        Connection::saveCheckConnector($fd); //保存连接到待检池

        (new Message())->newConnect($serv, $fd); //首次连接需要告知客户端验证
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
        WORLD_LOG("Get Message From Client {$fd}");

        Connection::update_checkTable($fd);

        (new Message())->serverreceive($serv, $fd, $data);

        WORLD_LOG("Continue Handle Worker");
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
        //断开连接账户下线
        (new Message())->Offline($fd);

        //清空用户信息
        $this->clearcache($fd);

        // 将连接从连接池中移除
        Connection::removeConnector($fd);
        WORLD_LOG("Client {$fd} close connection\n");
    }


    /**
     * 此事件在worker进程/task进程启动时发生
     *
     * @param swoole_server $serv
     * @param int $worker_id
     */
    public function onWorkerStart($serv, $worker_id)
    {
        WORLD_LOG("onWorkerStart");

        // $serv->tick(5000, function ($id) {
        //     $this->tickerEvent($this->serv);
        // });

        // if ($worker_id == 0) {
        //     if (!$serv->taskworker) {
        //         $serv->tick(5000, function ($id) {
        //             $this->tickerEvent($this->serv);
        //         });
        //     } else {
        //         $serv->addtimer(5000);
        //     }

        //     WORLD_LOG("start timer finished");
        // }
    }

    /**
     * 定时任务
     *
     * @param swoole_server $serv
     */
    private function tickerEvent($serv)
    {
        Connection::clearInvalidConnection($serv);
    }

    //清空redis
    private function clearcache($fd)
    {
        WORLD_LOG("Clear Cache");

        unset(WorldServer::$clientparam[$fd]);
    }
}
