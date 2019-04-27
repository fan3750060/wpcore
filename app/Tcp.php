<?php
namespace app;

use core\query\DB;
use app\common\int_helper;
/**
 *
 */
class Tcp
{
	public function run()
	{
		$client = new \swoole_client(SWOOLE_SOCK_TCP, SWOOLE_SOCK_ASYNC);
		$client->on("connect", function($cli) {
		    $cli->send("hello world\n");
		});
		$client->on("receive", function($cli, $data){
		    echo "received: {$data}\n";
		});
		$client->on("error", function($cli){
		    echo "connect failed\n";
		});
		$client->on("close", function($cli){
		    echo "connection close\n";
		});
		$client->connect("127.0.0.1", 3724, 0.5);
	}

	// 转成byte数组
    public function getBytes($string)
    {
        $bytes = array();
        for ($i = 0; $i < strlen($string); $i++) {
            //遍历每一个字符 用ord函数把它们拼接成一个php数组
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }

	public function getRealmInfo2()
    {
        // 模拟数据
        $name = 'WpcoreServer'; //服务器名称
        $addr_port = '127.0.0.1:13250'; //服务器端口
        $population = 'zhCN'; //服务器本地化

        $type_b = [0,0,0,0];
        $flags = int_helper::HexToDecimal('0x00');
        $num_chars = int_helper::HexToDecimal('0x00');
        $time_zone = int_helper::HexToDecimal('0x00');
        $unknown = int_helper::HexToDecimal('0x00');
        $cmd = int_helper::HexToDecimal('0x10');
        $name = array_merge($this->getBytes($name),[0]) ;
        $addr_port = array_merge($this->getBytes($addr_port),[0]) ;
        $population = $this->getBytes($population);

        // 拼装服内容信息
        $RealmInfo_Server = [];
        foreach ($type_b as $k => $v) 
        {
        	$RealmInfo_Server[] = $v;
        }

        $RealmInfo_Server[] = $flags;

        foreach ($name as $k => $v) 
        {
        	$RealmInfo_Server[] = $v;
        }

        foreach ($addr_port as $k => $v) 
        {
        	$RealmInfo_Server[] = $v;
        }

        foreach ($population as $k => $v) 
        {
        	$RealmInfo_Server[] = $v;
        }

        $RealmInfo_Server[] = $num_chars;
        $RealmInfo_Server[] = $time_zone;
        $RealmInfo_Server[] = $unknown;

        //拼装服脚信息
        $RealmFooter_Server = [0,0];

        //拼装服头信息
        $length = 7+count($RealmInfo_Server);
        $length_b = [$length,0];
        $unk = [0,0,0,0];
        $num_realms = int_helper::HexToDecimal('0x01');
        $RealmHeader_Server = [];
        $RealmHeader_Server[] = $cmd;

        foreach ($length_b as $k => $v) 
        {
        	$RealmHeader_Server[] = $v;
        }

        foreach ($unk as $k => $v) 
        {
        	$RealmHeader_Server[] = $v;
        }

        $RealmHeader_Server[] = $num_realms;echolog($RealmHeader_Server);

        $RealmInfo = array_merge($RealmHeader_Server,$RealmInfo_Server,$RealmFooter_Server);
        echolog(json_encode($RealmInfo));
        return $RealmInfo;
    }

}
