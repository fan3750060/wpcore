<?php
namespace app\World;

use app\Common\int_helper;
use app\Common\Srp6;
use app\World\Connection;
use app\World\OpCode;
use app\World\Worldpackt;

/**
 * 世界服务器鉴权
 */
class Authchallenge
{
    /**
     * [Authchallenge 发起验证 SMSG_AUTH_CHALLENGE]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-11
     * ------------------------------------------------------------------------------
     */
    public function Authchallenge($fd)
    {
        // //要求客户端鉴权
        // $data = [0x00, 0x2a, 0xec, 0x01, 0x01, 0x00, 0x00, 0x00, 0x8a, 0xd0, 0x07, 0x33, 0x37, 0x33, 0xe6, 0x9c, 0x11, 0xcd, 0x6b, 0x73,
        //     0x24, 0xfe, 0x8d, 0x6d, 0x2a, 0x53, 0xdf, 0x91, 0xcb, 0x15, 0x27, 0xeb, 0x02, 0x7d, 0x41, 0x26, 0x15, 0xd6, 0xd6, 0xc8, 0x05, 0x3b, 0x7b, 0xe2];
        // 00,2a,ec,01,01,00,00,00,43,e7,4c,c4,37,e6,0f,0d,51,5c,50,88,53,e7,7a,c1,b7,db,db,aa,51,0f137c3f0d2dbab10c0bf59fc2b2c6

        $cmd       = 0x00;
        $hardcoded = '0x01000000';

        $Srp6      = new Srp6();
        $hardcoded = $Srp6->BigInteger($hardcoded, 16)->toBytes();

        // 固定
        $seed  = '0xc44ce743';
        $seed1 = '0xaadbdbb7c17ae75388505c510d0fe637';
        $seed2 = '0xc6b2c29ff50b0cb1ba2d0d3f7c130f51';

        // 随机
        // $seed  = $Srp6->_random_number_helper(4)->toHex();
        // $seed1 = $Srp6->_random_number_helper(16)->toHex();
        // $seed2 = $Srp6->_random_number_helper(16)->toHex();

        $seed  = $Srp6->Littleendian($Srp6->BigInteger($seed, 16)->toHex())->toBytes();
        $seed1 = $Srp6->Littleendian($Srp6->BigInteger($seed1, 16)->toHex())->toBytes();
        $seed2 = $Srp6->Littleendian($Srp6->BigInteger($seed2, 16)->toHex())->toBytes();

        $data = $hardcoded . $seed . $seed1 . $seed2;

        $ThePackt = Worldpackt::Packtdata(OpCode::SMSG_AUTH_CHALLENGE, $data);
        $packdata = $Srp6->BigInteger($ThePackt, 16)->toBytes();
        $packdata = int_helper::getBytes($packdata);

        $data = array_merge([$cmd], $packdata);

        // 存储
        $connectionCls = new Connection();
        $connectionCls->saveConnector($fd, ['serverseed' => $seed]);

        return $data;
    }

