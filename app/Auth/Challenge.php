<?php
namespace app\Auth;

use app\Common\int_helper;
use app\Common\Math_BigInteger;
use app\Common\Srp6;
use core\lib\Cache;

/**
 *
 */
class Challenge
{
    public $srpdata;
    public $seesionkey;

    /**
     * [getinfo_ClientLogonChallenge 解析客户端登录信息]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-01
     * ------------------------------------------------------------------------------
     * @param   [type]          $data [description]
     * @return  [type]                [description]
     */
    public function getinfo_ClientLogonChallenge($data)
    {
        $info                  = [];
        $info['cmd']           = $data[0]; //命令
        $info['error']         = $data[1]; //错误
        $info['size']          = int_helper::uInt16(int_helper::toStr(array_slice($data, 2, 2)));
        $info['gamename']      = strrev(int_helper::toStr(array_slice($data, 4, 3)));
        $info['version']       = $data[8] . '.' . $data[9] . '.' . $data[10];
        $info['build']         = int_helper::uInt16(int_helper::toStr(array_slice($data, 11, 2)));
        $info['platform']      = strrev(int_helper::toStr(array_slice($data, 13, 3)));
        $info['os']            = strrev(int_helper::toStr(array_slice($data, 17, 3)));
        $info['country']       = strrev(int_helper::toStr(array_slice($data, 21, 4)));
        $info['timezone_bias'] = array_slice($data, 25, 4);
        $info['ip']            = array_slice($data, 29, 4);
        $info['ip']            = implode('.', $info['ip']);

        $info['user_lenth'] = $data[33]; //用户名长度
        $info['username']   = array_slice($data, 34, $info['user_lenth']); //截取用户名
        $info['username']   = int_helper::toStr($info['username']);

        AUTH_LOG('Unpack: ' . json_encode($info), 'info');
        return $info;
    }

    /**
     * [getAuthSrp 开始验证]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-20
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getAuthSrp($data = null)
    {
        $cmd   = 0x00;
        $error = 0x00;
        $unk2  = 0;
        $g_len = 1;
        $N_len = 32;
        $unk3  = [0x2A, 0xD5, 0x48, 0xCC, 0x9B, 0x9D, 0xA1, 0x99, 0xCC, 0x04, 0x7A, 0x60, 0x91, 0x15, 0x6C, 0x51];
        $unk4  = 0;

        // PHP实现
        $SRP = new Srp6();
        $SRP->authSrp6($data['username'], $data['sha_pass_hash']);
        $this->srpdata = $SRP->data;

        //储存缓存 TODO mysql
        Cache::drive()->set($data['username'], $this->srpdata, 100);

        $return_data   = [];
        $return_data[] = $cmd;
        $return_data[] = $error;
        $return_data[] = $unk2;

        foreach ($this->srpdata['B'] as $k => $v) {
            $return_data[] = $v;
        }

        $return_data[] = $g_len;
        $return_data[] = (int) $this->srpdata['g'];
        $return_data[] = $N_len;

        foreach ($this->srpdata['N'] as $k => $v) {
            $return_data[] = $v;
        }

        foreach ($this->srpdata['s_bytes'] as $k => $v) {
            $return_data[] = $v;
        }

        foreach ($unk3 as $k => $v) {
            $return_data[] = $v;
        }

        $return_data[] = $unk4;

        return $return_data;
    }

    /**
     * AuthServerLogonChallenge 验证数据]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function AuthServerLogonChallenge($data, $username)
    {
        //获取缓存 TODO mysql
        $this->srpdata = Cache::drive()->get($username);

        $A  = array_slice($data, 1, 32);
        $M1 = array_slice($data, 33, 20);

        $A  = int_helper::toStr($A);
        $M1 = int_helper::toStr($M1);
        $A  = new Math_BigInteger($A, 256);
        $M1 = new Math_BigInteger($M1, 256);
        $A  = $A->toHex();
        $M1 = $M1->toHex();

        $v = $this->srpdata['v'];
        $s = $this->srpdata['s'];
        $b = $this->srpdata['b'];
        $B = $this->srpdata['B'];
        $B = int_helper::toStr($B);
        $B = new Math_BigInteger($B, 256);
        $B = $B->toHex();

        $SRP = new Srp6();
        $SRP->configvs($v, $s, $b, $B, $username);
        $check = $SRP->getM($A, $M1);

        if ($check) {
            $M    = $SRP->M->toBytes();
            $M    = int_helper::getBytes($M);
            $data = [0x01, 0x00];
            foreach ($M as $k => $v) {
                $data[] = $v;
            }

            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x80;
            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x00;
            $data[] = 0x00;

            $this->seesionkey = $SRP->sessionkey->toHex();

        } else {
            $data = [0x00, 0x00, 0x04];
        }

        AUTH_LOG('Verify: ' . json_encode($data), 'info');

        return $data;
    }

    /**
     * [AuthServerSeesionKey 获取sessionkey]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-03
     * ------------------------------------------------------------------------------
     * @param   [type]          $username [description]
     */
    public function AuthServerSeesionKey($username)
    {
        return $this->seesionkey;
    }
}
