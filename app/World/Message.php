<?php
namespace app\World;

use app\Common\int_helper;
use app\Common\WebSocket;
use app\World\Clientstate;
use app\World\Connection;

// use app\World\OpCode;
class Message
{
    /**
     * [newConnect 首次连接要求验证]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-28
     * ------------------------------------------------------------------------------
     * @param   [type]          $serv [description]
     * @param   [type]          $fd   [description]
     * @return  [type]                [description]
     */
    public function newConnect($serv, $fd)
    {
        // $data   = [];
        // // $m_Seed = int_helper::getBytes(int_helper::HexToDecimal('\x00\x06\xec\x012u\xfc\x87'));
        // $m_Seed = int_helper::getBytes('\x00\x06\xec\x012u\xfc\x87');
        // // $data   = array_merge($data, int_helper::getBytes(int_helper::uInt32(1)));
        // $data   = array_merge($data, $m_Seed);
        // echolog(json_encode($data));

        $data = [];
        $data = array_merge($data,int_helper::uInt8(12));
        $data = array_merge($data,int_helper::uInt32(0));
        $data = array_merge($data,int_helper::uInt8(0));
        $data = array_merge($data,int_helper::uInt32(0));

        echolog($data);
        $this->serversend($serv, $fd, $data);
    }

    /**
     * websocket握手和消息分发
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param string $data
     */
    public function send($serv, $fd, $data)
    {
        if (!empty($data)) {
            $connectionCls = new Connection();

            // websocket握手，如果是握手则直接返回
            if ($this->wsHandShake($serv, $fd, $data)) {
                echolog("websocket handsake.");
                $connectionCls->saveConnector($fd, Clientstate::CONNECTION_TYPE_WEBSOCKET);
                return;
            }

            // 判断客户端类型，对websocket的消息进行解包
            $connectionType = $connectionCls->getConnectionType($fd);
            if ($connectionType == Clientstate::CONNECTION_TYPE_WEBSOCKET) {
                echolog("I am websocket.");
                $ws   = new WebSocket();
                $data = $ws->unwrap($data);
            }

            // 验证逻辑
            echolog($data);
            
            $data = int_helper::getBytes($data);
            echolog(json_encode($data));

            $this->handlePacket($serv, $fd, $data);

            /*貌似客户端是按长度来约定结束的, 不需要解包拼包*/
            // 数据拆包
            // $messageArr = (new MessageCache())->getSplitDataList($fd, $data);

            // // 如果没有完整的消息，则直接返回，直到收到完整消息再处理
            // echolog($messageArr);
            // if (empty($messageArr) && !is_array($messageArr)) {
            //     return;
            // }

            // // 将所有收到的所有完整消息进行投递处理
            // for ($i = 0; $i < count($messageArr); $i++) {
            //     $this->sendMessage($serv, $fd, $messageArr[$i], $connectionType);
            // }
        }
    }

    /**
     * [handlePacket 根据当前ClientState处理传入的数据包]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function handlePacket($serv, $fd, $data)
    {
        $this->serversend($serv, $fd, $data);
    }

    /**
     * [serversend 发送]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-27
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function serversend($serv, $fd, $data = null)
    {
        $serv->send($fd, int_helper::toStr($data));
    }

    /**
     * websocket握手
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param string $data
     * @return boolean 如果为websocket连接则进行握手，握手成功返回true，否则返回false
     */
    private function wsHandShake($serv, $fd, $data)
    {
        // 判断客户端类型 通过websocket握手时的关键词进行判断
        if (strpos($data, "Sec-WebSocket-Key") > 0) {
            $ws            = new WebSocket();
            $handShakeData = $ws->getHandShakeHeaders($data);
            $serv->send($fd, $handShakeData);
            return true;
        }
        return false;
    }
}
