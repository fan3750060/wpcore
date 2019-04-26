# wpcore
	World of Warcraft server

	魔兽世界服务器
=====

# Introduction
	This is a World of Warcraft server written in php language. My original intention is to verify whether PHP can 
	
	write a MMORPG game server. According to the current progress,PHP is fully capable. Now it has been debugged 
	
	through the login server. Process, because World of Warcraft login verification uses the SRP6 protocol, my 
	
	current temporary solution is to call Python module to handle SRP6 authentication in PHP, while PHP handles 
	
	the overall login process. The current World of Warcraft client is 1.12.1. The server list and account password
	
	data are all local analog data. The account needs to be consistent with the password to pass the verification. 
	
	The world server will start writing after the login server is improved.

# 介绍
	这是一款用php语言写的魔兽世界服务器,我的初衷是验证PHP是否能写一款MMORPG游戏服务器,依照现在的进度来看,PHP是完全可以
	
	胜任的,现在已经调试通了登录服务器的流程,由于魔兽世界登录验证采用了SRP6协议,我目前暂时采用的方案是php中调用python
	
	模块处理SRP6的验证,而PHP则处理整体的登录流程,目前测试的魔兽世界客户端为1.12.1,服务器列表及账户密码数据都为本地模拟
	
	数据,账户需要与密码一致才能通过验证,世界服务器将在登录服务器完善之后开始编写.

~~~
                                                                                 
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
        
Authserver version 1.0.1
author by.fan <fan3750060@163.com>
Gameversion: 1.12.1

~~~

# 安装及依赖 Installation dependency
	git clone https://github.com/fan3750060/wpcore.git

	Php version >= 7.0
	Swoole version >= 2.0
	Python version >= 3.5

# 运行 Run
	./start 
	or
	php script Authserver/start

# Demonstration 演示

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/0.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/1.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/2.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/3.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/4.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/5.png?x-oss-process=image/resize,w_700,h_700)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/wow/6.png?x-oss-process=image/resize,w_700,h_700)





	



