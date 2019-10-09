<?php
namespace app\World\Query;

use app\Common\Srp6;
use app\World\Movement\MovementHandler;
use app\World\OpCode;
use app\World\WorldServer;
use core\query\DB;

/**
 *  查询
 */
class QueryResponse
{
    //人物名称属性
    public static function QueryName($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_NAME_QUERY_RESPONSE] Client : ' . $fd, 'warning');

        $Srp6 = new Srp6();
        $guid = HexToDecimal($Srp6->Littleendian($Srp6->BigInteger(ToStr($data), 256)->toHex())->toHex());

        $characters = DB::table('characters', 'characters')->where(['guid' => $guid])->find();
        $name       = $characters['name'];
        $name_len   = strlen($characters['name']);
        $packdata   = pack("QZ*cI3c",
            $characters['guid'],
            $name,
            0,
            $characters['race'],
            $characters['gender'],
            $characters['class'],
            0
        );
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_NAME_QUERY_RESPONSE, $packdata];
    }

    //查询时间响应
    public static function QueryTime($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_QUERY_TIME_RESPONSE] Client : ' . $fd, 'warning');

        $packdata = pack('I2', time(), 0);
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_QUERY_TIME_RESPONSE, $packdata];
    }

    //退出到角色
    public static function LogOut($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_LOGOUT_RESPONSE] Client : ' . $fd, 'warning');

        //TODO 判断处于无法坐下的状态,跳跃
        if (false) {
            $packdata = pack('cIc', 0xC, 0, 0);
        } else {
            //正常退出
            $packdata = pack('Ic', 0, 0);

            //投递退出任务
            $param = [
                'opcode' => 'LogOutTimer', 'callback' => 'LogOutComplete', 'data' => ['time' => config('LOGOUTCOMPLETETIME'), 'fd' => $fd],
            ];
            $serv->task_id = $serv->task($param);

            WorldServer::$clientparam[$fd]['goonlogout'] = true;

            if (!empty(WorldServer::$clientparam[$fd]['player']['GetMovementInfo'])) {
                //保持最后位置
                MovementHandler::UpdateSetMove($serv, $fd, WorldServer::$clientparam[$fd]['player']['GetMovementInfo']);
            }
        }

        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_LOGOUT_RESPONSE, $packdata];
    }

    //退出计时器
    public static function LogOutTimer($serv, $fd, $data = null)
    {
        sleep($data['time']);

        return false;
    }

    //退出完成
    public static function LogOutComplete($serv, $fd, $data = null)
    {
        if (WorldServer::$clientparam[$fd]['goonlogout']) {
            WORLD_LOG('[SMSG_LOGOUT_COMPLETE] Client : ' . $fd, 'warning');
            $packdata = pack('c', 0);
            $packdata = GetBytes($packdata);

            return [OpCode::SMSG_LOGOUT_COMPLETE, $packdata];
        }
    }

    //取消退出
    public static function LogOutCancel($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_LOGOUT_CANCEL_ACK] Client : ' . $fd, 'warning');

        WorldServer::$clientparam[$fd]['goonlogout'] = false;

        $packdata = pack('c', 0);

        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_LOGOUT_CANCEL_ACK, $packdata];
    }
}
