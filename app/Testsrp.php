<?php
namespace app;

use app\Common\Srp6;
use app\World\Login\PlayerLogin;
use app\World\Movement\MovementHandler;
use app\World\Packet\Packetmanager;
use core\query\DB;

class Testsrp
{
    public static $equipment = [
        'HEAD'      => 0,
        'NECK'      => 1,
        'SHOULDERS' => 2,
        'BODY'      => 3,
        'CHEST'     => 4,
        'WAIST'     => 5,
        'LEGS'      => 6,
        'FEET'      => 7,
        'WRISTS'    => 8,
        'HANDS'     => 9,
        'FINGER1'   => 10,
        'FINGER2'   => 11,
        'TRINKET1'  => 12,
        'TRINKET2'  => 13,
        'BACK'      => 14,
        'MAINHAND'  => 15,
        'OFFHAND'   => 16,
        'RANGED'    => 17,
        'TABARD'    => 18,
        'BAG1'      => 19,
    ];

    public function player()
    {
        $a = (new PlayerLogin)->LoginObject(1, 2, 3);

        var_dump(strtohex($a));die;
    }

    public function GetPackGuid($guid = null)
    {
        $pack_guid = array_merge(packInt(0, 64), [0]);
        $size      = 1;
        $index     = 0;

        while ($guid) {

            if (($guid & 0xff) > 0) {
                $pack_guid[0] |= (1 << $index);
                // var_dump($pack_guid);
                $pack_guid[$size] = $guid & 0xff;
                $size += 1;
            }

            $index += 1;
            $guid >>= 8;
        }

        $pack_guid = ToStr(array_slice($pack_guid, 0, $size));

        return $pack_guid;
    }

