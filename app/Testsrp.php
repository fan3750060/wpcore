<?php
namespace app;

use app\Common\Srp6;
use app\World\Worldpacket;
use app\World\OpCode;
use app\World\Packetmanager;
use core\lib\Cache;

class Testsrp
{
    public function run()
    {
        $Srp6       = new Srp6();
        $sessionkey = 'F5AFC49E1798090EAD1BB1BAE68B8BFDD38BA36B8DB0803B2DEE094FC3D235501D4FB99289D7586D';
        $sessionkey = $Srp6->BigInteger($sessionkey, 16)->toBytes();

        $fd = 'test001';
        $list = ['serverseed','sessionkey','Worldpacket_encrypter','Worldpacket_decrypter'];
        foreach ($list as $k => $v) 
        {
            Cache::drive('redis')->delete($fd.$v);
        }

        // 角色进入游戏
        // $mapid         = 1;
        // $x = -618.0;
        // $y = -4251.0;
        // $z = 38.774200439453125;
        // $orientation = 0.0;
        // $data = pack('Iffff',$mapid,$x,$y,$z,$orientation);
        // $data = GetBytes($data);
        // $encodeheader = Worldpacket::encrypter(OpCode::SMSG_LOGIN_VERIFY_WORLD, $data, $sessionkey);
        // $packdata     = array_merge($encodeheader, $data);
        // var_dump('Encode:'.$packdata);die;
        //

        /************** 加密包 ******************/
        $data = [0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00];
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_ADDON_INFO,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_AUTH_RESPONSE] Client : ', 'warning');
        $data     = [0x0c, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x01];
        $packdata = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_AUTH_RESPONSE,$data,$sessionkey]);
        $data     = $packdata     = array_merge($packdata, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[CMSG_CHAR_ENUM] Client : ', 'warning');
        $data = '027A03000000000000E69FA5E5A89C00050400040900080E0155000000000000005C2FD1448FD2CF44CD8C0A430000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000001527000004000000000000000000000000000000000000000000001627000007000000001827000008000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000002A1900000D000000000000000000000000000000000000000000000000000000000000000000000000000000008B030000000000006C6B6C6B000102010705080700010C00000000000000A45B13C6290DB04269C06342000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000C10C00000400000000000000000000000000000000000000000000D12600000700000000D22600000800000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000F22100001100000000000000000000000000000000000000000000000000000000000000000000000000000000';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);

        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_CHAR_ENUM,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_MOTD] Client : ' . $fd, 'warning');
        $data = '0100000057656C636F6D6520746F20746865206964772D636F72652073657276657200';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_MOTD,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_TUTORIAL_FLAGS] Client : ' . $fd, 'warning');
        $data = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_TUTORIAL_FLAGS,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_LOGIN_VERIFY_WORLD] Client : ' . $fd, 'warning');
        $data = '000000005C2FD1448FD2CF44CD8C0A435131B740';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_LOGIN_VERIFY_WORLD,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_ACCOUNT_DATA_TIMES] Client : ' . $fd, 'warning');
        $data = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_ACCOUNT_DATA_TIMES,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_INITIAL_SPELLS] Client : ' . $fd, 'warning');
        $data = '000C009D0200004945000061500000635000006B140000401E00009D0200004945000061500000635000006B140000401E00000C000000';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_INITIAL_SPELLS,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_UPDATE_OBJECT] Client : ' . $fd, 'warning');
        $data = '010000000003037A03047100000000003216795D5C2FD1448FD2CF44CD8C0A435131B74000000000000020400000E04000009040711C9740000020400000E04000009040E00F49400000000031170040141D40000000000000000000000000C00300F80004000001000090010000000000000000000000000400040004000400040004000400040004000400040004000400040004000400040000000000FCFFFFFFFF0000000000000000000000000000000000000000000000000000000000C0BD000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000003001800000000000000000000000000000020001040000000800000000000407A03000000000000190000000000803F0C000000000000000C0000000000000001000000050000000504000300000000022BC73E0000C03F39000000390000000F0000000F0000000C0000000700000002000000000000000C00000000000000040900080E0000020000000000000000000000003908000000000000000000007800000079000000000000000000000000000000000000000000000000000000000000002C080000000000000000000000000000000000000000000000000000000000007C03003908000040000000000000000000000000000000007D030078000000407E0300790000004000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000007B03002C080000400000000000000000000000000000000000000000000000000000000000000000A10200002C010000DC0000002C010000010000000100000000000000000000000000000000000000102700000100000000000000FFFFFFFF46000000';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode:: SMSG_UPDATE_OBJECT,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_LOGIN_SETTIMESPEED] Client : ' . $fd, 'warning');
        $data = '53F2EF508A88883C';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode:: SMSG_LOGIN_SETTIMESPEED,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        WORLD_LOG('[SMSG_TIME_SYNC_REQ] Client : ' . $fd, 'warning');
        $data = '00000000';
        $data = $Srp6->BigInteger($data, 16)->toBytes();
        $data = GetBytes($data);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode:: SMSG_TIME_SYNC_REQ,$data,$sessionkey]);
        $packdata     = array_merge($encodeheader, $data);
        $packdata = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump('Encode:'.$packdata);

        // /************** 解密包 ******************/
        // $data = '76EFB19E223C5936114FCB46FFFFFFFF';
        // $data = $Srp6->BigInteger($data, 16)->toBytes();
        // $data = GetBytes($data);
        // $packdata = Packetmanager::Worldpacket_decrypter($fd,[$data,$sessionkey]);
        // $packdata = json_encode($packdata);
        // var_dump('Decode:'.$packdata);

        // $data = '50CA447F80366B6C7571697100030101020601010000';
        // $data = $Srp6->BigInteger($data, 16)->toBytes();
        // $data = GetBytes($data);

        // for ($i=0; $i < 20; $i++) { 
        //     $packdata = Packetmanager::Worldpacket_decrypter($fd,[$data,$sessionkey]);
        //     $opcode = Worldpacket::getopcode($packdata['opcode'], $fd);
        //     if($opcode){
        //         break;
        //     }
        // }

        // // $packdata = Packetmanager::Worldpacket_decrypter($fd,[$data,$sessionkey]);
        // $packdata = json_encode($packdata);
        // var_dump('Decode:'.$packdata);

        // $data = '252E6FECE1CE5264F5CB225FFFFFFFFF';
        // $data = $Srp6->BigInteger($data, 16)->toBytes();
        // $data = GetBytes($data);
        // // $packdata = Packetmanager::Worldpacket_decrypter($fd,[$data,$sessionkey]);

        // for ($i=0; $i < 20; $i++) { 
        //     $packdata = Packetmanager::Worldpacket_decrypter($fd,[$data,$sessionkey]);
        //     $opcode = Worldpacket::getopcode($packdata['opcode'], $fd);
        //     if($opcode){
        //         break;
        //     }
        // }

        // $packdata = json_encode($packdata);
        // var_dump('Decode:'.$packdata);
    }
}
