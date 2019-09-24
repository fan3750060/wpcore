<?php
namespace app\World\Character;
use app\World\WorldServer;
use app\Common\Srp6;
use app\World\Character\CharacterHandler;
use app\World\Packet\Packetmanager;
use app\World\OpCode;

/**
 * 角色管理
 */
class Character
{
    const CHAR_LIST_RETRIEVING                                   = '0x2B';
    const CHAR_LIST_RETRIEVED                                    = '0x2C';
    const CHAR_LIST_FAILED                                       = '0x2D';
    const CHAR_CREATE_IN_PROGRESS                                = '0x2E';
    const CHAR_CREATE_SUCCESS                                    = '0x2F';
    const CHAR_CREATE_ERROR                                      = '0x30';
    const CHAR_CREATE_FAILED                                     = '0x31';
    const CHAR_CREATE_NAME_IN_USE                                = '0x32';
    const CHAR_CREATE_DISABLED                                   = '0x33';
    const CHAR_CREATE_PVP_TEAMS_VIOLATION                        = '0x34';
    const CHAR_CREATE_SERVER_LIMIT                               = '0x35';
    const CHAR_CREATE_ACCOUNT_LIMIT                              = '0x36';
    const CHAR_CREATE_SERVER_QUEUE                               = '0x37';
    const CHAR_CREATE_ONLY_EXISTING                              = '0x38';
    const CHAR_CREATE_EXPANSION                                  = '0x39';
    const CHAR_DELETE_IN_PROGRESS                                = '0x3A';
    const CHAR_DELETE_SUCCESS                                    = '0x3B';
    const CHAR_DELETE_FAILED                                     = '0x3C';
    const CHAR_DELETE_FAILED_LOCKED_FOR_TRANSFER                 = '0x3D';
    const CHAR_DELETE_FAILED_GUILD_LEADER                        = '0x3E';
    const CHAR_DELETE_FAILED_ARENA_CAPTAIN                       = '0x3F';
    const CHAR_NAME_SUCCESS                                      = '0x4A';
    const CHAR_NAME_FAILURE                                      = '0x4B';
    const CHAR_NAME_NO_NAME                                      = '0x4C';
    const CHAR_NAME_TOO_SHORT                                    = '0x4D';
    const CHAR_NAME_TOO_LONG                                     = '0x4E';
    const CHAR_NAME_INVALID_CHARACTER                            = '0x4F';
    const CHAR_NAME_MIXED_LANGUAGES                              = '0x50';
    const CHAR_NAME_PROFANE                                      = '0x51';
    const CHAR_NAME_RESERVED                                     = '0x52';
    const CHAR_NAME_INVALID_APOSTROPHE                           = '0x53';
    const CHAR_NAME_MULTIPLE_APOSTROPHES                         = '0x54';
    const CHAR_NAME_THREE_CONSECUTIVE                            = '0x55';
    const CHAR_NAME_INVALID_SPACE                                = '0x56';
    const CHAR_NAME_CONSECUTIVE_SPACES                           = '0x57';
    const CHAR_NAME_RUSSIAN_CONSECUTIVE_SILENT_CHARACTERS        = '0x58';
    const CHAR_NAME_RUSSIAN_SILENT_CHARACTER_AT_BEGINNING_OR_END = '0x59';
    const CHAR_NAME_DECLENSION_DOESNT_MATCH_BASE_NAME            = '0x5A';

    public static $equipment = [
        'HEAD'      => 0,
        'NECK'      => 1,
        'SHOULDERS' => 2,
        'BODY'      => 3,
        'CHEST'     => 4,
        'WAIST'     => 5,
        'LEGS'      => 6,
        'FEET'      => 7,
        'WRISTS'    => 8,
        'HANDS'     => 9,
        'FINGER1'   => 10,
        'FINGER2'   => 11,
        'TRINKET1'  => 12,
        'TRINKET2'  => 13,
        'BACK'      => 14,
        'MAINHAND'  => 15,
        'OFFHAND'   => 16,
        'RANGED'    => 17,
        'TABARD'    => 18,
        'BAG1'      => 19,
    ];

