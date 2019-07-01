<?php
namespace app\Common;
use core\query\DB;

/**
 * 账户管理
 */
class Account
{
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
}