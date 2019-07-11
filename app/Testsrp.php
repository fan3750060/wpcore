<?php
namespace app;

use app\Common\int_helper;
use app\Common\Math_BigInteger;
use app\Common\Srp6;

class Testsrp
{
    public function run()
    {
        $username      = 'FAN3750060';
        $sha_pass_hash = '578b994cb24aa8521315b2e8dbd8fc3fd70ee9f2';
        $SRP           = new Srp6();
        $SRP->authSrp6($username, $sha_pass_hash);
        $srpdata = $SRP->data;

        $param = 'AYJ6YDagD42v/Yb+fVbBHOhVZ4vA5Sg84etk/I75I20Srcf9w/EQDzhHx7mDYaVUOQqQ3AlAWwfv8pb2LmvuGF/GaDtTTcawTAAA';
        $param = base64_decode($param);
        $param = int_helper::getBytes($param);

        $A  = array_slice($param, 1, 32);
        $M1 = array_slice($param, 33, 20);

        $A  = int_helper::toStr($A);
        $M1 = int_helper::toStr($M1);
        $A  = new Math_BigInteger($A, 256);
        $M1 = new Math_BigInteger($M1, 256);
        $A  = $A->toHex();
        $M1 = $M1->toHex();

        $v = $srpdata['v'];
        $s = $srpdata['s'];
        $b = $srpdata['b'];
        $B = $srpdata['B'];
        $B = int_helper::toStr($B);
        $B = new Math_BigInteger($B, 256);
        $B = $B->toHex();

        $SRP = new Srp6();
        $SRP->configvs($v, $s, $b, $B, $username);
        $check = $SRP->getM($A, $M1);
    }
}
