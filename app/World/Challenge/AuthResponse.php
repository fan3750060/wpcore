<?php
namespace app\World\Challenge;

use app\World\OpCode;
use app\World\Packet\Packetmanager;
use app\World\WorldServer;
use app\Common\Srp6;

/**
 *  验证响应
 */
class AuthResponse
{
    //验证响应
    public static function LoadAuthResponse($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_AUTH_RESPONSE] Client : ' . $fd, 'warning');

        $Srp6 = new Srp6();
        $AUTH_OK              = $Srp6->BigInteger(OpCode::AUTH_OK, 16)->toString();
        $BillingTimeRemaining = PackInt(0, 32);
        $BillingPlanFlags     = PackInt(0, 8);
        $BillingTimeRested    = PackInt(0, 32);
        $expansion            = [(int) $data['data']['expansion']];
        $packdata             = array_merge([(int) $AUTH_OK], $BillingTimeRemaining, $BillingPlanFlags, $BillingTimeRested, $expansion);
        $encodeheader         = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_AUTH_RESPONSE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata             = array_merge($encodeheader, $packdata);

        return $packdata;
    }
}
