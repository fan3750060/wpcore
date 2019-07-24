<?php
namespace app;

use app\Common\Srp6;
use app\World\OpCode;
use app\World\Worldpacket;

class Testsrp
{
    // const CMSG_PING = '0x1DC';
    public function run()
    {
        // $Srp6       = new Srp6();
        // $a = [139,149,171,204,12,0,0,0,0,0,0,0,0,0,2];
        // $a = ToStr($a);
        // $a = $Srp6->BigInteger($a, 256)->toHex();
        // var_dump($a);

        // $a = [225,173,196,83,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,2,1,0,0,0,0,0,0,0,0,0,0];
        // $a = ToStr($a);
        // $a = $Srp6->BigInteger($a, 256)->toHex();
        // var_dump($a);

        // $a = [8,242,250,189,87,74,0,0];
        // $a = ToStr($a);
        // $a = $Srp6->BigInteger($a, 256)->toHex();
        // var_dump($a);

        // $a = [54,185,208,168,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0];
        // $a = ToStr($a);
        // $a = $Srp6->BigInteger($a, 256)->toHex();
        // var_dump($a);

        // die;

        // die;

        $Srp6       = new Srp6();
        $sessionkey = '9E167999C030CFE4765C2C5D304F75CF10BAC063757404FFC1C4B4DB571D1035A2ED4A96E66651DA';
        $sessionkey = $Srp6->BigInteger($sessionkey, 16)->toBytes();

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
        // var_dump($packdata);die;

        WORLD_LOG('[SMSG_AUTH_RESPONSE] Client : ', 'warning');
        //加密
        $data     = [0x0c, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02];
        $packdata = Worldpacket::encrypter(OpCode::SMSG_AUTH_RESPONSE, $data, $sessionkey);
        $data     = $packdata     = array_merge($packdata, $data);
        // var_dump(json_encode($packdata));
        $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump($packdata);

        //加密
        $data = [0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00];
        $encodeheader = Worldpacket::encrypter(OpCode::SMSG_ADDON_INFO, $data, $sessionkey);
        $packdata     = array_merge($encodeheader, $data);
        // var_dump(json_encode($packdata));
        $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump($packdata);
        

        WORLD_LOG('[SMSG_CLIENTCACHE_VERSION] Client : ', 'warning');
        $data = [0x57, 0x4a, 0x00, 0x00];
        $encodeheader = Worldpacket::encrypter(OpCode::SMSG_CLIENTCACHE_VERSION, $data, $sessionkey);
        $packdata     = array_merge($encodeheader, $data);
        // var_dump(json_encode($packdata));
        $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump($packdata);

        WORLD_LOG('[SMSG_TUTORIAL_FLAGS] Client : ', 'warning');
        $data = [0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00];
        $encodeheader = Worldpacket::encrypter(OpCode::SMSG_TUTORIAL_FLAGS, $data, $sessionkey);
        $packdata     = array_merge($encodeheader, $data);
        /// var_dump(json_encode($packdata));
        $packdata     = $Srp6->BigInteger(ToStr($packdata), 256)->toHex();
        var_dump($packdata);

        // //解包
        // // $data = [0x88,0xe3,0x31,0x1a,0x23,0x9f];
        // $data = [0x63,0xaf,0xd0,0xdf,0x2f,0x3b];
        // $packdata = Worldpacket::decrypter($data, $sessionkey);
        // // unset($packdata['content']);
        // var_dump($packdata);
    }
}
