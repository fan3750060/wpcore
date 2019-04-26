<?php
namespace app;
use app\Auth\Auth;

/**
 * Authority verification service
 * 权限验证服务
 */
class Authserver
{
    public $active;

    /**
     * [start 开始]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function start()
    {
        $str = '
 PPPP    PPPP     PPP                    PPPPPPP                                 
  PPP    PPPPP    PPP                   PPPPPPPPP                                
  PPPP   PPPPP   PPPP                  PPPP   PPPP                               
  PPPP   PPPPP   PPPP                 PPPP     PPPP                              
   PPP  PPPPPPP  PPP  PPPPPPPP        PPP       PPP   PPPPPP   PPPPPP   PPPPPP   
   PPP  PPP PPP  PPP  PPPPPPPPP      PPPP           PPPPPPPPP  PPPPPP PPPPPPPPP  
   PPPP PPP PPP PPPP  PPPP  PPPP     PPPP           PPPP  PPPP PPPP   PPP   PPPP 
   PPPP PPP PPP PPP   PPP   PPPP     PPPP          PPPP   PPPP PPP   PPPP    PPP 
    PPPPPP  PPPPPPP   PPP    PPP     PPPP          PPP     PPP PPP   PPPPPPPPPPP 
    PPPPPP   PPPPPP   PPP    PPP     PPPP          PPP     PPP PPP   PPPPPPPPPPP 
    PPPPPP   PPPPPP   PPP    PPP      PPP       PPPPPP     PPP PPP   PPP         
     PPPPP   PPPPP    PPP   PPPP      PPPP     PPPPPPPP   PPPP PPP   PPPP        
     PPPP    PPPPP    PPPP  PPPP       PPPP   PPPP  PPPP  PPPP PPP    PPPP  PPPP 
     PPPP     PPPP    PPPPPPPPP         PPPPPPPPPP  PPPPPPPPP  PPP    PPPPPPPPP  
     PPPP     PPPP    PPPPPPPP           PPPPPPP      PPPPPP   PPP      PPPPPP   
                      PPP                                                        
                      PPP                                                        
                      PPP                                                        
                      PPP                                                        
                      PPP 
        ';
        echo $str.PHP_EOL;
        echo 'Authserver version 1.0.1'.PHP_EOL;
        echo 'author by.fan <fan3750060@163.com>'.PHP_EOL;
        echo 'Gameversion: ' . config('Gameversion').PHP_EOL;

        // 初始状态
        $this->active = true;

        $this->runAuthServer();
    }

    /**
     * [runAuthServer 运行服务器]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-04-19
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function runAuthServer()
    {
    	if($this->active)
    	{
    		(new Auth) -> listen(); //开启监听
    	}else{
    		echolog('Error: Did not start the service according to the process...');
    	}
    }
}
