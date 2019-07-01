<?php
namespace app;
use ArtisanSdk\SRP\Server;
use app\Common\int_helper;
use app\Common\Srp;

/**
 * Server-Side SRP-6a Implementation.
 *
 * @example $srp = Server::configure($N = '21766174458...', $g = '2', $k = '5b9e8ef0...',$hash="sha1");
 *            $B = $srp->challenge($I = 'user123', $v = 'a636254492e...');
 *           $M2 = $srp->verify($A = '48147d013e3a2...', $M1 = '21d1546a18f9...');
 */
class Testsrp
{
	public $s = '1E83C69FB48E8AE894D26A47441F167C540E19A15CF791BCA306099E237D55A7';
	public $N = 0x894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7;
	
	// public function run()
	// {
	// 	$param = input('');

	// 	$srp = Server::configure($N = int_helper::HexToDecimal($this->N), $g = '7', $k = $param[1],'sha1');
	// 	$B = $srp->challenge($I = $param[0], $v = $param[1],$this->s);
	// 	var_dump(int_helper::getBytes($B));die;
	// 	// new Srp6($param[0],$param[1]);
	// }

	public function run()
	{
		$param = input('');
		$Srp = new Srp();
		$a = $Srp->prepare($param[1],$this->s);
		var_dump($a);die;
	}
}