<?php
namespace app\Auth;

use app\Auth\Challenge;
use app\Auth\Clientstate;
use app\Auth\Realmlist;
use app\Common\Account;

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

            $data = GetBytes($data);

            AUTH_LOG("Receive: " . json_encode($data), 'info');

            $this->handlePacket($serv, $fd, $data, AuthServer::$clientparam[$fd]['state']);
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
                // file_put_contents('runtime/1_auth_.log', ToStr($data));

                $Challenge      = new Challenge();
                $userinfo       = $Challenge->getinfo_ClientLogonChallenge($data); //解析数据包
                $userinfo['ip'] = $serv->getClientInfo($fd)['remote_ip']; //数据包ip不对(路由的分配ip),获取真实ip地址

                AUTH_LOG('Verify the account for SRP operations :' . $userinfo['username'], 'warning');

                $Account = new Account();

                //1) 检查该IP是否被封禁
                $ip_banned = $Account->ip_banned($userinfo['ip']);
                if ($ip_banned) {
                    AUTH_LOG('IP is banned : ' . $userinfo['username'], 'warning');

                    // IP被冻结
                    $this->serversend($serv, $fd, [0, 0, HexToDecimal(Clientstate::WOW_FAIL_GAME_ACCOUNT_LOCKED)]);
                    return;
                }

                //2) 查询数据库中是否有该账户
                $account_info = $Account->get_account($userinfo['username']);
                if (!$account_info) {
                    AUTH_LOG('Account does not exist : ' . $userinfo['username'], 'warning');

                    // 账户不存在
                    $this->serversend($serv, $fd, [0, 0, HexToDecimal(Clientstate::WOW_FAIL_UNKNOWN_ACCOUNT)]);
                    return;
                }

                //3) 检查该账号是否被封禁
                $account_banned = $Account->account_banned($account_info['id']);
                if ($account_banned) {
                    AUTH_LOG('Account is banned : ' . $userinfo['username'], 'warning');

                    // 账户被冻结
                    $this->serversend($serv, $fd, [0, 0, HexToDecimal(Clientstate::WOW_FAIL_BANNED)]);
                    return;
                }

                //4)开始SRP6计算
                $returndata = $Challenge->getAuthSrp($fd,$account_info); //开始验证
                $this->serversend($serv, $fd, $returndata);

                //5) 缓存用户信息
                $userinfo['id'] = $account_info['id'];

                //初始化auth状态 1
                AuthServer::$clientparam[$fd]['state']    = Clientstate::ClientLogonChallenge;
                AuthServer::$clientparam[$fd]['username'] = $userinfo['username'];
                AuthServer::$clientparam[$fd]['userinfo'] = json_encode($userinfo);

                break;

            case Clientstate::ClientLogonChallenge:
                // file_put_contents('runtime/2_auth_.log', ToStr($data));

                $username = AuthServer::$clientparam[$fd]['username'];

                $Challenge = new Challenge();
                $data      = $Challenge->AuthServerLogonChallenge($fd,$data, $username); //校验

                if (count($data) != 3) {
                    AUTH_LOG('Password verification succeeded', 'success');

                    // 验证成功
                    $this->serversend($serv, $fd, $data);

                    //初始化auth状态 5
                    AuthServer::$clientparam[$fd]['state'] = Clientstate::Authenticated;

                    //更新用户信息
                    $userinfo = json_decode(AuthServer::$clientparam[$fd]['userinfo'], true);

                    if ($userinfo) {
                        //获取sessionkey
                        $userinfo['sessionkey'] = AuthServer::$clientparam[$fd]['seesionkey'];

                        $Account = new Account();
                        $Account->updateinfo($userinfo);
                    }

                } else {
                    AUTH_LOG('Password verification failed', 'warning');

                    // 验证失败
                    $this->serversend($serv, $fd, [0, 0, HexToDecimal(Clientstate::WOW_FAIL_INCORRECT_PASSWORD)]);

                    //初始化auth状态 0
                    AuthServer::$clientparam[$fd]['state'] = Clientstate::Init;
                }

                break;

            case Clientstate::Authenticated:
                // file_put_contents('runtime/3_auth_Authenticated.log', $data);

                AUTH_LOG('Get server domain list');

                $userinfo = json_decode(AuthServer::$clientparam[$fd]['userinfo'], true);

                // 获取服务器列表
                $Realmlist = new Realmlist();
                $RealmInfo = $Realmlist->get_realmlist(['accountId' => $userinfo['id']]);

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
        AUTH_LOG("Send: " . json_encode($data), 'info');

        $serv->send($fd, ToStr($data));
    }
}
