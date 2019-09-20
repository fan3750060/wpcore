<?php
namespace app\World\Addon;

use app\World\OpCode;
use app\World\Packet\Packetmanager;
use app\World\WorldServer;

/**
 *  插件程序
 */
class AddonHandler
{
    //加载插件处理程序 TODO 实际获取插件
    public static function LoadAddonHandler($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_ADDON_INFO] Client : ' . $fd, 'warning');

        $data = '';
        for ($i = 0; $i < 16; $i++) {
            $data .= pack('V2', 258, 0);
        }
        $data = GetBytes($data);

        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_ADDON_INFO, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata     = array_merge($encodeheader, $data);

        return $packdata;
    }
}
