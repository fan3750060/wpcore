# wpcore
	World of Warcraft server

	魔兽世界服务器
	
	Linux:

	运行登录服务器(Run Authserver): php script Server/start auth OR ./start_auth.sh

	运行世界服务器(Run Worldserver): php script Server/start world OR ./start_world.sh

	关闭服务器( Stop Server): ctrl+C OR ./stop.sh 

	Win:

	运行登录服务器(Run Authserver): php script Server/start auth OR win_start_auth.bat

	运行世界服务器(Run Worldserver): php script Server/start world OR win_start_world.bat

	注: 测试账户(test user) fan 密码 fan  (数据库密码哈希值加密为: sha1("FAN:FAN") )

		创建账户(create user): account create username password

		GM权限(set gmlevel): account set gmlevel username 3 1

		当前核心在linux下运行正常,windows下swoole出现异常(正在排查中...)

		数据库配置文件是.env，请将.env.example复制到.env并更改配置。

		The database configuration file is in .env
		please copy .env.example to .env and change the configuration.

=====

# Introduction
	This is a World of Warcraft server written in PHP.
	Now it has debugged the process of logging in to the server.
	The current World of Warcraft client is 2.4.3_8606.
	The server list and account password data need to query the AUTH library.
	The world server authentication process is complete and packet encryption is complete
	The follow-up process is in development...

	The database file is in the root directory: sql/sql.7z

	Limited energy, welcome to submit version, QQ group: 186510932 welcome to learn together ~

	Wow client link: https://pan.baidu.com/s/1A4EeOdngdtIrcgSzfkj6-A extraction code: 2vkt

# 介绍
	这是用PHP编写的魔兽世界服务器。
	现在它已经调试了登录服务器的过程。
	目前的魔兽世界客户端是2.4.3_8606。
	服务器列表和帐户密码数据需要查询AUTH库。
	世界服务器身份验证过程已完成，数据包加密已完成
	后续进程正在开发中......

	数据库文件在根目录: sql/sql.7z

	精力有限,欢迎提交版本,QQ群:186510932 欢迎一起研究~

	wow客户端 链接: https://pan.baidu.com/s/1ih1fUBoyl8dLyZcCNJVoMA 提取码: 73s9

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
Gameversion: 3.3.5

~~~

# 安装及依赖 Installation dependency
	git clone https://github.com/fan3750060/wpcore.git

	Php version >= 7.0
	Swoole version >= 2.0
	redis version >= 2.2

# 运行 Run
	运行登录服务器(Run Authserver): php script Server/start auth

	运行世界服务器(Run Worldserver): php script Server/start world

# Demonstration 演示

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/1.png1.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/1.png2.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/1.png3.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/1.png4.png)







	



