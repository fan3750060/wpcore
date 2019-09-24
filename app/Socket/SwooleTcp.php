<?php
namespace app\Socket;

/**
 * tcp server
 */
class SwooleTcp
{
    public static function Listen($addr, $port, $object)
    {
        $serv = new \swoole_server($addr, $port);

        $serv->set([
            'worker_num'               => 4,
            //'daemonize' => true, // 是否作为守护进程
            'max_request'              => 10000,
            'heartbeat_check_interval' => 60 * 60, //每隔多少秒检测一次，单位秒，Swoole会轮询所有TCP连接，将超过心跳时间的连接关闭掉
            // 'log_file'                 => RUNTIME_PATH . 'swoole.log',
            // 'open_eof_check' => true, //打开EOF检测
            // 'package_eof'              => "###", //设置EOF
            // 'open_eof_split'=>true, //是否分包
            'package_max_length'       => 4096,
        ]);

        $serv->on('Start', [$object, 'onStart']);

        $serv->on('Connect', [$object, 'onConnect']);

        $serv->on('Receive', [$object, 'onReceive']);

        $serv->on('Close', [$object, 'onClose']);

        $serv->on('WorkerStart', [$object, 'onWorkerStart']);

        $serv->start();

        return $serv;
    }
}
