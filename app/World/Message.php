<?php
namespace app\World;

use app\Common\Srp6;
use app\World\Authchallenge;
use app\World\Character;
use app\World\OpCode;
use app\World\Packetmanager;
use app\World\Worldpacket;

class Message
{
    public function serverreceive($serv, $fd, $data)
    {
        if (!empty($data)) {

            WORLD_LOG("Receive: " . (new Srp6)->BigInteger($data, 256)->toHex(), 'info');

            $data = GetBytes($data);

            // WORLD_LOG("Receive: " . json_encode($data), 'info');

            $this->handlePacket($serv, $fd, $data, WorldServer::$clientparam[$fd]['state']);
        }
    }

    public function newConnect($serv, $fd)
    {
        $Authchallenge = new Authchallenge();

        $data = $Authchallenge->Authchallenge($fd);

        $this->serversend($serv, $fd, $data);
    }

    public function checkauth($fd, $data)
    {
        $Authchallenge = new Authchallenge();

        return $Authchallenge->AuthSession($fd, $data);
    }

    public function Offline($fd)
    {
        // $username      = Connection::getCache($fd, 'username');

        // $Account = new \app\Common\Account();
        // $Account->Offline($username);
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
                        $checkauth = $this->checkauth($fd, $data);

                        if ($checkauth['code'] == 2000) {
                            WORLD_LOG('[SMSG_ADDON_INFO] Client : ' . $fd, 'warning');

                            $data         = [0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x02, 0x01, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00];
                            $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_ADDON_INFO, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                            $packdata     = array_merge($encodeheader, $data);
                            $this->serversend($serv, $fd, $packdata);

                            WORLD_LOG('[SMSG_AUTH_RESPONSE] Client : ' . $fd, 'warning');
                            $AUTH_OK              = $Srp6->BigInteger(OpCode::AUTH_OK, 16)->toString();
                            $BillingTimeRemaining = PackInt(0, 32);
                            $BillingPlanFlags     = PackInt(0, 8);
                            $BillingTimeRested    = PackInt(0, 32);
                            $expansion            = [(int) $checkauth['data']['expansion']];
                            $data                 = array_merge([(int) $AUTH_OK], $BillingTimeRemaining, $BillingPlanFlags, $BillingTimeRested, $expansion);
                            $encodeheader         = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_AUTH_RESPONSE, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                            $packdata             = array_merge($encodeheader, $data);
                            $this->serversend($serv, $fd, $packdata);

                        } else {
                            $this->serversend($serv, $fd, $checkauth['data']);
                        }

                        break;

                    case 'CMSG_CHAR_ENUM':
                        WORLD_LOG('[SMSG_CHAR_ENUM] Client : ' . $fd, 'warning');

                        $result = Character::CharacterCharEnum($fd, $unpackdata['content']);
                        $this->serversend($serv, $fd, $result);

                        // $data = '01C30300000000000074657374000A0500010202080701660D00001202000066B6214652AAC6C5439C0542000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000D82600000400000000E88100001400000000000000000000000000D92600000700000000DA2600000800000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000AE9100001500000000000000000000000000000000000000000000000000000000000000000000000000000000';
                        // $data = $Srp6->BigInteger($data, 16)->toBytes();
                        // $data = GetBytes($data);

                        // $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_CHAR_ENUM, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        // $packdata     = array_merge($encodeheader, $data);

                        // $this->serversend($serv, $fd, $packdata);

                        break;

                    case 'CMSG_PING':
                        WORLD_LOG('[SMSG_PONG] Client : ' . $fd, 'warning');

                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_PONG, $unpackdata['content'], WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata = array_merge($encodeheader, $unpackdata['content']);
                        $this->serversend($serv, $fd, $packdata);
                        break;

                    case 'CMSG_CHAR_CREATE':
                        WORLD_LOG('[SMSG_CHAR_CREATE] Client : ' . $fd, 'warning');

                        $result = Character::CharacterCreate($fd, $unpackdata['content']);
                        $this->serversend($serv, $fd, $result);

                        break;

                    case 'CMSG_CHAR_DELETE':
                        WORLD_LOG('[SMSG_CHAR_DELETE] Client : ' . $fd, 'warning');

                        $result = Character::CharacterDelete($fd, $unpackdata['content']);
                        $this->serversend($serv, $fd, $result);

                        break;

                    case 'CMSG_PLAYER_LOGIN':
                        WORLD_LOG('[SMSG_MOTD] Client : ' . $fd, 'warning');
                        $data         = '0100000057656C636F6D6520746F20746865206964772D636F72652073657276657200';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_MOTD, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_TUTORIAL_FLAGS] Client : ' . $fd, 'warning');
                        $data         = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_TUTORIAL_FLAGS, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_LOGIN_VERIFY_WORLD] Client : ' . $fd, 'warning');
                        $data         = '000000005C2FD1448FD2CF44CD8C0A435131B740';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_LOGIN_VERIFY_WORLD, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_ACCOUNT_DATA_TIMES] Client : ' . $fd, 'warning');
                        $data         = '0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_ACCOUNT_DATA_TIMES, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

                        WORLD_LOG('[SMSG_INITIAL_SPELLS] Client : ' . $fd, 'warning');
                        $data         = '000C009D0200004945000061500000635000006B140000401E00009D0200004945000061500000635000006B140000401E00000C000000';
                        $data         = $Srp6->BigInteger($data, 16)->toBytes();
                        $data         = GetBytes($data);
                        $encodeheader = Packetmanager::Worldpacket_encrypter($fd, [OpCode::SMSG_INITIAL_SPELLS, $data, WorldServer::$clientparam[$fd]['sessionkey']]);
                        $packdata     = array_merge($encodeheader, $data);
                        $this->serversend($serv, $fd, $packdata);

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
        // WORLD_LOG("Send: " . json_encode($data), 'info');
        WORLD_LOG("Send: " . (new Srp6)->BigInteger(ToStr($data), 256)->toHex(), 'info');
        $serv->send($fd, ToStr($data));
    }
}
