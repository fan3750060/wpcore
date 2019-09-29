<?php
namespace app\World;

use app\World\Message;

/**
 * opcode映射
 */
class Reflection
{
    private static $mapOpcode = [

        // 角色相关
        'CMSG_CHAR_ENUM'                => ['app\World\Character\Character', 'CharacterCharEnum'],
        'CMSG_PING'                     => ['app\World\Ping\PongHandler', 'LoadPongHandler'],
        'CMSG_CHAR_CREATE'              => ['app\World\Character\Character', 'CharacterCreate'],
        'CMSG_CHAR_DELETE'              => ['app\World\Character\Character', 'CharacterDelete'],
        'CMSG_REALM_SPLIT'              => ['app\World\Login\PlayerLogin', 'LoadPelamSplit'],
        'CMSG_SET_ACTIVE_VOICE_CHANNEL' => ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],
        'CMSG_VOICE_SESSION_ENABLE'     => ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],

        // 登录世界
        'CMSG_PLAYER_LOGIN'             => [
            ['app\World\Login\PlayerLogin', 'LoadLoginVerifyWorld'],
            ['app\World\Login\PlayerLogin', 'LoadAccountDataTimes'],
            ['app\World\Login\PlayerLogin', 'LoadFeatureSystemStatus'],
            ['app\World\Login\PlayerLogin', 'LoadMotd'],
            ['app\World\Login\PlayerLogin', 'LoadTutorialFlags'],
            ['app\World\Login\PlayerLogin', 'LoadInitialSpells'],
            ['app\World\Login\PlayerLogin', 'LoginObject'],
            ['app\World\Login\PlayerLogin', 'SetTimeSpeed'],
            ['app\World\Login\PlayerLogin', 'LoadTimeSyncReq'],
        ],
        'CMSG_NAME_QUERY'               => ['app\World\Query\QueryResponse', 'QueryName'],
        'CMSG_QUERY_TIME'               => ['app\World\Query\QueryResponse', 'QueryTime'],
        'CMSG_LOGOUT_REQUEST'           => ['app\World\Query\QueryResponse', 'LogOut'],
        'LogOutTimer'                   => ['app\World\Query\QueryResponse', 'LogOutTimer'], //退出倒计时定时器
        'LogOutComplete'                => ['app\World\Query\QueryResponse', 'LogOutComplete'], //退出完成
        'CMSG_LOGOUT_CANCEL'            => ['app\World\Query\QueryResponse', 'LogOutCancel'],

        //移动处理
        'MSG_MOVE_START_FORWARD'        => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_TURN_LEFT'      => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_TURN_RIGHT'     => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_STOP_STRAFE'          => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_STRAFE_LEFT'    => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_STRAFE_RIGHT'   => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_BACKWARD'       => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_SWIM'           => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_STOP_SWIM'            => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_SET_PITCH'            => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_START_ASCEND'         => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_JUMP'                 => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_FALL_LAND'            => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_STOP'                 => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_STOP_TURN'            => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_SET_FACING'           => ['app\World\Movement\MovementHandler', 'MoveSetList'],
        'MSG_MOVE_HEARTBEAT'            => ['app\World\Movement\MovementHandler', 'MoveSetList'],
    ];

    public static function LoadClass($opcode, $serv, $fd, $data = null, $mapOpcode = null)
    {
        if (!$mapOpcode) {
            $mapOpcode = self::$mapOpcode;
        }

        if (isset($mapOpcode[$opcode]) && $mapinfo = $mapOpcode[$opcode]) {
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
            if ($packdata = $classObject->invokeArgs(null, [$serv, $fd, $data])) {
                Message::serversend($serv, $fd, $packdata);
            }
        } else {
            if ($packdata = $classObject->invokeArgs(new $class, [$serv, $fd, $data])) {
                Message::serversend($serv, $fd, $packdata);
            }
        }
    }
}
