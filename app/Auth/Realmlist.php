<?php
namespace app\Auth;

use app\Common\Account;
use app\Common\Srp6;

/**
 *
 */
class Realmlist
{
    /**
     * [get_realmlist 获取世界服务器配置]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-01
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function get_realmlist($param)
    {
        $Account    = new Account();
        $realmlist  = $Account->get_realmlist();
        $num_player = $Account->get_realmlistuserinfo($param);
        $data       = $this->getRealmInfo($realmlist, $num_player);
        return $data;
    }

    public function get_realm_packet($realmlist = null, $num_player = 0)
    {
        $packet = '';
        foreach ($realmlist as $k => $RealmInfo) {
            $name          = $RealmInfo['name']; #服务器名称
            $address       = $RealmInfo['address'] . ':' . $RealmInfo['port']; #服务器ip
            $realm_id      = $RealmInfo['id']; //服务器ID
            $flags         = 32; #服务器状态 0:正常 1:红色 2:离线 32:推荐 64:新服务器
            $population    = 0.5; #服务器负载 0.5:低  1.0:中 2.0:高
            $num_chars     = $num_player; #角色数量
            $RealmTimezone = 16; #时区 16:中国
            $type          = 0; # 0:正常

            $packet .= pack(
                'c2Z*Z*fc4',
                $type,
                $flags,
                $name,
                $address,
                $population,
                $num_chars,
                $RealmTimezone,
                0x2c, # unknown
                1
            );
        }

        $Srp6         = new Srp6();
        $size_bytes   = $Srp6->Littleendian($Srp6->BigInteger(strlen($packet), 10)->toHex())->toBytes();
        $realm_packet = $size_bytes . $packet;

        return $realm_packet;
    }

    public function getRealmInfo($realmlist = null, $num_player = 0)
    {
        $realm_packet = $this->get_realm_packet($realmlist, $num_player);

        $REALMLIST         = 16; //服务器清单
        $MIN_RESPONSE_SIZE = 7; //最小响应大小
        $num_realms        = count($realmlist); //服务器数量

        $header = pack('cvIv',
            $REALMLIST,
            $MIN_RESPONSE_SIZE + strlen($realm_packet),
            0x00,
            $num_realms
        );

        $footer = pack('c', 0);

        $response = $header . $realm_packet . $footer;

        return GetBytes($response);
    }

    // /**
    //  * [getRealmInfo 获取服务器列表]
    //  * ------------------------------------------------------------------------------
    //  * @author  by.fan <fan3750060@163.com>
    //  * ------------------------------------------------------------------------------
    //  * @version date:2019-04-27
    //  * ------------------------------------------------------------------------------
    //  * @return  [type]          [description]
    //  */
    // public function getRealmInfo($realmlist, $num_player = 0)
    // {
    //     // 模拟数据
    //     $name      = $realmlist['name']; //服务器名称
    //     $addr_port = $realmlist['address'] . ':' . $realmlist['port']; //服务器端口
    //     $realm_id  = $realmlist['id']; //服务器ID

    //     $type_b     = [0, 0, 0, 0];
    //     $population = 1;

    //     $time_zone = HexToDecimal('0x00');
    //     $unknown   = HexToDecimal('0x00');
    //     $cmd       = HexToDecimal('0x10');
    //     $name      = array_merge(GetBytes($name), [0]);
    //     $addr_port = array_merge(GetBytes($addr_port), [0]);

    //     // 拼装服内容信息 5
    //     $RealmInfo_Server = [];
    //     foreach ($type_b as $k => $v) {
    //         $RealmInfo_Server[] = $v;
    //     }

    //     //13
    //     foreach ($name as $k => $v) {
    //         $RealmInfo_Server[] = $v;
    //     }

    //     //20
    //     foreach ($addr_port as $k => $v) {
    //         $RealmInfo_Server[] = $v;
    //     }

    //     //4
    //     $RealmInfo_Server[] = $population;
    //     $RealmInfo_Server[] = 0;
    //     $RealmInfo_Server[] = $time_zone;
    //     $RealmInfo_Server[] = $unknown;

    //     //拼装服脚信息
    //     $RealmFooter_Server = [$num_player, 1, (int) $realm_id, 16, 1];

    //     //拼装服头信息
    //     $length               = 5 + count($RealmInfo_Server) + count($RealmFooter_Server);
    //     $length_b             = [$length, 0];
    //     $unk                  = [0, 0, 0, 0]; //4
    //     $num_realms           = 1; //服务器数量
    //     $RealmHeader_Server   = [];
    //     $RealmHeader_Server[] = $cmd;

    //     foreach ($length_b as $k => $v) {
    //         $RealmHeader_Server[] = $v;
    //     }

    //     foreach ($unk as $k => $v) {
    //         $RealmHeader_Server[] = $v;
    //     }

    //     $RealmHeader_Server[] = $num_realms;

    //     return array_merge($RealmHeader_Server, $RealmInfo_Server, $RealmFooter_Server);
    // }
}
