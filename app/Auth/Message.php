<?php
namespace app\Auth;

use app\Auth\Clientstate;
use app\Auth\Connection;
use app\Auth\WebSocket;
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
            $state = $connectionCls->getConnectorUserId($fd);

            $data = int_helper::getBytes($data);

            $this->handlePacket($serv, $fd, $data, $state);

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
     * [getinfo_ClientLogonChallenge 解包数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getinfo_ClientLogonChallenge($data)
    {
        $info                  = [];
        $info['cmd']           = $data[0]; //命令
        $info['error']         = $data[1]; //错误
        $info['size']          = array_slice($data, 2, 2);
        $info['gamename']      = strrev(int_helper::toStr(array_slice($data, 4, 3)));
        $info['version']       = $data[8] . '.' . $data[9] . '.' . $data[10];
        $info['build']         = array_slice($data, 11, 1);
        $info['platform']      = strrev(int_helper::toStr(array_slice($data, 13, 3)));
        $info['os']            = strrev(int_helper::toStr(array_slice($data, 17, 3)));
        $info['country']       = strrev(int_helper::toStr(array_slice($data, 21, 4)));
        $info['timezone_bias'] = array_slice($data, 25, 4);
        $info['ip']            = implode('.', array_slice($data, 29, 4));

        $info['user_lenth'] = $data[33]; //用户名长度
        $info['username']   = array_slice($data, 34, $info['user_lenth']); //截取用户名
        $info['username']   = int_helper::toStr($info['username']);

        return $info;
    }

    /**
     * AuthServerLogonChallenge 验证数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function AuthServerLogonChallenge($data, $fd)
    {
        $connectionCls = new Connection();
        $username      = $connectionCls->getConnectorUsername($fd);
        
        // 借助python实现srp6验证(字符类型)
        $output = @shell_exec('python core.py 1 ' . $username . ' ' . base64_encode(int_helper::toStr($data)));
        $data   = trim($output);
        $data   = substr($data, 1, -1);
        $data   = str_replace(' ', '', $data);
        $data   = explode(',', $data);
        foreach ($data as $k => $v) {
            $data[$k] = (int) $v;
        }
        echolog(int_helper::toStr($data));
        return $data;
    }

    /**
     * [getAuthSrp 获取Srp]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-20
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getAuthSrp($data = null)
    {
        // // 借助python实现srp6验证(betys)
        // $output = @shell_exec('python core.py 0 '.$data['username']);
        // $array = explode(',', $output);
        // $data = $array[0];
        // return $data;

        // 借助python实现srp6验证(字符类型)
        $output = @shell_exec('python core.py 0 ' . $data['username']);
        $data   = trim($output);
        $data   = substr($data, 1, -1);
        $data   = explode(',', $data);
        return $data;
    }

    /**
     * [getRealmInfo 获取服务器列表]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-27
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getRealmInfo()
    {
        // 模拟数据
        $name       = 'WpcoreServer'; //服务器名称
        $addr_port  = '127.0.0.1:13250'; //服务器端口
        $population = 'zhCN'; //服务器本地化

        $type_b     = [0, 0, 0, 0];
        $flags      = int_helper::HexToDecimal('0x00');
        $num_chars  = int_helper::HexToDecimal('0x00');
        $time_zone  = int_helper::HexToDecimal('0x00');
        $unknown    = int_helper::HexToDecimal('0x00');
        $cmd        = int_helper::HexToDecimal('0x10');
        $name       = array_merge(int_helper::getBytes($name), [0]);
        $addr_port  = array_merge(int_helper::getBytes($addr_port), [0]);
        $population = int_helper::getBytes($population);

        // 拼装服内容信息
        $RealmInfo_Server = [];
        foreach ($type_b as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        $RealmInfo_Server[] = $flags;

        foreach ($name as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        foreach ($addr_port as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        foreach ($population as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        $RealmInfo_Server[] = $num_chars;
        $RealmInfo_Server[] = $time_zone;
        $RealmInfo_Server[] = $unknown;

        //拼装服脚信息
        $RealmFooter_Server = [0, 0];

        //拼装服头信息
        $length               = 7 + count($RealmInfo_Server);
        $length_b             = [$length, 0];
        $unk                  = [0, 0, 0, 0];
        $num_realms           = int_helper::HexToDecimal('0x01');
        $RealmHeader_Server   = [];
        $RealmHeader_Server[] = $cmd;

        foreach ($length_b as $k => $v) {
            $RealmHeader_Server[] = $v;
        }

        foreach ($unk as $k => $v) {
            $RealmHeader_Server[] = $v;
        }

        $RealmHeader_Server[] = $num_realms;

        return [$RealmHeader_Server, $RealmInfo_Server, $RealmFooter_Server];
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

        switch ($state) {
            case Clientstate::Init:
                // 第一步srp6运算
                // file_put_contents('auth_'.time() . '_ClientLogonChallenge.log', $data . PHP_EOL, FILE_APPEND);
                $data = $this->getinfo_ClientLogonChallenge($data); //解析数据包

                echolog('Verify the account for SRP operations');

                //ToDo
                /*
                1) 检查该ip是否被封禁，假如是发送相应的错误

                　　2) 查询数据库中是否有该账户，假如没有返回相应的错误

                　　3) 查看最后一次登录ip与账户是否绑定，假如绑定对比当前ip与last_ip是否一致

                　　4) 检查该账号是否被封禁，假如是发送相应的错误信息

                　　5) 获取用户名密码，开始SRP6计算(已模拟完成)

                6) _accountSecurityLevel，保存用户的权限等级，普通用户、GM、admin等等

                7) 本地化：根据_localizationName的名字找对应的.mpq文件所在的位置比如enUS，zhTW，zhCN
                 */

                $Connection->saveConnector($fd, 0, Clientstate::ClientLogonChallenge, $data['username']); //初始化auth状态 1

                $data = $this->getAuthSrp($data);

                $this->serversend($serv, $fd, $data);
                break;

            case Clientstate::ClientLogonChallenge:
                // 第二步srp6校验
                // file_put_contents('auth_'.time() . '_ServerLogonChallenge.log', $data . PHP_EOL, FILE_APPEND);

                $data = $this->AuthServerLogonChallenge($data, $fd); //验证

                $this->serversend($serv, $fd, $data);

                if (count($data) != 3) {
                    $Connection->saveConnector($fd, 0, Clientstate::Authenticated); //初始化auth状态 5
                    echolog('Password verification succeeded');
                } else {
                    $Connection->saveConnector($fd, 0, Clientstate::Init); //初始化auth状态 0
                    $serv->close($key); //关闭连接
                    echolog('Password verification failed');
                }

                break;

            case Clientstate::Authenticated:
                // 第三步获取服务器列表
                // file_put_contents('auth_'.time() . '_Authenticated.log', $data . PHP_EOL, FILE_APPEND);

                echolog('Get server domain list');
                $data = $this->getRealmInfo();

                //分批发包
                $this->serversend($serv, $fd, $data[0]);
                $this->serversend($serv, $fd, $data[1]);
                $this->serversend($serv, $fd, $data[2]);

                //一次性发包
                // $RealmInfo = array_merge($data[0],$data[1],$data[2]);
                // $this->serversend($serv,$fd,$RealmInfo);
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
