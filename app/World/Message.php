<?php
namespace app\World;

use app\Common\Srp6;
use app\World\Addon\AddonHandler;
use app\World\Challenge\Authchallenge;
use app\World\Challenge\AuthResponse;
use app\World\Clientstate;
use app\World\Packet\Packetmanager;
use app\World\Packet\Worldpacket;
use app\World\Reflection;
use app\World\WorldServer;

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
            case Clientstate::ClientLogonChallenge:
                $opcode_value = Worldpacket::Unpackdata($data);
                $opcode       = Worldpacket::getopcode($opcode_value, $fd);

                if ($opcode == 'CMSG_AUTH_SESSION') {
                    $checkauth = Authchallenge::AuthSession($fd, $data);

                    if ($checkauth['code'] == 2000) {

                        $this->serversend($serv, $fd, AddonHandler::LoadAddonHandler($serv, $fd));
                        $this->serversend($serv, $fd, AuthResponse::LoadAuthResponse($serv, $fd, $checkauth));

                        WorldServer::$clientparam[$fd]['state'] = Clientstate::Authenticated;
                    } else {
                        $this->serversend($serv, $fd, $checkauth['data']);
                    }
                } else {
                    WorldServer::$clientparam[$fd]['state'] = Clientstate::Init;
                    WORLD_LOG('Unknown Clientstate and opcode: ' . $state . '_' . $opcode . ' Client : ' . $fd, 'warning');
                }

                break;

            case Clientstate::Authenticated:
                $sessionkey = WorldServer::$clientparam[$fd]['sessionkey'];

                for ($i = 0; $i < 40; $i++) {
                    // $unpackdata = Worldpacket::decrypter($data, $sessionkey);
                    $unpackdata = Packetmanager::Worldpacket_decrypter($fd, [$data, $sessionkey]);
                    $opcode     = Worldpacket::getopcode($unpackdata['opcode'], $fd);
                    if ($opcode) {
                        break;
                    }
                }

                Reflection::LoadClass($opcode, $serv, $fd, $unpackdata['content']);

                break;

            default:
                WORLD_LOG('Unknown Clientstate: ' . $state . ' Client : ' . $fd, 'warning');
                break;
        }
    }

    public static function serversend($serv, $fd, $data = null)
    {
        if (env('MSG_DEBUG', false)) {
            WORLD_LOG("Send: " . (new Srp6)->BigInteger(ToStr($data), 256)->toHex(), 'info');
        }

        $serv->send($fd, ToStr($data));
    }
}
