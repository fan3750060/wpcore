<?php
namespace app;

use core\query\DB;

/**
 *
 */
class Sql
{
    public function run()
    {
        $file = 'F:\download4\azerothcore-wotlk-master\azerothcore-wotlk-master\data\sql\base\db_world';
        $file_list = scandir($file);
        unset($file_list[0]);
        unset($file_list[1]);

        foreach ($file_list as $k => $v) 
        {
            $info = file_get_contents($file.'/'.$v);

            file_put_contents('db_world.sql',$info,FILE_APPEND);
        }
        
    }
}