    /**
     * [AuthSession 解包客户端信息并验证 CMSG_AUTH_SESSION]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-16
     * ------------------------------------------------------------------------------
     * @param   [type]          $data [description]
     */
    public function AuthSession($fd, $data)
    {
        $Srp6 = new Srp6();

        $packdata = Worldpackt::Unpackdata($data);
        $content  = int_helper::getBytes($packdata[2]);

        $packdata = [];

        // 版本
        $build             = array_slice($content, 0, 2);
        $build_0           = $Srp6->BigInteger(int_helper::toStr([$build[1]]), 256)->toHex();
        $build_1           = $Srp6->BigInteger(int_helper::toStr([$build[0]]), 256)->toHex();
        $packdata['build'] = int_helper::HexToDecimal($build_0 . $build_1);

        // 账户名称
        $account_name_bytes = array_slice($content, 8);
        $account_name       = [];
        foreach ($account_name_bytes as $k => $v) {
            if ($v != 0) {
                $account_name[] = $v;
            } else {
                break;
            }
        }
        $packdata['account_name'] = int_helper::toStr($account_name);
        $account_name             = $Srp6->BigInteger($packdata['account_name'], 256)->toBytes();
        $next_length = strlen($account_name)+8+5;

        //client_seed
        $client_seed             = array_slice($content, $next_length, 4);
        $packdata['client_seed'] = $Srp6->BigInteger(int_helper::toStr($client_seed), 256)->toHex();
        $client_seed_Bytes       = $Srp6->BigInteger(int_helper::toStr($client_seed), 256)->toBytes();
        $next_length = $next_length+4+4+4;

        //realm_id
        $realm_id             = array_slice($content, $next_length, 4);
        $packdata['realm_id'] =  $Srp6->BigInteger(strrev(int_helper::toStr($realm_id)), 256)->toString();
        $next_length = $next_length+4+4+4;

        //client_hash
        $client_hash             = array_slice($content, $next_length, 20);
        $packdata['client_hash'] = $Srp6->BigInteger(int_helper::toStr($client_hash), 256)->toHex();
        $client_hash_Bytes       = $Srp6->BigInteger(int_helper::toStr($client_hash), 256)->toBytes();

        // 查看账户
        $Account  = new \app\Common\Account();
        $userinfo = $Account->get_account($packdata['account_name']);

        if (!$userinfo) {

            WORLD_LOG('unknown account: ' . $account_name, 'error');

            // 用户不存在
            $AUTH_UNKNOWN_ACCOUNT = $Srp6->BigInteger(OpCode::AUTH_UNKNOWN_ACCOUNT, 16)->toString();
            $data                 = [$AUTH_UNKNOWN_ACCOUNT];
            $data                 = int_helper::toStr($data);
            $ThePackt             = Worldpackt::Packtdata(OpCode::SMSG_AUTH_RESPONSE, $data);

            $packdata = $Srp6->BigInteger($ThePackt, 16)->toBytes();
            $packdata = int_helper::getBytes($packdata);

            return $packdata;
        }

        //K
        $sessionkey = $Srp6->BigInteger($userinfo['sessionkey'], 16)->toBytes();

        //计算Hash
        $connectionCls = new Connection();
        $serverseed    = $connectionCls->getCache($fd, 'serverseed');

        $server_hash       = sha1($account_name . int_helper::toStr([0x00, 0x00, 0x00, 0x00]) . $client_seed_Bytes . $serverseed . $sessionkey);
        $server_hash_Bytes = $Srp6->BigInteger($server_hash, 16)->toBytes();

        //验证
        if ($Srp6->BigInteger($server_hash, 16)->toHex() != $packdata['client_hash']) {
            WORLD_LOG('Verification failed: ' . $account_name, 'error');
            WORLD_LOG('server_hash: ' . $Srp6->BigInteger($server_hash, 16)->toHex(), 'error');
            WORLD_LOG('client_hash: ' . $packdata['client_hash'], 'error');

            // 鉴权失败
            return [0, 0, 4];
        }

        WORLD_LOG('Unpack: ' . json_encode($packdata), 'info');
        WORLD_LOG('server_hash: ' . $Srp6->BigInteger($server_hash, 16)->toHex(), 'warning');
        WORLD_LOG('client_hash: ' . $packdata['client_hash'], 'warning');
        WORLD_LOG('AUTH_OK: Successful verification', 'success');

        // 鉴权完成
        $AUTH_OK  = $Srp6->BigInteger(OpCode::AUTH_OK, 16)->toString();
        $data     = [$AUTH_OK, 0, 0, 0, 2];
        $data     = int_helper::toStr($data);
        $ThePackt = Worldpackt::Packtdata(OpCode::SMSG_AUTH_RESPONSE, $data);

        $packdata = $Srp6->BigInteger($ThePackt, 16)->toBytes();
        $packdata = int_helper::getBytes($packdata);
        // $packdata = array_merge([0x01], $packdata);

        return $packdata;
    }
}
