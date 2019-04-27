<?php
namespace app;
use app\Auth\Authserver;

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
        (new Authserver())->start(); 

        //TODO start WorldServer
    }
}