    public function run()
    {
        $guid = 73;
        $pack_guid = $this->GetPackGuid($guid);
        $guid = pack('Q', $guid);
        $mask = '';
        if (true) {
            $guid = $pack_guid;
        } else {
            $mask = pack('c', 0xff);
        }
        $header = pack('c', 3);
        $header .= $mask . $guid;
        var_dump(strtohex($header));

        $guid = 74;
        $pack_guid = $this->GetPackGuid($guid);
        $guid = pack('Q', $guid);
        $mask = '';
        if (true) {
            $guid = $pack_guid;
        } else {
            $mask = pack('c', 0xff);
        }
        $header = pack('c', 3);
        $header .= $mask . $guid;
        var_dump(strtohex($header));


        // $Srp6 = new Srp6();

        // $bytes_0 = (1 | 2 << 8 | 3 << 16 | 4 << 24);
        // var_dump($bytes_0);die;

        // WORLD_LOG('[SMSG_LOGIN_SETTIMESPEED] Client : ' . $fd, 'warning');
        // $data         = 'bbc0d9940be40000000000ca14ce2b338bd14466aed1440a57f342d66c344000000000';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_decrypter(2, [$data, 1]);
        // MovementHandler::MSG_MOVE_SET_FACING(1,2,$encodeheader['content']);

        // $name          = 'wpcore'; #服务器名称
        // $address       = '127.0.0.1:8085'; #服务器ip
        // $flags         = 0; #服务器状态
        // $population    = 0.5; #服务器拥挤程度
        // $num_chars     = 1; #角色数量
        // $RealmTimezone = 16; #时区
        // $type          = 0;

        // $num_chars = 1;

        // $packet = pack(
        //     'c2Z*Z*fc4',
        //     $type,
        //     $flags,
        //     $name,
        //     $address,
        //     $population,
        //     $num_chars,
        //     $RealmTimezone,
        //     0x2c, # unknown
        //     0x0010# ?
        // );

        // $Srp6         = new Srp6();
        // $size_bytes   = $Srp6->Littleendian($Srp6->BigInteger(strlen($packet), 10)->toHex())->toBytes();
        // $realm_packet = $size_bytes . $packet;

        // $REALMLIST         = 10;
        // $MIN_RESPONSE_SIZE = 7;
        // $num_realms        = 1;

        // $header = pack('cvIv',
        //     $REALMLIST,
        //     $MIN_RESPONSE_SIZE + strlen($realm_packet),
        //     0x00,
        //     $num_realms
        // );

        // $footer   = pack('c', 0);
        // $response = $header . $realm_packet . $footer;

        // $Srp6     = new Srp6();
        // $response = $Srp6->BigInteger($response, 256)->toHex();
        // var_dump($response);die;

        // $update_flags = (32 | 64 | 16 | 1);
        // var_dump($update_flags);die;

        // $Srp6       = new Srp6();
        // $sessionkey = 'F5AFC49E1798090EAD1BB1BAE68B8BFDD38BA36B8DB0803B2DEE094FC3D235501D4FB99289D7586D';
        // $sessionkey = $Srp6->BigInteger($sessionkey, 16)->toBytes();

        // $fd = 'test001';

        // // 角色进入游戏
        // $mapid       = 1;
        // $x           = -618.0;
        // $y           = -4251.0;
        // $z           = 38.774200439453125;
        // $orientation = 0.0;
        // // $packdata = pack('Iffff',$mapid,$x,$y,$z,$orientation);
        // $packdata = pack('If4', $mapid, $x, $y, $z, $orientation);

        // $packdata     = GetBytes($packdata);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_LOGIN_VERIFY_WORLD, $packdata, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $packdata);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // /************** 加密包 ******************/
        // $data = '';
        // for ($i = 0; $i < 16; $i++) {
        //     $data .= pack('I2', 258, 0);
        // }
        // $data = GetBytes($data);

        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_ADDON_INFO, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_AUTH_RESPONSE] Client : ', 'warning');
        // $data     = [0x0c, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01];
        // $packdata = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_AUTH_RESPONSE, $data, $sessionkey]);
        // $data     = $packdata     = array_merge($packdata, $data);
        // $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[CMSG_CHAR_ENUM] Client : ', 'warning');
        // $data = '027A03000000000000E69FA5E5A89C00050400040900080E0155000000000000005C2FD1448FD2CF44CD8C0A430000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001527000004000000000000000000000000000000000000000000001627000007000000001827000008000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002A1900000D000000000000000000000000000000000000000000000000000000000000000000000000000000008B030000000000006C6B6C6B000102010705080700010C00000000000000A45B13C6290DB04269C06342000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000C10C00000400000000000000000000000000000000000000000000D12600000700000000D22600000800000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000F22100001100000000000000000000000000000000000000000000000000000000000000000000000000000000';
        // $data = $Srp6->BigInteger($data, 16)->toBytes();
        // $data = GetBytes($data);

        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_ENUM, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_MOTD] Client : ' . $fd, 'warning');
        // $data         = '0100000057656C636F6D6520746F20746865206964772D636F72652073657276657200';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_MOTD, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_TUTORIAL_FLAGS] Client : ' . $fd, 'warning');
        // $data         = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_TUTORIAL_FLAGS, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_LOGIN_VERIFY_WORLD] Client : ' . $fd, 'warning');
        // $data         = '000000005C2FD1448FD2CF44CD8C0A435131B740';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_LOGIN_VERIFY_WORLD, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_ACCOUNT_DATA_TIMES] Client : ' . $fd, 'warning');
        // $data         = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_ACCOUNT_DATA_TIMES, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_INITIAL_SPELLS] Client : ' . $fd, 'warning');
        // $data         = '000C009D0200004945000061500000635000006B140000401E00009D0200004945000061500000635000006B140000401E00000C000000';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_INITIAL_SPELLS, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_UPDATE_OBJECT] Client : ' . $fd, 'warning');
        // $data         = '010000000003037A03047100000000003216795D5C2FD1448FD2CF44CD8C0A435131B74000000000000020400000E04000009040711C9740000020400000E04000009040E00F49400000000031170040141D40000000000000000000000000C00300F80004000001000090010000000000000000000000000400040004000400040004000400040004000400040004000400040004000400040000000000FCFFFFFFFF0000000000000000000000000000000000000000000000000000000000C0BD000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000003001800000000000000000000000000000020001040000000800000000000407A03000000000000190000000000803F0C000000000000000C0000000000000001000000050000000504000300000000022BC73E0000C03F39000000390000000F0000000F0000000C0000000700000002000000000000000C00000000000000040900080E0000020000000000000000000000003908000000000000000000007800000079000000000000000000000000000000000000000000000000000000000000002C080000000000000000000000000000000000000000000000000000000000007C03003908000040000000000000000000000000000000007D030078000000407E0300790000004000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000007B03002C080000400000000000000000000000000000000000000000000000000000000000000000A10200002C010000DC0000002C010000010000000100000000000000000000000000000000000000102700000100000000000000FFFFFFFF46000000';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_UPDATE_OBJECT, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_LOGIN_SETTIMESPEED] Client : ' . $fd, 'warning');
        // $data         = '53F2EF508A88883C';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_LOGIN_SETTIMESPEED, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);

        // WORLD_LOG('[SMSG_TIME_SYNC_REQ] Client : ' . $fd, 'warning');
        // $data         = '00000000';
        // $data         = $Srp6->BigInteger($data, 16)->toBytes();
        // $data         = GetBytes($data);
        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_TIME_SYNC_REQ, $data, $sessionkey]);
        // $packdata     = array_merge($encodeheader, $data);
        // $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        // var_dump('Encode:' . $packdata);
    }

    public function sql()
    {
        $sql    = 'SELECT RaceID as race,ClassID as class,Gender,Items_1,Items_2,Items_3,Items_4,Items_5,Items_6,Items_7,Items_8,Items_9,Items_10,Items_11 as gender from db_CharStartOutfit_8606';
        $result = DB::table('db_CharStartOutfit_8606', 'characters')->select();

        $newdata = [];
        foreach ($result as $k => $v) {
            if ($v['Gender'] == 0) {
                for ($i = 1; $i <= 11; $i++) {
                    if (isset($v['Items_' . $i]) && $v['Items_' . $i] > 0) {
                        $data = [
                            'race'   => $v['RaceID'],
                            'class'  => $v['ClassID'],
                            'itemid' => $v['Items_' . $i],
                        ];

                        $newdata[] = $data;
                    }
                }
            }
        }

        DB::table('playercreateinfo_item', 'world')->insert($newdata);
    }
}
