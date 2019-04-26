<?php
namespace core;
use core\Config;
use core\lib\Response;

class Route{  
    static function executeroute() 
    {  
        $param = json_decode(ARGV,true);
        if(isset($param[1]))
        {
            if($param[1] == '--list')
            {
                $controller = 'Help';
                $action = 'scriptlist';
                $namespaceclass = '\core\\'.$controller;
            }else{
                if(strpos($param[1],'/') !== false)
                {
                    $param = explode('/', $param[1]);
                    $controller = $param[0];
                    $action = $param[1];
                }else{
                    die('['.date('Y-m-d H:i:s').']：param ' . $param[1] . ' error! Make sure to use / split controller and method!'.PHP_EOL);
                }
                 $namespaceclass = '\app\\'.$controller;
            }
        }else{
            $controller = 'Test';
            $action = 'run';
            $namespaceclass = '\core\\'.$controller;
        }

        if(class_exists($namespaceclass)) 
        {
            $newObj = new $namespaceclass();
            call_user_func_array([$newObj,$action],[]);
        } else {
            die('['.date('Y-m-d H:i:s').']：class ' . $namespaceclass . ' is not exists!'.PHP_EOL);
        }
    }  
} 