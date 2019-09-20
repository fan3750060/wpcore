<?php
namespace app\World;

use app\Common\Srp6;
use app\World\Addon\AddonHandler;
use app\World\Challenge\Authchallenge;
use app\World\Challenge\AuthResponse;
use app\World\Character\Character;
use app\World\Login\PlayerLogin;
use app\World\OpCode;
use app\World\Packet\Packetmanager;
use app\World\Packet\Worldpacket;
use app\World\Ping\PongHandler;

class Message
{
    public function serverreceive($serv, $fd, $data)
    {
        if (!empty($data)) {

            if (env('MSG_DEBUG', false)) {
                WORLD_LOG("Receive: " . (new Srp6)->BigInteger($data, 256)->toHex(), 'info');
            }

            $data = GetBytes($data);

            $this->handlePacket($serv, $fd, $data, WorldServer::$clientparam[$fd]['state']);
        }
    }

    public function newConnect($serv, $fd)
    {
        $this->serversend($serv, $fd, Authchallenge::Challenge($fd));
    }

    public function Offline($fd)
    {

    }

    public function handlePacket($serv, $fd, $data, $state)
    {
        $Srp6 = new Srp6();

        switch ($state) {
            case 1:
                $sessionkey = isset(WorldServer::$clientparam[$fd]['sessionkey']) ? WorldServer::$clientparam[$fd]['sessionkey'] : '';

                if (!$sessionkey) {
                    $opcode_value = Worldpacket::Unpackdata($data);
                    $opcode       = Worldpacket::getopcode($opcode_value, $fd);
                } else {
                    for ($i = 0; $i < 40; $i++) {
                        $unpackdata = Worldpacket::decrypter($data, $sessionkey);
                        $opcode     = Worldpacket::getopcode($unpackdata['opcode'], $fd);
                        if ($opcode) {
                            break;
                        }
                    }
                }

                switch ($opcode) {
                    case 'CMSG_AUTH_SESSION':
                        $checkauth = Authchallenge::AuthSession($fd, $data);

                        if ($checkauth['code'] == 2000) {
                            $this->serversend($serv, $fd, AddonHandler::LoadAddonHandler($serv, $fd));
                            $this->serversend($serv, $fd, AuthResponse::LoadAuthResponse($serv, $fd, $checkauth));
                        } else {
                            $this->serversend($serv, $fd, $checkauth['data']);
                        }

                        break;

                    case 'CMSG_CHAR_ENUM':
                        $this->serversend($serv, $fd, Character::CharacterCharEnum($fd, $unpackdata['content']));
                        break;

                    case 'CMSG_PING':
                        $this->serversend($serv, $fd, PongHandler::LoadPongHandler($serv, $fd, $unpackdata['content']));
                        break;

                    case 'CMSG_CHAR_CREATE':
                        $this->serversend($serv, $fd, Character::CharacterCreate($fd, $unpackdata['content']));
                        break;

                    case 'CMSG_CHAR_DELETE':
                        $this->serversend($serv, $fd, Character::CharacterDelete($fd, $unpackdata['content']));
                        break;

                    case 'CMSG_REALM_SPLIT':
                        $this->serversend($serv, $fd, PlayerLogin::loadPelamSplit($serv, $fd, $unpackdata['content']));
                        break;

                    case 'CMSG_SET_ACTIVE_VOICE_CHANNEL':
                        $this->serversend($serv, $fd, PlayerLogin::loadFeatureSystemStatus($serv, $fd));
                        break;

                    case 'CMSG_VOICE_SESSION_ENABLE':
                        $this->serversend($serv, $fd, PlayerLogin::loadFeatureSystemStatus($serv, $fd));
                        break;

                    case 'CMSG_PLAYER_LOGIN':
                        PlayerLogin::PlayerInit($serv, $fd, $unpackdata['content']);

                        $this->serversend($serv, $fd, PlayerLogin::LoadLoginVerifyWorld($serv, $fd));

                        $this->serversend($serv, $fd, PlayerLogin::LoadAccountDataTimes($serv, $fd));

                        $this->serversend($serv, $fd, PlayerLogin::loadFeatureSystemStatus($serv, $fd));

                        $this->serversend($serv, $fd, PlayerLogin::LoadMotd($serv, $fd));

                        $this->serversend($serv, $fd, PlayerLogin::LoadTutorialFlags($serv, $fd));

                        $this->serversend($serv, $fd, PlayerLogin::LoadInitialSpells($serv, $fd));

                        WORLD_LOG('[SMSG_UPDATE_OBJECT] Client : ' . $fd, 'warning');
                        $data         = '010000000003037A03047100000000003216795D5C2FD1448FD2CF44CD8C0A435131B74000000000000020400000E04000009040711C9740000020400000E04000009040E00F49400000000031170040141D40000000000000000000000000C00300F80004000001000090010000000000000000000000000400040004000400040004000400040004000400040004000400040004000400040000000000FCFFFFFFFF0000000000000000000000000000000000000000000000000000000000C0BD000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000003001800000000000000000000000000000020001040000000800000000000407A03000000000000190000000000803F0C000000000000000C0000000000000001000000050000000504000300000000022BC73E0000C03F39000000390000000F0000000F0000000C0000000700000002000000000000000C00000000000000040900080E0000020000000000000000000000003908000000000000000000007800000079000000000000000000000000000000000000000000000000000000000000002C080000000000000000000000000000000000000000000000000000000000007C03003908000040000000000000000000000000000000007D030078000000407E0300790000004000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000007B03002C080000400000000000000000000000000000000000000000000000000000000000000000A10200002C010000DC0000002C010000010000000100000000000000000000000000000000000000102700000100000000000000FFFFFFFF46000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_UPDATE_OBJECT, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_LOGIN_SETTIMESPEED] Client : ' . $fd, 'warning');
                        $data         = '53F2EF508A88883C';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_LOGIN_SETTIMESPEED, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_TIME_SYNC_REQ] Client : ' . $fd, 'warning');
                        $data         = '00000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_TIME_SYNC_REQ, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);
                        break;

                    case 'CMSG_NAME_QUERY':
                        WORLD_LOG('[SMSG_NAME_QUERY_RESPONSE] Client : ' . $fd, 'warning');
                        $data         = '7A03000000000000E69FA5E5A89C000005000000000000000400000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_NAME_QUERY_RESPONSE, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);
                        break;

                    case 'CMSG_QUERY_TIME':

                        WORLD_LOG('[SMSG_QUERY_TIME_RESPONSE] Client : ' . $fd, 'warning');
                        $data         = '3216795D00000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_QUERY_TIME_RESPONSE, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);
                        break;

                    default:
                        WORLD_LOG('Unknown opcode: ' . $opcode . ' Client : ' . $fd, 'warning');

                        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_PONG, [0x00, 0x00, 0x00, 0x00], WorldServer::$clientparam[$fd]['sessionkey']]);
                        // $packdata = array_merge($encodeheader, [0x00, 0x00, 0x00, 0x00]);
                        // $this->serversend($serv, $fd, $packdata);
                        break;
                }

                break;
        }
    }

    public function serversend($serv, $fd, $data = null)
    {
        if (env('MSG_DEBUG', false)) {
            WORLD_LOG("Send: " . (new Srp6)->BigInteger(ToStr($data), 256)->toHex(), 'info');
        }

        $serv->send($fd, ToStr($data));
    }
}
