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

            // 验证逻辑
            $state = $connectionCls->getConnectorState($fd);

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
        switch ($state) {
            case Clientstate::Init:
                // file_put_contents('runtime/1_auth_.log', int_helper::toStr($data));

                $Challenge  = new Challenge();
                $userinfo       = $Challenge->getinfo_ClientLogonChallenge($data); //解析数据包
                $userinfo['ip'] = $serv->getClientInfo($fd)['remote_ip']; //数据包ip不对(路由的分配ip),获取真实ip地址

                echolog('Verify the account for SRP operations :' . $userinfo['username'], 'warning');

                $Account = new Account();

                //1) 检查该IP是否被封禁
                $ip_banned = $Account->ip_banned($userinfo['ip']);
                if ($ip_banned) {
                    echolog('IP is banned : ' . $userinfo['username'], 'warning');

                    // IP被冻结
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_GAME_ACCOUNT_LOCKED)]);
                    return;
                }

                //2) 查询数据库中是否有该账户
                $account_info = $Account->get_account($userinfo['username']);
                if (!$account_info) {
                    echolog('Account does not exist : ' . $userinfo['username'], 'warning');

                    // 账户不存在
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_UNKNOWN_ACCOUNT)]);
                    return;
                }

                //3) 检查该账号是否被封禁
                $account_banned = $Account->account_banned($account_info['id']);
                if ($account_banned) {
                    echolog('Account is banned : ' . $userinfo['username'], 'warning');

                    // 账户被冻结
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_BANNED)]);
                    return;
                }

                //4)开始SRP6计算
                $returndata = $Challenge->getAuthSrp($account_info); //开始验证
                $this->serversend($serv, $fd, $returndata);

                //5) 缓存用户信息
                $Connection = new Connection();
                $Connection->saveConnector($fd,Clientstate::ClientLogonChallenge, $userinfo['username'],json_encode($userinfo)); //初始化auth状态 1
                break;

            case Clientstate::ClientLogonChallenge:
                // file_put_contents('runtime/2_auth_.log', int_helper::toStr($data));

                $Connection = new Connection();
                $username   = $Connection->getConnectorUsername($fd);

                $Challenge = new Challenge();
                $data      = $Challenge->AuthServerLogonChallenge($data, $username); //校验

                if (count($data) != 3) {
                    echolog('Password verification succeeded', 'success');

                    // 验证成功
                    $this->serversend($serv, $fd, $data);

                    $Connection->saveConnector($fd,Clientstate::Authenticated); //初始化auth状态 5

                    //更新用户信息
                    $Connection = new Connection();
                    $userinfo   = json_decode($Connection->getConnectorUserInfo($fd),true);
                    if($userinfo)
                    {
                        //获取sessionkey
                        $sessionkey = $Challenge->AuthServerSeesionKey($username);
                        $userinfo['sessionkey'] = $sessionkey;

                        $Account = new Account();
                        $Account->updateinfo($userinfo);
                    }

                } else {
                    echolog('Password verification failed', 'warning');

                    // 验证失败
                    $this->serversend($serv, $fd, [0, 0, int_helper::HexToDecimal(Clientstate::WOW_FAIL_INCORRECT_PASSWORD)]);

                    $Connection->saveConnector($fd,Clientstate::Init); //初始化auth状态 0
                }

                break;

            case Clientstate::Authenticated:
                // file_put_contents('runtime/3_auth_Authenticated.log', $data);

                echolog('Get server domain list');

                //模拟数据
                // $data = [0x10,0x27,0x00,0x00,0x00,0x00,0x00,0x01,0x00,0x00,0x00,0x00,0x58,0x59,0x57,0x4f,0x57,0x00,0x31,0x32,0x37,0x2e,0x30,0x2e,0x30,0x2e,0x31,0x3a,0x38,0x30,0x38,0x35,0x00,0x00,0x00,0x00,0x00,0x01,0x01,0x2c,0x10,0x00];
                // $this->serversend($serv, $fd, $data);

                // 获取服务器列表
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
