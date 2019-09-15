<?php
namespace app\World;

use app\Auth\Clientstate;
use core\lib\Cache;

class Connection
{
    /**
     * 获取连接信息
     * @param int $fd
     * 连接id
     * @return array
     */
    public static function getConnector($fd)
    {
        return Cache::drive('redis')->get($fd . 'connector');
    }

    /**
     * 保存当前连接到连接池
     * @param int $fd
     * @param int
     * @param int $userId
     */
    public static function saveConnector($fd, $param)
    {
        $arr = self::getConnector($fd);

        $arr['fd'] = $fd;

        // 保存连接
        Cache::drive('redis')->set($fd . 'connector', $arr, 0);
    }

    //当客户端发送数据后删除待检池
    public static function update_checkTable($fd)
    {
        $checkconnector = Cache::drive('redis')->get('checkconnector');

        if ($checkconnector && is_array($checkconnector)) {
            $newcheckconnector = [];
            foreach ($checkconnector as $k => $v) {
                if ($v['fd'] != $fd) {
                    $newcheckconnector[] = $v;
                }
            }

            Cache::drive('redis')->set('checkconnector', $newcheckconnector);

            //更新状态
            WorldServer::$clientparam[$fd]['state'] = Clientstate::ClientLogonChallenge;
        }
    }

    /**
     * 将连接从连接池中移除，并移除用户信息
     * @param int $fd
     */
    public static function removeConnector($fd)
    {
        // 移除连接池
        Cache::drive('redis')->delete($fd . 'connector');
    }

    /**
     * 验证用户 包括加密解密验证、token验证、时效性验证
     * @param string $token
     * 登录凭证
     * @return boolean
     */
    public static function validateConnector($token)
    {
        // TODO 根据实际情况对token进行验证，这里直接通过
        return true;
    }

    /**
     * 清理非法连接
     *
     * @param swoole_server $serv
     */
    public static function clearInvalidConnection($serv)
    {
        $checkconnector = Cache::drive('redis')->get('checkconnector');
        if ($checkconnector && is_array($checkconnector)) {
            $newcheckconnector = $checkconnector;

            foreach ($checkconnector as $k => $v) {

                if (WorldServer::$clientparam[$v['fd']]['state'] > Clientstate::Init || !$serv->exist($v['fd'])) {

                    WORLD_LOG("Remove to be connected : " . $v['fd']);

                    //已正常连接或者连接已不存在从待检池移除
                    unset($newcheckconnector[$k]);

                    continue;
                }

                $createTime = $v["createTime"];

                if ($createTime < strtotime("-5 seconds")) {

                    WORLD_LOG("Expired! Remove and close : " . $v['fd']);

                    //过期，从待检池移除并关闭连接
                    unset($newcheckconnector[$k]);

                    $serv->close($v['fd']);

                    continue;
                }
            }

            Cache::drive('redis')->set('checkconnector', $newcheckconnector);
        }
    }

    /**
     * 保存连接到待检池
     * @param int $fd
     */
    public static function saveCheckConnector($fd)
    {
        $data = [
            [
                'fd'         => $fd,
                'createTime' => time(),
            ],
        ];

        Cache::drive('redis')->set('checkconnector', $data, 60 * 2);
    }
}
