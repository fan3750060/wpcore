<?php
namespace app\World;

/**
 * OpCode 操作码
 */
class OpCode
{
    const AUTH_OK                     = 0x0C;
    const AUTH_FAILED                 = 0x0D;
    const AUTH_REJECT                 = 0x0E;
    const AUTH_BAD_SERVER_PROOF       = 0x0F;
    const AUTH_UNAVAILABLE            = 0x10;
    const AUTH_SYSTEM_ERROR           = 0x11;
    const AUTH_BILLING_ERROR          = 0x12;
    const AUTH_BILLING_EXPIRED        = 0x13;
    const AUTH_VERSION_MISMATCH       = 0x14;
    const AUTH_UNKNOWN_ACCOUNT        = 0x15;
    const AUTH_INCORRECT_PASSWORD     = 0x16;
    const AUTH_SESSION_EXPIRED        = 0x17;
    const AUTH_SERVER_SHUTTING_DOWN   = 0x18;
    const AUTH_ALREADY_LOGGING_IN     = 0x19;
    const AUTH_LOGIN_SERVER_NOT_FOUND = 0x1A;
    const AUTH_WAIT_QUEUE             = 0x1B;
    const AUTH_BANNED                 = 0x1C;
    const AUTH_ALREADY_ONLINE         = 0x1D;
    const AUTH_NO_TIME                = 0x1E;
    const AUTH_DB_BUSY                = 0x1F;
    const AUTH_SUSPENDED              = 0x20;
    const AUTH_PARENTAL_CONTROL       = 0x21;

    const CMSG_CHAR_CREATE = '0x036';
    const CMSG_CHAR_ENUM   = '0x037';
    const CMSG_CHAR_DELETE = '0x038';
    const SMSG_CHAR_CREATE = '0x03A';
    const SMSG_CHAR_ENUM   = '0x03B';
    const SMSG_CHAR_DELETE = '0x03C';

    const CMSG_PLAYER_LOGIN     = '0x03D';
    const SMSG_NEW_WORLD        = '0x03E';
    const SMSG_TRANSFER_PENDING = '0x03F';
    const SMSG_TRANSFER_ABORTED = '0x040';

    const CMSG_LOGOUT_REQUEST    = '0x04B';
    const SMSG_LOGOUT_RESPONSE   = '0x04C';
    const SMSG_LOGOUT_COMPLETE   = '0x04D';
    const CMSG_LOGOUT_CANCEL     = '0x04E';
    const SMSG_LOGOUT_CANCEL_ACK = '0x04F';

    const CMSG_NAME_QUERY          = '0x050';
    const SMSG_NAME_QUERY_RESPONSE = '0x051';

    const CMSG_ITEM_QUERY_SINGLE        = '0x056';
    const CMSG_ITEM_QUERY_MULTIPLE      = '0x057';
    const SMSG_ITEM_QUERY_SINGLE_RESP   = '0x058';
    const SMSG_ITEM_QUERY_MULTIPLE_RESP = '0x059';

    const CMSG_MESSAGECHAT    = '0x095';
    const SMSG_MESSAGECHAT    = '0x096';
    const CMSG_JOIN_CHANNEL   = '0x097';
    const CMSG_LEAVE_CHANNEL  = '0x098';
    const SMSG_CHANNEL_NOTIFY = '0x099';

    const SMSG_UPDATE_OBJECT  = '0x0A9';
    const SMSG_DESTROY_OBJECT = '0x0AA';

    const MSG_MOVE_START_FORWARD      = '0x0B5';
    const MSG_MOVE_START_BACKWARD     = '0x0B6';
    const MSG_MOVE_STOP               = '0x0B7';
    const MSG_MOVE_START_STRAFE_LEFT  = '0x0B8';
    const MSG_MOVE_START_STRAFE_RIGHT = '0x0B9';
    const MSG_MOVE_STOP_STRAFE        = '0x0BA';
    const MSG_MOVE_JUMP               = '0x0BB';
    const MSG_MOVE_START_TURN_LEFT    = '0x0BC';
    const MSG_MOVE_START_TURN_RIGHT   = '0x0BD';
    const MSG_MOVE_STOP_TURN          = '0x0BE';
    const MSG_MOVE_START_PITCH_UP     = '0x0BF';
    const MSG_MOVE_START_PITCH_DOWN   = '0x0C0';
    const MSG_MOVE_STOP_PITCH         = '0x0C1';
    const MSG_MOVE_SET_RUN_MODE       = '0x0C2';
    const MSG_MOVE_SET_WALK_MODE      = '0x0C3';
    const MSG_MOVE_FALL_LAND          = '0x0C9';
    const MSG_MOVE_SET_FACING         = '0x0DA';

    const MSG_MOVE_WORLDPORT_ACK = '0x0DC';

    const MSG_MOVE_HEARTBEAT = '0x0EE';

    const SMSG_TUTORIAL_FLAGS = '0x0FD';

    const CMSG_CANCEL_TRADE = '0x11C';

    const SMSG_INITIAL_SPELLS = '0x12A';

    const CMSG_QUERY_TIME          = '0x1CE';
    const SMSG_QUERY_TIME_RESPONSE = '0x1CF';

    const CMSG_PING = '0x1DC';
    const SMSG_PONG = '0x1DD';

    const SMSG_AUTH_CHALLENGE = '0x1EC';
    const CMSG_AUTH_SESSION   = '0x1ED';
    const SMSG_AUTH_RESPONSE  = '0x1EE';

    const CMSG_ZONEUPDATE = '0x1F4';

    const SMSG_COMPRESSED_UPDATE_OBJECT = '0x1F6';

    const MSG_LOOKING_FOR_GROUP = '0x1FF';

    const SMSG_ACCOUNT_DATA_MD5     = '0x209';
    const CMSG_REQUEST_ACCOUNT_DATA = '0x20A';
    const CMSG_UPDATE_ACCOUNT_DATA  = '0x20B';
    const SMSG_UPDATE_ACCOUNT_DATA  = '0x20C';

    const CMSG_GMTICKET_GETTICKET = '0x211';
    const SMSG_GMTICKET_GETTICKET = '0x212';

    const SMSG_LOGIN_VERIFY_WORLD = '0x236';

    const CMSG_SET_ACTIVE_MOVER = '0x26A';

    const MSG_QUERY_NEXT_MAIL_TIME = '0x284';
}
