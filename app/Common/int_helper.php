<?php
namespace app\Common;

class int_helper
{
    public static function int8($i)
    {
        return is_int($i) ? pack("c", $i) : unpack("c", $i)[1];
    }

    public static function uInt8($i)
    {
        return is_int($i) ? pack("C", $i) : unpack("C", $i)[1];
    }

    public static function int16($i)
    {
        return is_int($i) ? pack("s", $i) : unpack("s", $i)[1];
    }

    public static function uInt16($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {
            // big-endian
            $i = $f("n", $i);
        } else if ($endianness === false) {
            // little-endian
            $i = $f("v", $i);
        } else if ($endianness === null) {
            // machine byte order
            $i = $f("S", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

    public static function int32($i)
    {
        return is_int($i) ? pack("l", $i) : unpack("l", $i)[1];
    }

    public static function uInt32($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {
            // big-endian
            $i = $f("N", $i);
        } else if ($endianness === false) {
            // little-endian
            $i = $f("V", $i);
        } else if ($endianness === null) {
            // machine byte order
            $i = $f("L", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

    public static function int64($i)
    {
        return is_int($i) ? pack("q", $i) : unpack("q", $i)[1];
    }

    public static function uInt64($i, $endianness = false)
    {
        $f = is_int($i) ? "pack" : "unpack";

        if ($endianness === true) {
            // big-endian
            $i = $f("J", $i);
        } else if ($endianness === false) {
            // little-endian
            $i = $f("P", $i);
        } else if ($endianness === null) {
            // machine byte order
            $i = $f("Q", $i);
        }

        return is_array($i) ? $i[1] : $i;
    }

    public static function PackInt($int,$type=8)
    {
        switch ($type) {
            case 8:
                return self::getBytes(self::int8($int));
                break;

            case 16:
                return self::getBytes(self::int16($int));
                break;

            case 32:
                return self::getBytes(self::int32($int));
                break;

            case 64:
                return self::getBytes(self::int64($int));
                break;
        }
    }

    public static function UnPackInt($int,$type=8)
    {
        switch ($type) {
            case 8:
                return self::uInt8($int);
                break;

            case 16:
                return self::uInt16($int);
                break;

            case 32:
                return self::uInt32($int);
                break;

            case 64:
                return self::uInt64($int);
                break;
        }
    }

    //16进制转10进制
    public static function HexToDecimal($Hex)
    {
        return (int) base_convert($Hex, 16, 10);
    }

    // 转成byte数组
    public static function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            //遍历每一个字符 用ord函数把它们拼接成一个php数组
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

    // 转成数据包字符串
    public static function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch); //这里用chr函数
        }
        return $str;
    }
}
