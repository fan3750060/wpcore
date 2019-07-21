<?php
namespace app;

use app\Common\int_helper;
use app\Common\Math_BigInteger;
use app\Common\Srp6;
use app\World\Worldpacket;
use app\World\OpCode;

class Testsrp
{
     // const CMSG_PING = '0x1DC';
    public function run()
    {
        $Srp6 = new Srp6();
        $sessionkey = '671D37F36AA281507F041491EAA0CBFB9A1BC022C252AD3EBA552CEC79DC3FFFD250246A18BDB713';
        $sessionkey = $Srp6->BigInteger($sessionkey, 16)->toBytes();

        //加密
        // $data = [0x0c,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00,0x00];
        // $packdata = Worldpacket::encrypter(OpCode::CMSG_PING,$data, $sessionkey);
        // $data = $packdata = array_merge($packdata,$data);

        // var_dump($data);
        // $packdata = $Srp6->BigInteger(int_helper::toStr($packdata), 256)->toHex();
        // var_dump($packdata);

        //解包
        // $data = [0x88,0xe3,0x31,0x1a,0x23,0x9f];
        $data = [0x1c,0x67,0x49,0x8b,0x00];
        $packdata = Worldpacket::decrypter($data, $sessionkey);
        // unset($packdata['content']);
        var_dump($packdata);
    }
}




