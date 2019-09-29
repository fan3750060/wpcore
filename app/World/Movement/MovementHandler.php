<?php
namespace app\World\Movement;

use app\World\Object\ObjectPublic;
use app\World\WorldServer;
use core\query\DB;

/**
 * 移动
 */
class MovementHandler
{
    //解包获取位置信息
    public static function GetMovementInfo($data)
    {
        $next_length             = 0;
        $length                  = 4;
        $param['movement_flags'] = ToStr(array_slice($data, $next_length, $length));
        // $param['movement_flags'] = Littleendian(UnPackInt($param['movement_flags'], 16))->toString();
        $param['movement_flags'] = unpack('c', $param['movement_flags'])[1];
        $next_length += $length;

        foreach (ObjectPublic::MovementFlags as $k => $v) {
            if ($param['movement_flags'] == $v) {
                $param['movement_flags'] = $v;
                break;
            } else {
                $param['movement_flags'] = ObjectPublic::MovementFlags['NONE'];
            }
        }

        $length                   = 1;
        $param['movement_flags2'] = ToStr(array_slice($data, $next_length, $length));
        $param['movement_flags2'] = unpack('c', $param['movement_flags2'])[1];
        $next_length += $length;

        $length        = 4;
        $param['time'] = ToStr(array_slice($data, $next_length, $length));
        $param['time'] = unpack('I', $param['time'])[1];
        $next_length += $length;

        $length     = 4;
        $param['x'] = ToStr(array_slice($data, $next_length, $length));
        $param['x'] = unpack('f', $param['x'])[1];
        $next_length += $length;

        $length     = 4;
        $param['y'] = ToStr(array_slice($data, $next_length, $length));
        $param['y'] = unpack('f', $param['y'])[1];
        $next_length += $length;

        $length     = 4;
        $param['z'] = ToStr(array_slice($data, $next_length, $length));
        $param['z'] = unpack('f', $param['z'])[1];
        $next_length += $length;

        $length               = 4;
        $param['orientation'] = ToStr(array_slice($data, $next_length, $length));
        $param['orientation'] = unpack('f', $param['orientation'])[1];
        $next_length += $length;

        if ($param['movement_flags'] & ObjectPublic::MovementFlags['ONTRANSPORT']) {
            $length                  = 8;
            $param['transport_guid'] = ToStr(array_slice($data, $next_length, $length));
            // $param['transport_guid'] = unpack('f',$param['transport_guid'])[1];
            $next_length += $length;

            $length           = 4;
            $param['trans_x'] = ToStr(array_slice($data, $next_length, $length));
            $param['trans_x'] = unpack('f', $param['trans_x'])[1];
            $next_length += $length;

            $length           = 4;
            $param['trans_y'] = ToStr(array_slice($data, $next_length, $length));
            $param['trans_y'] = unpack('f', $param['trans_y'])[1];
            $next_length += $length;

            $length           = 4;
            $param['trans_z'] = ToStr(array_slice($data, $next_length, $length));
            $param['trans_z'] = unpack('f', $param['trans_z'])[1];
            $next_length += $length;

            $length                  = 4;
            $param['transport_time'] = ToStr(array_slice($data, $next_length, $length));
            $param['transport_time'] = unpack('f', $param['transport_time'])[1];
            $next_length += $length;

        }

        if ($param['movement_flags'] & ObjectPublic::MovementFlags['SWIMMING']) {
            $length              = 4;
            $param['swim_pitch'] = ToStr(array_slice($data, $next_length, $length));
            $param['swim_pitch'] = unpack('f', $param['swim_pitch'])[1];
            $next_length += $length;
        }

        if ($param['movement_flags'] & ObjectPublic::MovementFlags['FALLING']) {
            $length                 = 4;
            $param['jump_velocity'] = ToStr(array_slice($data, $next_length, $length));
            $param['jump_velocity'] = unpack('f', $param['jump_velocity'])[1];
            $next_length += $length;

            $length                  = 4;
            $param['jump_sin_angle'] = ToStr(array_slice($data, $next_length, $length));
            $param['jump_sin_angle'] = unpack('f', $param['jump_sin_angle'])[1];
            $next_length += $length;

            $length                  = 4;
            $param['jump_cos_angle'] = ToStr(array_slice($data, $next_length, $length));
            $param['jump_cos_angle'] = unpack('f', $param['jump_cos_angle'])[1];
            $next_length += $length;

            $length                  = 4;
            $param['jump_x_y_speed'] = ToStr(array_slice($data, $next_length, $length));
            $param['jump_x_y_speed'] = unpack('f', $param['jump_x_y_speed'])[1];
            $next_length += $length;
        }

        $length             = 4;
        $param['fall_time'] = ToStr(array_slice($data, $next_length, $length));
        $param['fall_time'] = unpack('f', $param['fall_time'])[1];
        $next_length += $length;

        return $param;
    }

    //更新db
    public static function UpdateSetMove($serv, $fd, $GetMovementInfo = null)
    {
        $where = [
            'guid' => WorldServer::$clientparam[$fd]['player']['guid'],
        ];

        $updatedata = [
            'position_x'  => $GetMovementInfo['x'],
            'position_y'  => $GetMovementInfo['y'],
            'position_z'  => $GetMovementInfo['z'],
            'orientation' => $GetMovementInfo['orientation'],
        ];

        go(function () use ($where, $updatedata) {
            // var_dump('又操作数据库了!!!');
            DB::table('characters', 'characters')->where($where)->update($updatedata);
        });
    }

    //设置移动位置及朝向
    public static function MoveSetList($serv, $fd, $data = null)
    {
        $GetMovementInfo = MovementHandler::GetMovementInfo($data);

        //N秒内只记录最后一次数据(禁止频繁操作数据库)
        if (!empty(WorldServer::$clientparam[$fd]['player']['movetime'])) {
            if (time() - WorldServer::$clientparam[$fd]['player']['movetime'] > config('MOVETIME')) {
                WorldServer::$clientparam[$fd]['player']['movetime'] = time();
                MovementHandler::UpdateSetMove($serv, $fd, $GetMovementInfo);
            }
        } else {
            WorldServer::$clientparam[$fd]['player']['movetime'] = time();
            MovementHandler::UpdateSetMove($serv, $fd, $GetMovementInfo);
        }
    }
}
