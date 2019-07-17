<?php
namespace app\World;

use app\Common\int_helper;
use app\World\Clientstate;
use app\World\Connection;
use app\World\Authchallenge;

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
            $state = $connectionCls->getCache($fd,'state');

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

        $Authchallenge = new Authchallenge();

        $data = $Authchallenge->Authchallenge($fd);

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
        WORLD_LOG('[CMSG_AUTH_SESSION]: Send Client : ' . $fd, 'warning');

        $Authchallenge = new Authchallenge();

        $data = $Authchallenge->AuthSession($fd,$data);

        return $data;
    }

    /**
     * [Offline 下线]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-17
     * ------------------------------------------------------------------------------
     * @param   [type]          $fd [description]
     */
    public function Offline($fd)
    {
        $connectionCls = new Connection();
        $username = $connectionCls->getCache($fd,'username');

        $Account = new \app\Common\Account();
        $Account -> Offline($username);
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
                $opcode = \app\World\Worldpackt::getopcode($data);

                switch ($opcode) {
                    case 'CMSG_AUTH_SESSION':
                        $data = $this->checkauth($fd, $data);
                        WORLD_LOG('[SMSG_AUTH_RESPONSE]: Send Client : ' . $fd, 'warning');
                        $this->serversend($serv, $fd, $data);
                        break;

                    default:
                        WORLD_LOG('[CMSG_PING]: Send Client : ' . $fd, 'warning');
                        $data = [0x00,0x00,0x00,0x00];
                        $this->serversend($serv, $fd, $data);
                    break;
                }

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
