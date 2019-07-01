<?php
namespace app\Auth;

use app\Auth\Challenge;
use app\Auth\Clientstate;
use app\Auth\Connection;
use app\Auth\Realmlist;
use app\Common\Account;
use app\Common\int_helper;

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
        $Connection = new Connection();
        $Challenge  = new Challenge();

        switch ($state) {
            case Clientstate::Init:
                //srp6运算
                file_put_contents('runtime/1_auth_.log', int_helper::toStr($data));

                $data = $Challenge->getinfo_ClientLogonChallenge($data); //解析数据包

                echolog('Verify the account for SRP operations :' . $data['username'], 'warning');

                //1) 查询数据库中是否有该账户，假如没有返回相应的错误
                $Account      = new Account();
                $account_info = $Account->get_account($data['username']);

                if (!$account_info) {
                    echolog('Account does not exist : ' . $data['username'], 'warning');

                    // 账户不存在
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_UNKNOWN_ACCOUNT)]);
                    return;
                }

                //2) 检查该账号是否被封禁，假如是发送相应的错误信息
                $account_banned = $Account->account_banned($account_info['id']);
                if ($account_banned) {
                    echolog('Account is banned : ' . $data['username'], 'warning');

                    // 账户被冻结
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_BANNED)]);
                    return;
                }

                //3) 获取用户名密码，开始SRP6计算
                $Connection->saveConnector($fd, 0, Clientstate::ClientLogonChallenge, $data['username']); //初始化auth状态 1

                $data = $Challenge->getAuthSrp($account_info); //开始验证

                $this->serversend($serv, $fd, $data);
                break;

            case Clientstate::ClientLogonChallenge:
                //srp6校验
                file_put_contents('runtime/2_auth_.log', int_helper::toStr($data));
                $username = $Connection->getConnectorUsername($fd);

                $data = $Challenge->AuthServerLogonChallenge($data, $username); //校验

                if (count($data) != 3) {
                    echolog('Password verification succeeded', 'success');

                    // 验证成功
                    $this->serversend($serv, $fd, $data);

                    $Connection->saveConnector($fd, 0, Clientstate::Authenticated); //初始化auth状态 5

                } else {
                    echolog('Password verification failed', 'warning');

                    // 验证失败
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_INCORRECT_PASSWORD)]);

                    $Connection->saveConnector($fd, 0, Clientstate::Init); //初始化auth状态 0
                }

                break;

            case Clientstate::Authenticated:
                // 第三步获取服务器列表
                file_put_contents('runtime/3_auth_Authenticated.log', $data);

                echolog('Get server domain list');

                //模拟数据
                // $data = [0x10,0x27,0x00,0x00,0x00,0x00,0x00,0x01,0x00,0x00,0x00,0x00,0x58,0x59,0x57,0x4f,0x57,0x00,0x31,0x32,0x37,0x2e,0x30,0x2e,0x30,0x2e,0x31,0x3a,0x38,0x30,0x38,0x35,0x00,0x00,0x00,0x00,0x00,0x01,0x01,0x2c,0x10,0x00];
                // $this->serversend($serv, $fd, $data);

                $Realmlist = new Realmlist();
                $RealmInfo = $Realmlist->get_realmlist();
                $this->serversend($serv, $fd, $RealmInfo);
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
        echolog("发送:" . json_encode($data), 'info');
        $serv->send($fd, int_helper::toStr($data));
    }
}
