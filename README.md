# wpcore
	注: 模拟器为私人研究项目,以学习为目的,不进行任何商业项目的活动

	WOWCORE is an online game object server that undergoes a lot of changes over time to optimize.
	Improve and clean up the code base while improving the game
	Mechanisms and functions.

	It is completely open source; it encourages community involvement.

	If you would like to provide an idea or code, please visit the website linked to us below or
	Make a pull request to our [Github repository]

	WOWCORE是一款网络游戏对象服务器,随着时间的推移而进行大量更改以进行优化，
	在改进游戏内的同时改进和清理代码库
	机制和功能。

	它是完全开源的; 非常鼓励社区参与。

	如果您想提供想法或代码，请访问我们下面链接的网站或
	向我们的[Github存储库]发出拉取请求
	
	Linux:

	运行登录模拟器(Run Authserver): php script Server/start auth OR ./start_auth.sh

	运行世界模拟器(Run Worldserver): php script Server/start world OR ./start_world.sh

	关闭模拟器( Stop Server): ctrl+C OR ./stop.sh 

	Win:

	运行登录模拟器(Run Authserver): php script Server/start auth OR win_start_auth.bat

	运行世界模拟器(Run Worldserver): php script Server/start world OR win_start_world.bat

	注: 测试账户(test user) fan 密码 fan  (数据库密码哈希值加密为: sha1("FAN:FAN") )

		创建账户(create user): account create username password

		GM权限(set gmlevel): account set gmlevel username 3 1

		当前核心在linux下运行正常,windows下swoole出现异常(正在排查中...)

		数据库配置文件是.env，请将.env.example复制到.env并更改配置。

		The database configuration file is in .env
		please copy .env.example to .env and change the configuration.

=====

# Introduction
	This is a web game simulator written in PHP.
	Now it has debugged the process of logging in to the simulator.
	The current game client is 2.4.3_8606.
	The simulator list and account password data need to query the AUTH library.
	The world emulator authentication process is complete and packet encryption is complete
	Can enter the world to play games
	The follow-up process is under development...

	The database file is in the root directory: sql/sql.7z

	Limited energy, welcome to submit version, QQ group: 186510932 welcome to learn together ~

	Wow client link: https://pan.baidu.com/s/1A4EeOdngdtIrcgSzfkj6-A extraction code: 2vkt

# 介绍
	这是用PHP编写的网络游戏模拟器。
	现在它已经调试了登录模拟器的过程。
	目前的游戏客户端是2.4.3_8606。
	模拟器列表和帐户密码数据需要查询AUTH库。
	世界模拟器身份验证过程已完成，数据包加密已完成
	能正常进入世界进行游戏
	后续进程正在开发中......

	数据库文件在根目录: sql/sql.7z

	精力有限,欢迎提交版本,QQ群:186510932 欢迎一起研究~

	游戏客户端 链接: https://pan.baidu.com/s/1ih1fUBoyl8dLyZcCNJVoMA 提取码: 73s9

~~~
                                                                                 
 pppp          ppppppppppp         pppppp      pppppp    ppppppppp   ppppppppp  
  ppp   ppp   ppp ppp   ppp       ppp  ppp    ppp  pppp  ppp   pppp  ppp        
  ppp   pppp  ppp ppp    ppp     ppp    ppp  ppp    pppp ppp    ppp  ppp        
  ppp  ppppp  ppp ppp    ppp    ppp     ppp ppp      ppp ppp    ppp  ppp        
   ppp ppppp  ppp ppp    ppp    ppp         ppp      ppp ppp    ppp  ppp        
   ppp pp pp ppp  ppp   ppp     ppp         ppp      ppp ppp   pppp  ppppppppp  
   ppp pp pppppp  pppppppp      ppp         ppp      ppp pppppppp    ppp        
   pppppp pppppp  ppp           ppp         ppp      ppp ppp  pppp   ppp        
    ppppp  pppp   ppp           ppp     ppp ppp      ppp ppp   ppp   ppp        
    pppp   pppp   ppp            ppp    ppp  ppp    pppp ppp    ppp  ppp        
    pppp   pppp   ppp             ppp  pppp   ppp  pppp  ppp    ppp  ppp        
    pppp   pppp   ppp              pppppp      pppppp    ppp    pppp pppppppppp
        
Authserver version 1.0.1
author by.fan <fan3750060@163.com>
Gameversion: 2.4.3

~~~

# 安装及依赖 Installation dependency
	git clone https://github.com/fan3750060/wpcore.git

	Php version >= 7.0
	Swoole version >= 2.0
	redis version >= 2.2

# 运行 Run
	运行登录模拟器(Run Authserver): php script Server/start auth

	运行世界模拟器(Run Worldserver): php script Server/start world

# Demonstration 演示

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/1.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/2.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/3.png)

![image](https://pictureblog.oss-cn-beijing.aliyuncs.com/4.png)







	



