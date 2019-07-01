<?php
namespace app\Auth;
use app\Common\Account;
use app\Common\int_helper;

/**
 * 
 */
class Realmlist
{
	/**
	 * [get_realmlist 获取世界服务器配置]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2019-07-01
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function get_realmlist()
	{
		$Account      = new Account();
        $realmlist = $Account -> get_realmlist();
        $data = $this->getRealmInfo($realmlist[0]);//暂时支持第一个服务器
        $RealmInfo = array_merge($data[0], $data[1], $data[2]);

        return $RealmInfo;
	}

	/**
     * [getRealmInfo 获取服务器列表]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-27
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function getRealmInfo($realmlist)
    {
        // 模拟数据
        $name       = $realmlist['name']; //服务器名称
        $addr_port  = $realmlist['address'].':'.$realmlist['port']; //服务器端口

        $type_b     = [0, 0, 0,0];
        $population  = int_helper::HexToDecimal('0x00');
        $num_chars  = int_helper::HexToDecimal('0x00');
        $time_zone  = int_helper::HexToDecimal('0x00');
        $unknown    = int_helper::HexToDecimal('0x00');
        $cmd        = int_helper::HexToDecimal('0x10');
        $name       = array_merge(int_helper::getBytes($name), [0]);
        $addr_port  = array_merge(int_helper::getBytes($addr_port), [0]);

        // 拼装服内容信息 5
        $RealmInfo_Server = [];
        foreach ($type_b as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        //13
        foreach ($name as $k => $v) {
            $RealmInfo_Server[] = $v;
        }

        //20
        foreach ($addr_port as $k => $v) {
            $RealmInfo_Server[] = $v;
        }
        
        //4
        $RealmInfo_Server[] = $population;
        $RealmInfo_Server[] = $num_chars;
        $RealmInfo_Server[] = $time_zone;
        $RealmInfo_Server[] = $unknown;

        //拼装服脚信息
        $RealmFooter_Server = [1,1,44,16, 0];

        //拼装服头信息
        $length               = 5+count($RealmInfo_Server)+count($RealmFooter_Server);
        $length_b             = [$length, 0];
        $unk                  = [0, 0, 0, 0];//4
        $num_realms           = 1;//服务器数量
        $RealmHeader_Server   = [];
        $RealmHeader_Server[] = $cmd;

        foreach ($length_b as $k => $v) {
            $RealmHeader_Server[] = $v;
        }

        foreach ($unk as $k => $v) {
            $RealmHeader_Server[] = $v;
        }

        $RealmHeader_Server[] = $num_realms;

        return [$RealmHeader_Server, $RealmInfo_Server, $RealmFooter_Server];
    }
}