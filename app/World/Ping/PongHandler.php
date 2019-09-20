<?php
namespace app\World\Ping;

use app\World\OpCode;
use app\World\Packet\Packetmanager;
use app\World\WorldServer;

/**
 *  心跳处理
 */
class PongHandler
{
    //响应心跳
    public static function LoadPongHandler($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_PONG] Client : ' . $fd, 'warning');
        
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_PONG, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata     = array_merge($encodeheader, $data);

        return $packdata;
    }
}
