<?php
namespace app\World\Login;

use app\Common\Srp6;
use app\World\Object\PlayerObject;
use app\World\OpCode;
use app\World\Player\PlayerManager;
use app\World\WorldServer;
use core\query\DB;

/**
 *  角色登录
 */
class PlayerLogin
{
    //所选角色信息
    public static function PlayerInit($serv, $fd, $data = null)
    {
        $Srp6 = new Srp6();

        WorldServer::$clientparam[$fd]['player']['guid'] = $guid = HexToDecimal($Srp6->Littleendian($Srp6->BigInteger(ToStr($data), 256)->toHex())->toHex());

        WORLD_LOG('Character GUID : ' . $guid . ' enters the game world  Client : ' . $fd, 'warning');
    }

    //人物地图
    public static function LoadLoginVerifyWorld($serv, $fd, $data = null)
    {
        self::PlayerInit($serv, $fd, $data); //初始化

        WORLD_LOG('[SMSG_LOGIN_VERIFY_WORLD] Client : ' . $fd, 'warning');

        $where = [
            'guid' => WorldServer::$clientparam[$fd]['player']['guid'],
        ];

        $characters = DB::table('characters', 'characters')->where($where)->find();
        $packdata   = pack('Vf4',
            $characters['map'],
            $characters['position_x'],
            $characters['position_y'],
            $characters['position_z'],
            $characters['orientation']);

        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_LOGIN_VERIFY_WORLD, $packdata];
    }

    //帐户数据时间
    public static function LoadAccountDataTimes($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_ACCOUNT_DATA_TIMES] Client : ' . $fd, 'warning');

        $packdata = '';
        for ($i = 0; $i < 128; $i++) {
            $packdata .= pack('c', 0);
        }
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_ACCOUNT_DATA_TIMES, $packdata];
    }

    //功能系统状态
    public static function LoadFeatureSystemStatus($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_FEATURE_SYSTEM_STATUS] Client : ' . $fd, 'warning');

        $packdata = pack('c2', 2, 0);
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_FEATURE_SYSTEM_STATUS, $packdata];
    }

    //欢迎语
    public static function LoadMotd($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_MOTD] Client : ' . $fd, 'warning');

        $motd = env('MOTD', 'Welcome to the wpcore server');

        $packdata = pack('VZ*', 1, $motd);
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_MOTD, $packdata];

    }

    //教程标志
    public static function LoadTutorialFlags($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_TUTORIAL_FLAGS] Client : ' . $fd, 'warning');

        $packdata = '';
        for ($i = 0; $i < 32; $i++) {
            $packdata .= pack('c', 255);
        }
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_TUTORIAL_FLAGS, $packdata];
    }

    //初始法术
    public static function LoadInitialSpells($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_INITIAL_SPELLS] Client : ' . $fd, 'warning');

        $field = ['character_spell.*'];

        $where = [
            'characters.guid' => WorldServer::$clientparam[$fd]['player']['guid'],
        ];

        $join = [
            ['character_spell', 'character_spell.guid = characters.guid', 'inner'],
        ];

        $character_spell = DB::table('characters', 'characters')->field($field)->join($join)->where($where)->select();

        $packdata  = '';
        $spall_len = count($character_spell);
        $packdata .= pack('cv', 0, $spall_len);

        foreach ($character_spell as $k => $v) {
            $packdata .= pack('v2', $v['spell'], 0);
        }

        $packdata .= pack('v2', $spall_len, 0);
        $packdata = GetBytes($packdata);

        //TODO 冷却时间

        return [OpCode::SMSG_INITIAL_SPELLS, $packdata];
    }

    //服务器分隔
    public static function LoadPelamSplit($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_REALM_SPLIT] Client : ' . $fd, 'warning');

        $split_date = "01/01/01";
        $packdata   = pack('VVZ*', $data, 0, $split_date);
        $packdata   = GetBytes($packdata);

        return [OpCode::SMSG_REALM_SPLIT, $packdata];
    }

    //设置服务器时间流速
    public static function SetTimeSpeed($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_LOGIN_SETTIMESPEED] Client : ' . $fd, 'warning');

        $timedata = ((date('Y') - 100) << 24 | date('m') << 20 | (date('d') - 1) << 14 | date("w") << 11 | (date("H") - 3) << 6 | date("i"));
        $packdata = pack('f2', $timedata, env('GAME_SPEED', 0.01666667));
        $packdata = GetBytes($packdata);

        return [OpCode::SMSG_LOGIN_SETTIMESPEED, $packdata];
    }

    //下一个计数器时间同步
    public static function LoadTimeSyncReq($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_TIME_SYNC_REQ] Client : ' . $fd, 'warning');

        if (isset(WorldServer::$clientparam[$fd]['player']['TimeSyncNextCounter'])) {
            $TimeSyncNextCounter = WorldServer::$clientparam[$fd]['player']['TimeSyncNextCounter'];
        } else {
            $TimeSyncNextCounter = 0;
        }

        $packdata = pack('V', $TimeSyncNextCounter);
        $packdata = GetBytes($packdata);

        $TimeSyncNextCounter++;
        WorldServer::$clientparam[$fd]['player']['TimeSyncNextCounter'] = $TimeSyncNextCounter;

        return [OpCode::SMSG_TIME_SYNC_REQ, $packdata];
    }

    //加载玩家对象
    public static function LoginObject($serv, $fd, $data = null)
    {
        WORLD_LOG('[SMSG_UPDATE_OBJECT] Client : ' . $fd, 'warning');

        $characters = PlayerManager::FindPlayer(WorldServer::$clientparam[$fd]['player']['guid']);

        if ($characters) {
            $packdata = (new PlayerObject)->LoadPlayerObject($characters);
            $packdata = GetBytes($packdata);

        } else {
            $packdata = [0, 0, 0, 0];
        }

        return [OpCode::SMSG_UPDATE_OBJECT, $packdata];
    }
}
