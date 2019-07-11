<?php
namespace app;

use app\Auth\AuthServer;
use app\World\WorldServer;

/**
 * service
 */
class Server
{
    public $active;

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
        $param = input();

        if ($param[0] == 'auth') {
            (new AuthServer())->start();
        } elseif ($param[0] == 'world') {
            (new WorldServer())->start();
        }
    }
}
