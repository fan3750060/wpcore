<?php
namespace app\Auth;

use app\Common\int_helper;

/**
 *
 */
class Challenge
{
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

        echolog('解包：' . json_encode($info), 'info');
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
        echolog('python core.py 0 ' . $data['username'] . ' ' . $data['sha_pass_hash']);

        // 借助python实现srp6验证(字符类型)
        $output = @shell_exec('python core.py 0 ' . $data['username'] . ' ' . $data['sha_pass_hash']);
        $data   = trim($output);
        $data   = substr($data, 1, -1);
        $data   = explode(',', $data);
        foreach ($data as $k => $v) {
            $data[$k] = (int) $v;
        }

        return $data;
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
        echolog('python core.py 1 ' . $username . ' ' . base64_encode(int_helper::toStr($data)));

        // 借助python实现srp6验证(字符类型)
        $output = @shell_exec('python core.py 1 ' . $username . ' ' . base64_encode(int_helper::toStr($data)));
        $data   = trim($output);
        $data   = substr($data, 1, -1);
        $data   = str_replace(' ', '', $data);
        $data   = explode(',', $data);

        foreach ($data as $k => $v) {
            $data[$k] = (int) $v;
        }

        echolog('验证：' . json_encode($data), 'info');
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
        echolog('python core.py 3 ' . $username);
        // 借助python实现srp6验证(字符类型)
        $output = @shell_exec('python core.py 3 ' . $username);
        $data   = trim($output);
        $data = strtoupper($data);
        echolog('SeesionKey：' . $data, 'info');
        return $data;
    }
}
