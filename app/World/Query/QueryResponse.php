<?php
namespace app\World\Query;

use app\Common\Srp6;
use app\World\OpCode;
use app\World\Packet\Packetmanager;
use app\World\WorldServer;
use core\query\DB;

/**
 *  查询
 */
class QueryResponse
{
    //人物名称属性
    public static function QueryName($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_NAME_QUERY_RESPONSE] Client : ' . $fd, 'warning');

        // $Srp6 = new Srp6();
        // $guid = HexToDecimal($Srp6->Littleendian($Srp6->BigInteger(ToStr($data), 256)->toHex())->toHex());
        // var_dump($guid);

        $characters = DB::table('characters', 'characters')->where(['guid' => WorldServer::$clientparam[$fd]['player']['guid']])->find();
        $name     = $characters['name'];
        $name_len = strlen($characters['name']);
        $packdata = pack("QZ*cI3c",
            $characters['guid'],
            $name,
            0,
            $characters['race'],
            $characters['gender'],
            $characters['class'],
            0
        );
        $packdata     = GetBytes($packdata);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_NAME_QUERY_RESPONSE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata     = array_merge($encodeheader, $packdata);

        return $packdata;
    }

    //查询时间响应
    public static function QueryTime($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_QUERY_TIME_RESPONSE] Client : ' . $fd, 'warning');

        $packdata     = pack('I2', time(), 0);
        $packdata     = GetBytes($packdata);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_QUERY_TIME_RESPONSE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata     = array_merge($encodeheader, $packdata);
        return $packdata;
    }
}
