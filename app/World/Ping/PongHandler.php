<?php
namespace app\World\Ping;

use app\World\OpCode;

/**
 *  心跳处理
 */
class PongHandler
{
    //响应心跳
    public static function LoadPongHandler($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_PONG] Client : ' . $fd, 'warning');

        $packdata = $data;

        return [OpCode::SMSG_PONG, $packdata];
    }
}
