<?php
namespace app\World;
use app\Common\int_helper;
use app\World\Clientstate;
use app\World\Connection;

class Message
{
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

            // 验证逻辑
            $state = $connectionCls->getConnectorUserId($fd);

            $data = int_helper::getBytes($data);

            echolog("接收:" . json_encode($data), 'info');

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
        // $data = [];
        // $data = array_merge($data,int_helper::uInt8(12));
        // $data = array_merge($data,int_helper::uInt32(0));
        // $data = array_merge($data,int_helper::uInt8(0));
        // $data = array_merge($data,int_helper::uInt32(0));

        // echolog($data);
        // $this->serversend($serv, $fd, $data);
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
        // $this->newConnect($serv, $fd, $data);
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
        echolog("发送:" . json_encode($data), 'info');
        $serv->send($fd, int_helper::toStr($data));
    }
}
