<?php
namespace app\common;

/**
 * 类名：StructPHP
 * 作者：mqycn
 * 博客：http://www.miaoqiyuan.cn
 * 源码：http://www.miaoqiyuan.cn/p/php-struct
 * 说明：PHP实现Struct，基于 pack/unpack
 *    官方文档： http://php.net/manual/zh/function.pack.php
 *    数据类型：
 *      a - NUL 填充的字符串
 *      A - SPACE 填充的字符串
 *      h - 十六进制字符串，低位在前
 *      H - 十六进制字符串，高位在前
 *      c - signed char
 *      C - unsigned char
 *      s - signed short（总是16位, machine 字节顺序）
 *      S - unsigned short（总是16位, machine 字节顺序）
 *      n - unsigned short（总是16位, big endian 字节顺序）
 *      v - unsigned short（总是16位, little endian 字节顺序）
 *      i - signed integer（取决于machine的大小和字节顺序）
 *      I - unsigned integer（取决于machine的大小和字节顺序）
 *      l - signed long（总是32位, machine 字节顺序）
 *      L - unsigned long（总是32位, machine 字节顺序）
 *      N - unsigned long（总是32位, big endian 字节顺序）
 *      V - unsigned long（总是32位, little endian 字节顺序）
 *      f - float（取决于 machine 的大小和表示）
 *      d - double（取决于 machine 的大小和表示）
 *      x - NUL 字节
 *      X - 备份一个字节
 *      Z - NUL 填充的字符串
 *      @ - NUL 填充绝对位置
 */

class StructPHP
{

    public static function decode($struct = array(), $bin = '')
    {
        $format = '';
        foreach ($struct as $key => $val) {
            $format .= '/' . $val . (is_numeric($key) ? '' : $key);
        }
        $format = substr($format, 1);
        if (strlen($bin) == 0) {
            throw new Exception('传入的数据长度为0');
        }
        return unpack($format, $bin);
    }

    public static function encode_hex($hex)
    {
        $hex = str_replace(' ', '', $hex);
        return self::encode(array('H' . strlen($hex)), array($hex));
    }

    public static function encode($struct = array(), $data = array())
    {
        if (!is_array($struct) || !is_array($data) || count($struct) == 0 || count($struct) != count($data)) {
            throw new Exception('结构体与数据长度不对应');
        }
        $bin = '';
        foreach ($struct as $key => $val) {
            $bin .= pack($val, $data[$key]);
        }
        return $bin;
    }
}
