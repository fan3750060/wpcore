<?php
namespace app\common;

/**
 * 	Server status
 * 	服务器状态
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
}