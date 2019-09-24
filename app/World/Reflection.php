<?php
namespace app\World;

use app\World\Message;

/**
 * opcode映射
 */
class Reflection
{
    private static $mapOpcode = [
        'CMSG_CHAR_ENUM'                => ['app\World\Character\Character', 'CharacterCharEnum'],
        'CMSG_PING'                     => ['app\World\Ping\PongHandler', 'LoadPongHandler'],
        'CMSG_CHAR_CREATE'              => ['app\World\Character\Character', 'CharacterCreate'],
        'CMSG_CHAR_DELETE'              => ['app\World\Character\Character', 'CharacterDelete'],
        'CMSG_REALM_SPLIT'              => ['app\World\Login\PlayerLogin', 'LoadPelamSplit'],
        'CMSG_SET_ACTIVE_VOICE_CHANNEL' => ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],
        'CMSG_VOICE_SESSION_ENABLE'     => ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],
        'CMSG_PLAYER_LOGIN'             => [
            ['app\World\Login\PlayerLogin', 'LoadLoginVerifyWorld'],
            ['app\World\Login\PlayerLogin', 'LoadAccountDataTimes'],
            ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],
            ['app\World\Login\PlayerLogin', 'LoadMotd'],
            ['app\World\Login\PlayerLogin', 'LoadTutorialFlags'],
            ['app\World\Login\PlayerLogin', 'LoadInitialSpells'],
            ['app\World\Object\WorldObject', 'LoginObject'],
            ['app\World\Login\PlayerLogin', 'SetTimeSpeed'],
            ['app\World\Login\PlayerLogin', 'LoadTimeSyncReq'],
        ],
        'CMSG_NAME_QUERY'               => ['app\World\Query\QueryResponse', 'QueryName'],
        'CMSG_QUERY_TIME'               => ['app\World\Query\QueryResponse', 'QueryTime'],
    ];

    public static function LoadClass($opcode, $serv, $fd, $data = null)
    {
        if (isset(self::$mapOpcode[$opcode]) && $mapinfo = self::$mapOpcode[$opcode]) {
            if (is_array($mapinfo[0])) {
                foreach ($mapinfo as $k => $v) {
                    self::LoadFunc($v[0], $v[1], $serv, $fd, $data);
                }
            } else {
                self::LoadFunc($mapinfo[0], $mapinfo[1], $serv, $fd, $data);
            }
        } else {
            WORLD_LOG('Unknown opcode: ' . $opcode . ' Client : ' . $fd, 'warning');
        }
    }

    public static function LoadFunc($class, $func, $serv, $fd, $data)
    {
        $classObject = new \ReflectionMethod($class, $func);
        if ($classObject->isStatic()) {
            $packdata = $classObject->invokeArgs(null, [$serv, $fd, $data]);
            Message::serversend($serv, $fd, $packdata);
        } else {
            $packdata = $classObject->invokeArgs(new $class, [$serv, $fd, $data]);
            Message::serversend($serv, $fd, $packdata);
        }
    }
}
