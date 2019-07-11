<?php
namespace app\World;

use app\Common\int_helper;
use app\World\Clientstate;
use app\World\Connection;

class Message
{
    /**
     * 握手和消息分发
     *
     * @param swoole_server $serv
     * @param int $fd
     * @param string $data
     */
    public function serverreceive($serv, $fd, $data)
    {
        if (!empty($data)) {
            $connectionCls = new Connection();

            // 状态
            $state = $connectionCls->getConnectorState($fd);

            $data = int_helper::getBytes($data);

            WORLD_LOG("Receive: " . json_encode($data), 'info');

            $this->handlePacket($serv, $fd, $data, $state);
        }
    }

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
        WORLD_LOG('[SMSG_AUTH_CHALLENGE]: Send Client : ' . $fd, 'warning');

        //要求客户端鉴权
        $data = [0x00, 0x2a, 0xec, 0x01, 0x01, 0x00, 0x00, 0x00, 0x8a, 0xd0, 0x07, 0x33, 0x37, 0x33, 0xe6, 0x9c, 0x11, 0xcd, 0x6b, 0x73,
            0x24, 0xfe, 0x8d, 0x6d, 0x2a, 0x53, 0xdf, 0x91, 0xcb, 0x15, 0x27, 0xeb, 0x02, 0x7d, 0x41, 0x26, 0x15, 0xd6, 0xd6, 0xc8, 0x05, 0x3b, 0x7b, 0xe2];

        $this->serversend($serv, $fd, $data);
    }

    /**
     * [checkauth 处理验证]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-01
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function checkauth($fd, $data)
    {
        WORLD_LOG('[SMSG_AUTH_RESPONSE]: Send Client : ' . $fd, 'warning');

        $data = [0x0C, 0x00, 0x00, 0x00, 0x2];
        return $data;
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
    public function handlePacket($serv, $fd, $data, $state)
    {
        switch ($state) {
            case 1:
                $data = $this->checkauth($fd, $data);
                $this->serversend($serv, $fd, $data);
                break;
        }
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
        WORLD_LOG("Send: " . json_encode($data), 'info');
        $serv->send($fd, int_helper::toStr($data));
    }
}
