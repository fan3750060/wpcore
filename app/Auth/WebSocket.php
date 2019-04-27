<?php
namespace app\Auth;

class WebSocket
{
    /**
     * 服务端根据Sec-WebSocket-Key生成握手数据,将给数据发回客户端完成连接
     * @param string $requestHeaders
     * http协议报头信息
     * @return string
     * 返回握手http协议报头
     */
    public function getHandShakeHeaders($requestHeaders)
    {
        //提取http请求header中的Sec-WebSocket-Key
        $key = $this->getSecWebSocketKey($requestHeaders);
        //将key加上特殊串进行sha1和base64加密作为accept key
        $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));

        //组装报头信息，必须以两个回车结尾
        $upgrade = "HTTP/1.1 101 Switching Protocol\r\n" .
            "Upgrade: websocket\r\n" .
            "Sec-WebSocket-Version: 13\r\n" .
            "Connection: Upgrade\r\n" .
            "Sec-WebSocket-Accept: " . $acceptKey . "\r\n\r\n";

        return $upgrade;
    }

    /**
     * 提取http请求header中的Sec-WebSocket-Key
     * @param string $requestHeaders
     * http协议报头信息
     * @return string
     * 返回 Sec-WebSocket-Key
     */
    private function getSecWebSocketKey($requestHeaders)
    {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $requestHeaders, $match)) {
            return $match[1];
        }

        return "";
    }

    /**
     * 按websocket协议打包发送给客户端的数据
     * @param string $message
     * 要发送的文本内容
     * @param real $opcode
     * 数据包类型，默认为文本类型
     * 0x0：表示附加数据包
     * 0x1：表示文本类型数据包
     * 0x2：表示二进制类型数据包
     * 0x3-7：保留
     * 0x8：表示断开连接类型数据包
     * 0x9：表示ping类型数据包
     * 0xA：表示pong类型数据包
     * 0xB-F：保留
     * @return string
     * 返回打包后的数据
     */
    public function wrap($message = "", $opcode = 0x1)
    {
        $fin = 0x80;
        //第一个字节8位为10000001即0x81，其中0x80为100000000，0x1为00000001
        $firstByte  = $fin | $opcode;
        $dataLength = strlen($message);

        $payloadLengthExtended = "";
        $payloadLength         = 0;
        if (0 <= $dataLength && $dataLength <= 125) {
            //如果数据长度为0-125，则payload长度即为数据长度，存在第二个字节
            $payloadLength = $dataLength;
        } else if ($dataLength >= 126 && $dataLength <= 65535) {
            //如果数据的长度为126-65535（0xFFFF），第二个字节默认存储的长度为126（0x7E），再接两个字节16位来表示长度
            $payloadLength = 126;
            //通过pack函数转为2字节16位的二进制字符串
            $payloadLengthExtended = pack('n', $dataLength);
        } else {
            //如果数据的长度大于65535（0xFFFF），第二个字节默认存储的长度为127（0x7F），再接8个字节64位来表示长度
            $payloadLength = 127;
            //通过pack函数转为8字节64位的二进制字符串,4个空字节（x）32位和一个32位整形（N）
            $payloadLengthExtended = pack("xxxxN", $dataLength);
        }

        //服务端向客户端不需要做掩码处理，也就是第二字节第一位为0,由于小于等于127转为8位，第一位就为0，所以不需要额外处理
        $encodeData = chr($firstByte) . chr($payloadLength) . $payloadLengthExtended . $message;
        //$encodeData = pack('n', ($firstByte << 8) | $payloadLength) . $payloadLengthExtended . $message;

        return $encodeData;
    }

    /**
     * 解包客户端发过来的数据
     * @param string $message
     * 消息
     * @return  boolean|string
     * 解包后的消息，数据不合法则返回false
     */
    public function unwrap($message = "")
    {
        //取第一字节低4位即为opcode
        $opcode = ord(substr($message, 0, 1)) & 0x0F;
        //取第二字节低7位则为payload长度（第一位为mask）
        $payloadLength = ord(substr($message, 1, 1)) & 0x7F;
        //取第二字节高一位及为mask值（0或1，是否进行掩码处理）
        $isMask = (ord(substr($message, 1, 1)) & 0x80) >> 7;

        $maskKey    = null;
        $data       = null;
        $decodeData = null;

        //数据不合法（$isMask不为1则表示没有进行掩码处理，0x8表示连接断开）
        if ($isMask != 1 || $opcode == 0x8) {
            return false;
        }

        //获取掩码密钥和原始数据
        if ($payloadLength >= 0 && $payloadLength <= 125) {
            //如果payload长度为0-125，第二字节为payload长度，3-6的4个字节为mask key，剩余为数据
            $maskKey = substr($message, 2, 4);
            $data    = substr($message, 6);
        } else if ($payloadLength == 126) {
            //如果payload长度为126，第二字节为payload长度，3-4的两个字节为数据长度，5-8的4个字节为mask key，剩余为数据
            $maskKey = substr($message, 4, 4);
            $data    = substr($message, 8);
        } else if ($payloadLength == 127) {
            //如果payload长度为127，第二字节为payload长度，3-10的8个字节为数据长度，11-14的4个字节为mask key，剩余为数据
            $maskKey = substr($message, 10, 4);
            $data    = substr($message, 14);
        }
        //进行掩码处理
        $length = strlen($data);
        for ($i = 0; $i < $length; $i++) {
            $decodeData .= $data[$i] ^ $maskKey[$i % 4];
        }
        return $decodeData;
    }

}
