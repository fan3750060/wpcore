<?php
namespace app\World;

use app\World\Rc4;
use app\Common\Srp6;
use app\World\OpCode;

/**
 * 包
 */
class Worldpacket
{
    public static $ServerEncryptionKey = [0xCC, 0x98, 0xAE, 0x04, 0xE8, 0x97, 0xEA, 0xCA, 0x12, 0xDD, 0xC0, 0x93, 0x42, 0x91, 0x53, 0x57];
    public static $ServerDecryptionKey = [0xC2, 0xB3, 0x72, 0x3C, 0xC6, 0xAE, 0xD9, 0xB5, 0x34, 0x3C, 0x53, 0xEE, 0x2F, 0x43, 0x67, 0xCE];

    /**
     * [getopcode 获取操作码]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $data [description]
     * @param   [type]          $fd   [description]
     * @return  [type]                [description]
     */
    public static function getopcode($data, $fd)
    {
        $Srp6   = new Srp6();
        $OpCode = ToStr(array_slice($data, 2, 2));
        $OpCode = $Srp6->Littleendian($Srp6->BigInteger($OpCode, 256)->toHex())->toHex();

        //获取类的所有常量
        $objClass = new \ReflectionClass(new OpCode());
        $arrConst = $objClass->getConstants();

        $OpCode_name = '';
        foreach ($arrConst as $k => $v) {
            if (HexToDecimal($v) == HexToDecimal($OpCode)) {
                $OpCode_name = $k;
                continue;
            }
        }

        if ($OpCode_name) {
            WORLD_LOG('[' . $OpCode_name . '] Client : ' . $fd, 'warning');
        }

        return $OpCode_name;
    }

    /**
     * [Packtdata 普通打包]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $OpCode [description]
     * @param   [type]          $data   [description]
     */
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

    /**
     * [Unpackdata 普通解包]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $data [description]
     */
    public static function Unpackdata($data)
    {
        $packdata = [];

        $size_bytes = ToStr(array_slice($data, 0, 2));
        $OpCode     = ToStr(array_slice($data, 2, 4));
        $content    = ToStr(array_slice($data, 6));

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
    public static function encrypter($OpCode, $data, $sessionkey = null,$gono = false)
    {
        // 包头
        $header = self::ServerPktHeader(HexToDecimal($OpCode), count($data) + 2);

        if ($sessionkey) {
            // 加密
            $seed   = self::AuthCrypt_s_seed($sessionkey); //hash_hmac

            // $header = self::rc4_encode_decode($seed, ToStr($header)); //RC4(备用)
            
            $header = Rc4::getInstance($seed,$gono)->rc4_endecode(ToStr($header));

            $header = GetBytes($header);
        }

        return $header;
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
    public static function decrypter($data, $sessionkey = null,$gono = false)
    {
        if ($sessionkey) {
            $seed         = self::AuthCrypt_s_seed($sessionkey); //hash_hmac

            // $decodeheader = self::rc4_encode_decode($seed, ToStr(array_slice($data, 0, 4))); //RC4

            $decodeheader = Rc4::getInstance($seed,$gono)->rc4_endecode(ToStr(array_slice($data, 0, 6)));
            
            $decodeheader = GetBytes($decodeheader);
            $data         = array_merge($decodeheader, array_slice($data, 4));
        }

        $Srp6 = new Srp6();
        $size = $Srp6->BigInteger(ToStr(array_slice($data, 0, 2)), 256)->toString();

        $opcode = $Srp6->Littleendian($Srp6->BigInteger(ToStr(array_slice($data, 2, 2)), 256)->toHex())->toHex();

        $data = ['size' => $size, 'opcode' => $opcode, 'content' => array_slice($data, 6)];

        return $data;
    }

    /**
     * [ServerPktHeader 包头]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $cmd  [description]
     * @param   [type]          $size [description]
     */
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

    /**
     * [getHeaderLength 头长度]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $size [description]
     * @return  [type]                [description]
     */
    public static function getHeaderLength($size)
    {
        return 2 + ($size > 32767 ? 3 : 2);
    }

    /**
     * [rc4_encode_decode RC4加解密]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $seed  [密钥]
     * @param   [type]          $data [待加解密数据]
     * @return  [type]                [description]
     */
    public static function rc4_encode_decode($seed, $data)
    {
        $Ciphertext  = '';
        $key[]       = "";
        $s[]         = "";
        $seed_length = strlen($seed);
        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($seed[$i % $seed_length]);
            $s[$i]   = $i;
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j                   = ($j + $s[$i] + $key[$i]) % 256;
            list($s[$i], $s[$j]) = [$s[$j], $s[$i]];
        }

        // 丢弃前1024个字节，因为WoW使用ARC4-drop1024。
        for ($i = $j = $c = 0; $c < 1024; $c++) {
            $i                   = ($i + 1) % 256;
            $j                   = ($j + $s[$i]) % 256;
            list($s[$i], $s[$j]) = [$s[$j], $s[$i]];
            $r                   = $s[($s[$i] + $s[$j]) % 256];
        }

        for ($c = 0; $c < $data_length; $c++) {
            $i                   = ($i + 1) % 256;
            $j                   = ($j + $s[$i]) % 256;
            list($s[$i], $s[$j]) = [$s[$j], $s[$i]];
            $r                   = $s[($s[$i] + $s[$j]) % 256];
            $Ciphertext .= chr($r ^ ord($data[$c]));
        }

        return $Ciphertext;
    }

    /**
     * [AuthCrypt_s_seed hash_hmac]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $sessionkey [description]
     */
    public static function AuthCrypt_s_seed($sessionkey)
    {
        return hash_hmac('sha1', strrev($sessionkey), ToStr(self::$ServerEncryptionKey), true);
    }

    /**
     * [AuthCrypt_s_seed hash_hmac]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-20
     * ------------------------------------------------------------------------------
     * @param   [type]          $sessionkey [description]
     */
    public static function AuthCrypt_c_seed($sessionkey)
    {
        return hash_hmac('sha1', strrev($sessionkey), ToStr(self::$ServerDecryptionKey), true);
    }
}
