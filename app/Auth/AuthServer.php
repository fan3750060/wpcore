<?php
namespace app\Auth;

use app\Auth\Connection;
use app\Auth\Message;
use app\Common\Checksystem;
use app\Socket\SwooleTcp;
use core\lib\Cache;
use core\Work;

/**
 * auth server
 */
class AuthServer
{
    public static $clientparam = [];

    public $active;
    public $ServerConfig;

    public $serv;

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

        $this->runAuthServer();
    }

    public function runAuthServer()
    {
        if ($this->active) {

            $this->ServerConfig = config('LogonServer');

            $this->serv = SwooleTcp::Listen('0.0.0.0', $this->ServerConfig['Port'], new self());

            //清空待连接池
            Cache::drive('redis')->delete('auth_checkconnector');
        } else {
            AUTH_LOG('Error: Did not start the service according to the process...');
        }
    }

    public function onStart($serv)
    {
        // 设置进程名称
        @cli_set_process_title("wow_auth_master");

        AUTH_LOG("Start");
    }

    public function onConnect($serv, $fd, $from_id)
    {
        $this->clearcache($fd);

        AUTH_LOG("Client {$fd} connect");

        //初始化auth状态 0
        AuthServer::$clientparam[$fd]['state'] = Clientstate::Init;

        //保存连接到待检池
        Connection::saveCheckConnector($fd);
    }

    public function onReceive($serv, $fd, $from_id, $data)
    {
        AUTH_LOG("Get Message From Client {$fd}");

        Connection::update_checkTable($fd);

        (new Message())->serverreceive($serv, $fd, $data);

        (new Connection())->update_checkTable($fd);

        AUTH_LOG("Continue Handle Worker");
    }

    public function onClose($serv, $fd, $from_id)
    {
        $this->clearcache($fd);

        Connection::removeConnector($fd);

        AUTH_LOG("Client {$fd} close connection\n");
    }

    public function onWorkerStart($serv, $worker_id)
    {
        AUTH_LOG("onWorkerStart");

        if ($worker_id == 0) {
            if (!$serv->taskworker) {

                $serv->tick(5000, function ($id) use ($serv) {
                    $this->tickerEvent($serv);
                });

            } else {
                $serv->addtimer(5000);
            }

            AUTH_LOG("start timer finished");
        }
    }

    private function tickerEvent($serv)
    {
        Connection::clearInvalidConnection($serv);
    }

    private function clearcache($fd)
    {
        AUTH_LOG("Clear Cache");

        AuthServer::$clientparam[$fd] = [];

        unset(AuthServer::$clientparam[$fd]);
    }
}
