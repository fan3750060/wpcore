<?php
namespace app\World;

use app\Common\Srp6;
use app\World\OpCode;
use core\lib\Cache;

/**
 * 包
 */
class Worldpacket
{
    public static $ENCRYPT_HEADER_SIZE = 4;
    public static $DECRYPT_HEADER_SIZE = 6;

    public static $send_i = 0;
    public static $send_j = 0;
    public static $recv_i = 0;
    public static $recv_j = 0;

    public static $ServerEncryPtionKey = [0x38, 0xA7, 0x83, 0x15, 0xF8, 0x92, 0x25, 0x30, 0x71, 0x98, 0x67, 0xB1, 0x8C, 0x04, 0xE2, 0xAA];
    public static $ServerDecryPtionKey = [0x38, 0xA7, 0x83, 0x15, 0xF8, 0x92, 0x25, 0x30, 0x71, 0x98, 0x67, 0xB1, 0x8C, 0x04, 0xE2, 0xAA];

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
    public static function getopcode($OpCode, $fd)
    {
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
        $Srp6   = new Srp6();
        $OpCode = ToStr(array_slice($data, 2, 2));
        $OpCode = $Srp6->Littleendian($Srp6->BigInteger($OpCode, 256)->toHex())->toHex();

        return $OpCode;
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
    public static function encrypter($OpCode, $data, $sessionkey = null, $gono = true)
    {
        // 包头
        $header = self::ServerPktHeader(HexToDecimal($OpCode), count($data) + 2);
        $data   = array_merge($header, $data);

        if ($sessionkey) {
            // 加密
            $crypt_key = self::AuthCrypt_s_seed($sessionkey); //hash_hmac
            $crypt_key = GetBytes($crypt_key);

            $encrypted_header = PackInt(0, self::$ENCRYPT_HEADER_SIZE * 8);
            $crypt_key_length = count($crypt_key);

            if(!$gono)
            {
                self::$send_i = 0;
                self::$send_j = 0;
            }

            foreach (range(0, self::$ENCRYPT_HEADER_SIZE - 1) as $k => $v) {
                self::$send_i %= $crypt_key_length;
                $enc = ($data[$k] ^ $crypt_key[self::$send_i]) + self::$send_j;
                // $enc %= 0x100;
                self::$send_i += 1;
                $encrypted_header[$k] = self::$send_j = $enc;
            }

            $header = $encrypted_header;
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
    public static function decrypter($data, $sessionkey = null, $gono = true)
    {
        $header = array_slice($data, 0, 6);

        if ($sessionkey) {

            // 加密
            $crypt_key = self::AuthCrypt_c_seed($sessionkey); //hash_hmac
            $crypt_key = GetBytes($crypt_key);
            $decrypted_header = PackInt(0, self::$DECRYPT_HEADER_SIZE * 8);
            $crypt_key_length = count($crypt_key);

            if(!$gono)
            {
                self::$recv_i = 0;
                self::$recv_j = 0;
            }

            foreach (range(0, self::$DECRYPT_HEADER_SIZE - 1) as $k => $v) {
                self::$recv_i %= $crypt_key_length;
                $dec = ($data[$k] - self::$recv_j) ^ $crypt_key[self::$recv_i];
                // $dec %= 0x100;
                self::$recv_i += 1;
                self::$recv_j         = $data[$k];
                $decrypted_header[$k] = $dec;
            }

            $header = $decrypted_header;
        }

        $data = array_merge($header, array_slice($data, 6));

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
        return hash_hmac('sha1', $sessionkey, ToStr(self::$ServerEncryPtionKey), true);
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
        return hash_hmac('sha1', $sessionkey, ToStr(self::$ServerDecryPtionKey), true);
    }
}
