<?php
namespace app\Auth;

/**
 *     Server status
 *     服务器状态
 */
class Clientstate
{
    const CONNECTION_TYPE_SOCKET    = 1;
    const CONNECTION_TYPE_WEBSOCKET = 2;

    const File_TYPE_FD   = 1;
    const File_TYPE_USER = 2;

    const MESSAGE_END_FLAG = "";

    const Init                 = 0;
    const ClientLogonChallenge = 1;
    const ServerLogonChallenge = 2;
    const ClientLogonProof     = 3;
    const ServerLogonProof     = 4;
    const Authenticated        = 5;
    const Disconnected         = 6;

    const WOW_SUCCESS                              = '0x00'; //成功
    const WOW_FAIL                                 = '0x01'; //无法连接
    const WOW_FAIL_BANNED                          = '0x03'; //账户被冻结
    const WOW_FAIL_UNKNOWN_ACCOUNT                 = '0x04'; //账户不存在或密码错误
    const WOW_FAIL_INCORRECT_PASSWORD              = '0x05'; //密码错误
    const WOW_FAIL_ALREADY_ONLINE                  = '0x06'; //账户已经在线
    const WOW_FAIL_NO_TIME                         = '0x07'; //预付费时间已经用完
    const WOW_FAIL_DB_BUSY                         = '0x08'; //现在无法登陆
    const WOW_FAIL_VERSION_INVALID                 = '0x09'; //无法验证游戏版本,文件可能损坏
    const WOW_FAIL_VERSION_UPDATE                  = '0x0A'; //正在下载
    const WOW_FAIL_SUSPENDED                       = '0x0C'; //账户已经被暂时冻结
    const WOW_SUCCESS_SURVEY                       = '0x0E'; //连接成功,刷新服务器
    const WOW_FAIL_PARENTCONTROL                   = '0x0F'; //账户被账户冷冻器锁定
    const WOW_FAIL_LOCKED_ENFORCED                 = '0x10'; //用户自己锁定账户
    const WOW_FAIL_TRIAL_ENDED                     = '0x11'; //试玩账户过期
    const WOW_FAIL_USE_BATTLENET                   = '0x12'; //账户和战网关联,需使用战网账户登录
    const WOW_FAIL_CHARGEBACK                      = '0x16'; //账户因费用问题被暂停使用
    const WOW_FAIL_INTERNET_GAME_ROOM_WITHOUT_BNET = '0x17'; //如果使用网吧游戏时间登录,需与战网账户合并
    const WOW_FAIL_GAME_ACCOUNT_LOCKED             = '0x18'; //账户被暂时禁用
    const WOW_FAIL_UNLOCKABLE_LOCK                 = '0x19'; //账户被锁定,但可以解锁
    const WOW_FAIL_CONVERSION_REQUIRED             = '0x20'; //必须使用战网账户登录
    const WOW_FAIL_DISCONNECTED                    = '0xFF'; //从服务器端开
}
