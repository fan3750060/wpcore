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
		$where = [];
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
		$where = [];
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
		$where = [];
		$where['ip'] = $account_id;
		$info = DB::table('ip_banned')->where($where)->find();
		if($info)
		{
			//解封日期已过
			return $info['unbandate'] <= time() ? false : true;
		}else{
			return false;
		}
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
		$data['online'] = 1;

		if(!empty($param['ip']))
		{
			$data['last_ip'] = $param['ip'];
			$data['last_attempt_ip'] = $param['ip'];
		}

		if(!empty($param['os']))
		{
			$data['os'] = $param['os'];
		}

		if(!empty($param['country']))
		{
			$data['locale'] = $this->country[$param['country']];
		}

		if(!empty($param['sessionkey']))
		{
			$data['sessionkey'] = $param['sessionkey'];
		}

		$where = [];
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
	public function createuser($param=[])
	{
		$param['username'] = strtoupper($param['username']);
		$param['password'] = strtoupper($param['password']);

		$where = [
			'username' => $param['username']
		];

		if(DB::table('account')->where($where)->find() == false)
		{
			$data = [
				'username' => $param['username'],
				'sha_pass_hash' => strtoupper(sha1($param['username'].':'.$param['password'])),
				'joindate' => date('Y-m-d H:i:s'),
				'expansion' => 2
			];

			if($id = DB::table('account')->insert($data))
			{
				$access_data = [
					'id' => $id,
					'gmlevel' => 0,
				];
				DB::table('account_access')->insert($access_data);
				$this->commandsuccess('账户创建成功:'.$param['username']);
			}else{
				$this->commanderror('账户创建失败:'.$param['username']);
			}
		}else{
			$this->commanderror('当前账户已经存在:'.$param['username']);
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
	public function updategmlevel($param=[])
	{
		$param['username'] = strtoupper($param['username']);
		$where = [
			'username' => $param['username']
		];

		if($info = DB::table('account')->where($where)->find())
		{
			$where = [
				'id' => $info['id'],
			];

			$udata = [
				'gmlevel' => $param['gmlevel'],
				'RealmID' => $param['RealmID'],
			];

			$info = DB::table('account_access')->where($where)->update($udata);

			if($info !== false)
			{
				$this->commandsuccess('账户权限更改成功:'.$param['username']);
			}else{
				$this->commanderror('账户权限更改失败:'.$param['username']);
			}
		}else{
			$this->commanderror('账户不存在:'.$param['username']);
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
		if(empty($param[1]))
		{
			$this->commandparamerror(implode(' ', $param));
			return;
		}

		switch (strtolower($param[1])) {
			case 'create':
				if(empty($param[2]) || empty($param[3]))
				{
					$this->commandparamerror(implode(' ', $param));
					return;
				}

				$data = [
					'username' => trim($param[2]),
					'password' => trim($param[3])
				];
				$this->createuser($data);

				break;
			
			case 'set':
				if(empty($param[2]))
				{
					$this->commandparamerror(implode(' ', $param));
					return;
				}

				switch (strtolower($param[2])) {
					case 'gmlevel':
						if(empty($param[3]) || empty($param[4]) || empty($param[5]))
						{
							$this->commandparamerror(implode(' ', $param));
							return;
						}

						$data = [
							'username' => trim($param[3]),
							'gmlevel' => (int)$param[4],
							'RealmID' => (int)$param[5],
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
		echolog($str,'success');
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
		echolog($str,'warning');
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
		echolog($str.' 没有这样的命令','error');
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
	public function commandparamerror($str=null)
	{
		echolog($str.' 参数错误','error');
	}
}