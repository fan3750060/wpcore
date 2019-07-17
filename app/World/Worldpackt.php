<?php
namespace app\World;

use app\Common\int_helper;
use app\Common\Srp6;
use app\World\OpCode;

/**
 * 包
 */
class Worldpackt
{
    public static function getopcode($data)
    {
        $Srp6   = new Srp6();
        $OpCode = int_helper::toStr(array_slice($data, 2, 2));
        $OpCode = $Srp6->Littleendian($Srp6->BigInteger($OpCode, 256)->toHex())->toHex();

        //获取类的所有常量
        $objClass = new \ReflectionClass(new OpCode());
        $arrConst = $objClass->getConstants();

        $OpCode_name = '';
        foreach ($arrConst as $k => $v) {
            if (int_helper::HexToDecimal($v) == int_helper::HexToDecimal($OpCode)) {
                $OpCode_name = $k;
                continue;
            }
        }

        return $OpCode_name;
    }

    public static function Packtdata($OpCode, $data)
    {
        $Srp6       = new Srp6();
        $OpCode     = $Srp6->Littleendian($Srp6->BigInteger($OpCode, 16)->toHex())->toBytes();
        $Packet     = $OpCode . $data;
        $size_bytes = strlen($Packet);
        $size_bytes = $Srp6->BigInteger($size_bytes, 10)->toHex();
        $Packet     = $Srp6->BigInteger($Packet, 256)->toHex();
        $Packet     = $size_bytes . $Packet;
        return $Packet;
    }

    public static function Unpackdata($data)
    {
        $packdata = [];

        $size_bytes = int_helper::toStr(array_slice($data, 0, 2));
        $OpCode     = int_helper::toStr(array_slice($data, 2, 4));
        $content    = int_helper::toStr(array_slice($data, 6));

        $Srp6       = new Srp6();
        $packdata[] = $Srp6->BigInteger($size_bytes, 256)->toString();
        $packdata[] = $Srp6->BigInteger($OpCode, 256)->toHex();
        $packdata[] = $Srp6->BigInteger($content, 256)->toBytes();

        return $packdata;
    }
}
