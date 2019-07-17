<?php
namespace app\Common;

use core\query\DB;

/**
 * 账户管理
 */
class Account
{
    public $country = [
        'enUS' => 0,
        'koKR' => 1,
        'frFR' => 2,
        'deDE' => 3,
        'zhCN' => 4,
        'zhTW' => 5,
        'esES' => 6,
        'esMX' => 7,
        'ruRU' => 8,
    ];

    /**
     * [get_account #如果帐户存在，则从帐户返回帐户，或者返回None]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-28
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function get_account($account_name = '')
    {
        $where             = [];
        $where['username'] = $account_name;
        return DB::table('account')->where($where)->find();
    }

    /**
     * [account_banned 账户是否禁止]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-05-06
     * ------------------------------------------------------------------------------
     * @param   [type]          $account_id [description]
     * @return  [type]                      [description]
     */
    public function account_banned($account_id = null)
    {
        $where       = [];
        $where['id'] = $account_id;
        return DB::table('account_banned')->where($where)->find();
    }

    /**
     * [ip_banned IP是否禁止]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-05-06
     * ------------------------------------------------------------------------------
     * @param   [type]          $account_id [description]
     * @return  [type]                      [description]
     */
    public function ip_banned($account_id = null)
    {
        $where       = [];
        $where['ip'] = $account_id;
        $info        = DB::table('ip_banned')->where($where)->find();
        if ($info) {
            //解封日期已过
            return $info['unbandate'] <= time() ? false : true;
        } else {
            return false;
        }
    }

    /**
     * [Offline 下线]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-17
     * ------------------------------------------------------------------------------
     */
    public function Offline($username)
    {
        $where       = [];
        $where['username'] = $username;
        return DB::table('account')->where($where)->update(['online' => 0]);
    }

    /**
     * [get_realmlist 获取世界服务器信息]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-06-29
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function get_realmlist()
    {
        return DB::table('realmlist')->select();
    }

    /**
     * [get_realmlistuserinfo 获取服务器角色数量]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-13
     * ------------------------------------------------------------------------------
     * @param   [type]          $param [description]
     * @return  [type]                 [description]
     */
    public function get_realmlistuserinfo($param)
    {
        $where = [
            'accountId' => $param['accountId']
        ];
        return DB::table('account_data','characters')->where($where)->count();
    }

    /**
     * [updateinfo 更新用户信息]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-03
     * ------------------------------------------------------------------------------
     * @param   [type]          $param [description]
     * @return  [type]                 [description]
     */
    public function updateinfo($param)
    {
        $data = [];

        $data['last_login'] = date('Y-m-d H:i:s');
        $data['online']     = 1;

        if (!empty($param['ip'])) {
            $data['last_ip']         = $param['ip'];
            $data['last_attempt_ip'] = $param['ip'];
        }

        if (!empty($param['os'])) {
            $data['os'] = $param['os'];
        }

        if (!empty($param['country'])) {
            $data['locale'] = $this->country[$param['country']];
        }

        if (!empty($param['sessionkey'])) {
            $data['sessionkey'] = $param['sessionkey'];
        }

        if(!empty($param['v']))
        {
            $data['v'] = $param['v'];
        }

        if(!empty($param['s']))
        {
            $data['s'] = $param['s'];
        }

        if(!empty($param['token_key']))
        {
            $data['token_key'] = $param['token_key'];
        }

        $where             = [];
        $where['username'] = $param['username'];

        DB::table('account')->where($where)->update($data);
    }

    /**
     * [createuser 创建用户]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @param   array           $param [description]
     * @return  [type]                 [description]
     */
    public function createuser($param = [])
    {
        $param['username'] = strtoupper($param['username']);
        $param['password'] = strtoupper($param['password']);

        $where = [
            'username' => $param['username'],
        ];

        if (DB::table('account')->where($where)->find() == false) {
            $data = [
                'username'      => $param['username'],
                'sha_pass_hash' => strtoupper(sha1($param['username'] . ':' . $param['password'])),
                'joindate'      => date('Y-m-d H:i:s'),
                'expansion'     => 2,
            ];

            if ($id = DB::table('account')->insert($data)) {
                $access_data = [
                    'id'      => $id,
                    'gmlevel' => 0,
                ];
                DB::table('account_access')->insert($access_data);
                $this->commandsuccess('Account created successfully: ' . $param['username']);
            } else {
                $this->commanderror('Account creation failed: ' . $param['username']);
            }
        } else {
            $this->commanderror('Account already exists: ' . $param['username']);
        }
    }

    /**
     * [updategmlevel 更新gm权限]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @param   array           $param [description]
     * @return  [type]                 [description]
     */
    public function updategmlevel($param = [])
    {
        $param['username'] = strtoupper($param['username']);
        $where             = [
            'username' => $param['username'],
        ];

        if ($info = DB::table('account')->where($where)->find()) {
            $where = [
                'id' => $info['id'],
            ];

            $udata = [
                'gmlevel' => $param['gmlevel'],
                'RealmID' => $param['RealmID'],
            ];

            $info = DB::table('account_access')->where($where)->update($udata);

            if ($info !== false) {
                $this->commandsuccess('Account permission changed successfully: ' . $param['username']);
            } else {
                $this->commanderror('Account permission change failed: ' . $param['username']);
            }
        } else {
            $this->commanderror('Account does not exist: ' . $param['username']);
        }
    }

    #################### command ##########################
    /**
     * [command 处理命令行命令]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function command($param)
    {
        if (empty($param[1])) {
            $this->commandparamerror(implode(' ', $param));
            return;
        }

        switch (strtolower($param[1])) {
            case 'create':
                if (empty($param[2]) || empty($param[3])) {
                    $this->commandparamerror(implode(' ', $param));
                    return;
                }

                $data = [
                    'username' => trim($param[2]),
                    'password' => trim($param[3]),
                ];
                $this->createuser($data);

                break;

            case 'set':
                if (empty($param[2])) {
                    $this->commandparamerror(implode(' ', $param));
                    return;
                }

                switch (strtolower($param[2])) {
                    case 'gmlevel':
                        if (empty($param[3]) || empty($param[4]) || empty($param[5])) {
                            $this->commandparamerror(implode(' ', $param));
                            return;
                        }

                        $data = [
                            'username' => trim($param[3]),
                            'gmlevel'  => (int) $param[4],
                            'RealmID'  => (int) $param[5],
                        ];

                        $this->updategmlevel($data);

                        break;

                    default:
                        $this->commandcmderror(implode(' ', $param));
                        break;
                }

                break;

            default:
                $this->commandcmderror(implode(' ', $param));
                break;
        }
    }

    /**
     * [commandsuccess 成功]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function commandsuccess($str = null)
    {
        AUTH_LOG($str, 'success');
    }

    /**
     * [commanderror 错误]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @param   [type]          $str [description]
     * @return  [type]               [description]
     */
    public function commanderror($str = null)
    {
        AUTH_LOG($str, 'warning');
    }

    /**
     * [commandcmderror 没有这样的命令]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function commandcmderror($str = null)
    {
        AUTH_LOG($str . ' No such comand', 'error');
    }

    /**
     * [commandparamerror 参数错误]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function commandparamerror($str = null)
    {
        AUTH_LOG($str . ' Parameter error', 'error');
    }
}