    public static function CharacterCreate($serv, $fd, $data)
    {
        $result = [];
        $name   = [];
        foreach ($data as $k => $v) {
            if ($v != 0) {
                $name[] = $v;
            } else {
                break;
            }
        }
        $result['name'] = ToStr($name);
        $next_length    = count($name) + 1;

        $result['race'] = $data[$next_length];
        $next_length += 1;

        $result['class'] = $data[$next_length];
        $next_length += 1;

        $result['gender'] = $data[$next_length];
        $next_length += 1;

        $result['skin'] = $data[$next_length];
        $next_length += 1;

        $result['face'] = $data[$next_length];
        $next_length += 1;

        $result['hairStyle'] = $data[$next_length];
        $next_length += 1;

        $result['hairColor'] = $data[$next_length];
        $next_length += 1;

        $result['facialStyle'] = $data[$next_length];
        $next_length += 1;

        $result['account'] = WorldServer::$clientparam[$fd]['userinfo']['id'];

        if (CharacterHandler::rolenum($result) >= 10) {
            $packdata     = PackInt(HexToDecimal(self::CHAR_CREATE_SERVER_LIMIT), 32);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_CREATE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
            return $packdata;
        }

        if (CharacterHandler::create($result)) {
            WORLD_LOG('create role name:"' . $result['name'] . '" Client : ' . $fd, 'success');

            $packdata     = PackInt(HexToDecimal(self::CHAR_CREATE_SUCCESS), 32);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_CREATE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
        } else {
            WORLD_LOG('create role name:"' . $result['name'] . '" Client : ' . $fd, 'error');

            $packdata     = PackInt(HexToDecimal(self::CHAR_CREATE_ERROR), 32);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_CREATE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
        }

        return $packdata;
    }

    public static function CharacterDelete($serv, $fd, $data)
    {
        $Srp6 = new Srp6();
        $guid = HexToDecimal($Srp6->Littleendian($Srp6->BigInteger(ToStr($data), 256)->toHex())->toHex());

        if (CharacterHandler::delete($guid) !== false) {

            WORLD_LOG('delete role guid:"' . $guid . '" Client : ' . $fd, 'success');

            $packdata     = PackInt(HexToDecimal(self::CHAR_DELETE_SUCCESS), 32);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_DELETE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
        } else {
            $packdata     = PackInt(HexToDecimal(self::CHAR_DELETE_FAILED), 32);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_DELETE, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
        }

        return $packdata;
    }

    public  function CharacterCharEnum($serv, $fd, $data)
    {
        $Srp6 = new Srp6();

        $param            = [];
        $param['account'] = WorldServer::$clientparam[$fd]['userinfo']['id'];

        if ($result = CharacterHandler::CharEnum($param)) {
            $packdata = pack('c', count($result));

            //获取角色物品信息
            $guids = array_column($result, 'guid');

            $character_inventory = CharacterHandler::CharEnumItem($guids);

            foreach ($result as $k => $v) {
                $name     = $v['name'];
                $name_len = strlen($v['name']);
                $info     = pack("QZ*c9Vif3l2cl3",
                    $v['guid'],
                    $name,
                    $v['race'],
                    $v['class'],
                    $v['gender'],
                    $v['skin'],
                    $v['face'],
                    $v['hairStyle'],
                    $v['hairColor'],
                    $v['facialStyle'],
                    $v['level'],
                    $v['zone'],
                    $v['map'],
                    $v['position_x'],
                    $v['position_y'],
                    $v['position_z'],
                    $v['guildid'],
                    $v['playerFlags'],
                    $v['at_login'],
                    $v['entry'],
                    $v['pet_level'],
                    0
                );
                //装备信息: 物品显示id(displayid)-物品部位(slot)-附魔id(暂时为0)
                if (isset($character_inventory[$v['guid']])) {

                    $item_info = '';
                    foreach (self::$equipment as $k1 => $v1) {
                        $displayid     = isset($character_inventory[$v['guid']][$v1]['displayid']) ? $character_inventory[$v['guid']][$v1]['displayid'] : 0;
                        $InventoryType = isset($character_inventory[$v['guid']][$v1]['InventoryType']) ? $character_inventory[$v['guid']][$v1]['InventoryType'] : 0;

                        $item_info_tmp = pack("VcV", $displayid, $InventoryType, 0);

                        $item_info .= $item_info_tmp;
                    }

                    $info .= $item_info;
                } else {
                    //默认显示装备
                    $item_info = '';
                    foreach (self::$equipment as $k1 => $v1) {
                        if ($k1 == 'CHEST') {
                            $displayid     = 12683;
                            $InventoryType = 20;
                        } elseif ($k1 == 'MAINHAND') {
                            $displayid     = 40371;
                            $InventoryType = 17;
                        } else {
                            $displayid     = 0;
                            $InventoryType = 0;
                        }

                        $item_info_tmp = pack("VcV", $displayid, $InventoryType, 0);

                        $item_info .= $item_info_tmp;
                    }

                    $info .= $item_info;
                }

                $packdata .= $info;
            }

            $packdata     = GetBytes($packdata);
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_ENUM, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);

        } else {
            $packdata     = [0];
            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_ENUM, $packdata, WorldServer::$clientparam[$fd]['sessionkey']]);
            $packdata     = array_merge($encodeheader, $packdata);
        }

        return $packdata;
    }
}
