<?php
namespace app\Common;

use core\query\DB;

/**
 * 
 */
class CharacterHandler
{
	
	public static function create($param = null)
	{
		$data = [
			'account' => $param['accountID'],
			'name' => $param['name'],
			'race' => $param['race'],
			'class' => $param['class'],
			'gender' => $param['gender'],
			'level' => isset($param['level']) ? $param['level'] : 1,
			'money' => env('Money',1000)
		];
		return DB::table('characters','characters')->insert($data);
	}
}