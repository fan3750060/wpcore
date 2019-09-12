<?php
namespace app\World;
use core\lib\Cache;
use app\World\Packetmanager;
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

    public static function CharacterCreate($fd, $data)
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

        $result['char_class'] = $data[$next_length];
        $next_length += 1;

        $result['gender'] = $data[$next_length];
        $next_length += 1;

        $result['skin'] = $data[$next_length];
        $next_length += 1;

        $result['face'] = $data[$next_length];
        $next_length += 1;

        $result['hair_style'] = $data[$next_length];
        $next_length += 1;

        $result['hair_color'] = $data[$next_length];
        $next_length += 1;

        $result['facial_hair'] = $data[$next_length];
        $next_length += 1;

        WORLD_LOG('create '.$result['name'].' Client : ' . $fd, 'success');

        $packdata = PackInt(HexToDecimal(self::CHAR_CREATE_SUCCESS), 32);
        $encodeheader = Packetmanager::Worldpacket_encrypter($fd,[OpCode::SMSG_CHAR_CREATE,$packdata,WorldServer::$clientparam[$fd]['sessionkey']]);
        $packdata     = array_merge($encodeheader, $packdata);
        return $packdata;
    }
}
