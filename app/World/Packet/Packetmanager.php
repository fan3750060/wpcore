<?php
namespace app\World\Packet;

use app\World\WorldServer;

/**
 *  数据包加解密管理器
 */
class Packetmanager
{
    public static function Worldpacket_encrypter($fd, $param = [])
    {
        if (!$param) {
            return false;
        }

        $Worldpacket_encrypter = isset(WorldServer::$clientparam[$fd]['Worldpacket_encrypter']) ? WorldServer::$clientparam[$fd]['Worldpacket_encrypter'] : '';

        if ($Worldpacket_encrypter) {
            Worldpacket::$send_i = $Worldpacket_encrypter['send_i'];
            Worldpacket::$send_j = $Worldpacket_encrypter['send_j'];
        } else {
            //初始化
            Worldpacket::$send_i = 0;
            Worldpacket::$send_j = 0;
        }

        $encrypter = Worldpacket::encrypter($param[0], $param[1], $param[2], isset($param[3]) ? $param[3] : true);

        $Worldpacket_encrypter = [
            'send_i' => Worldpacket::$send_i,
            'send_j' => Worldpacket::$send_j,
        ];

        WorldServer::$clientparam[$fd]['Worldpacket_encrypter'] = $Worldpacket_encrypter;

        return $encrypter;
    }

    public static function Worldpacket_decrypter($fd, $param = [])
    {
        if (!$param) {
            return false;
        }

        $Worldpacket_decrypter = isset(WorldServer::$clientparam[$fd]['Worldpacket_decrypter']) ? WorldServer::$clientparam[$fd]['Worldpacket_decrypter'] : '';
        if ($Worldpacket_decrypter) {
            Worldpacket::$recv_i = $Worldpacket_decrypter['recv_i'];
            Worldpacket::$recv_j = $Worldpacket_decrypter['recv_j'];
        } else {
            //初始化
            Worldpacket::$recv_i = 0;
            Worldpacket::$recv_j = 0;
        }

        $decrypter = Worldpacket::decrypter($param[0], $param[1], (isset($param[2]) ? $param[2] : true));

        $Worldpacket_decrypter = [
            'recv_i' => Worldpacket::$recv_i,
            'recv_j' => Worldpacket::$recv_j,
        ];

        WorldServer::$clientparam[$fd]['Worldpacket_decrypter'] = $Worldpacket_decrypter;
        return $decrypter;
    }
}
