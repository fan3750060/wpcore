<?php
namespace app\World;

use app\Common\int_helper;
use app\Common\Srp6;
use app\World\OpCode;
use phpseclib\Crypt\RC4;

/**
 * 包
 */
class Worldpacket
{
    public static $ServerEncryptionKey = [0xCC, 0x98, 0xAE, 0x04, 0xE8, 0x97, 0xEA, 0xCA, 0x12, 0xDD, 0xC0, 0x93, 0x42, 0x91, 0x53, 0x57];
    public static $ServerDecryptionKey = [0xC2, 0xB3, 0x72, 0x3C, 0xC6, 0xAE, 0xD9, 0xB5, 0x34, 0x3C, 0x53, 0xEE, 0x2F, 0x43, 0x67, 0xCE];

    public static function getopcode($data, $fd)
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

        if($OpCode_name)
        {
        	WORLD_LOG('[' . $OpCode_name . '] Client : ' . $fd, 'warning');
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

    /********** 加密代码 **********/

    /**
     * [encrypter 包头加密]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-18
     * ------------------------------------------------------------------------------
     * @param   [type]          $OpCode     [description]
     * @param   [type]          $data       [description]
     * @param   [type]          $sessionkey [description]
     */
    public static function encrypter($OpCode, $data, $sessionkey)
    {
        // 包头
        $header = self::ServerPktHeader(int_helper::HexToDecimal($OpCode), count($data) + 2);
        $header = int_helper::toStr($header);

        //包头加密
        $seed         = self::AuthCrypt_s_seed($sessionkey);
        $encodeheader = self::encodeRC4($seed, $header);
        $encodeheader = int_helper::getBytes($encodeheader);
        return $encodeheader;
    }

    /**
     * [decrypter 包头解密]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-18
     * ------------------------------------------------------------------------------
     * @param   [type]          $data       [description]
     * @param   [type]          $sessionkey [description]
     * @return  [type]                      [description]
     */
    public static function decrypter($data, $sessionkey)
    {
        $seed         = self::AuthCrypt_c_seed($sessionkey);
        $decodeheader = self::decodeRC4($seed, int_helper::toStr(array_slice($data, 0, 6)));
        $decodeheader = int_helper::getBytes($decodeheader);

        $size   = unpack('H*', int_helper::toStr(array_slice($decodeheader, 0, 2)))[1];
        $opcode = strrev(unpack('h*', int_helper::toStr(array_slice($decodeheader, 2, 2)))[1]);
        return ['size' => int_helper::HexToDecimal($size), 'opcode' => $opcode];
    }

    public static function ServerPktHeader($cmd, $size)
    {
        $header = [];

        if ($size > 32767) {
            $header[] = (0x80 | (0xFF & ($size >> 16)));
        }
        $header[] = (0xFF & ($size >> 8));
        $header[] = (0xFF & $size);
        $header[] = (0xFF & $cmd);
        $header[] = (0xFF & ($cmd >> 8));

        return $header;
    }

    public static function getHeaderLength($size)
    {
        return 2 + ($size > 32767 ? 3 : 2);
    }

    public static function AuthCrypt_s_seed($sessionkey)
    {
        return hash_hmac('sha1', strrev($sessionkey), int_helper::toStr(self::$ServerEncryptionKey), true);
    }

    public static function AuthCrypt_c_seed($sessionkey)
    {
        return hash_hmac('sha1', strrev($sessionkey), int_helper::toStr(self::$ServerDecryptionKey), true);
    }

    public static function encodeRC4($k, $data)
    {
        $rc4 = new RC4();
        $rc4->setKey($k);
        return $rc4->encrypt($data);
    }

    public static function decodeRC4($k, $data)
    {
        $rc4 = new RC4();
        $rc4->setKey($k);
        return $rc4->decrypt($data);
    }
}
