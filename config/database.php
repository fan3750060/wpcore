<?php

return [
    /**************** 数据库配置 ****************/ 
	'database' =>[
		// 数据库类型
    	'type'            	=> 'mysql',

    	// 服务器地址
    	'hostname'        	=> '127.0.0.1',

    	//数据库名称
    	'dbname'			=>  'auth',

    	//用户名
    	'username'			=>	'root',

    	//密码
    	'password'			=>	'fan3339012',

    	//端口
    	'hostport'			=>	'3306',

    	//字符编码
    	'charset'			=>	'UTF8',

	],

    /**************** 数据库配置 ****************/ 
    'database_2' => [
        // 数据库类型
        'type'              => 'mysql',

        // 服务器地址
        'hostname'          => '127.0.0.1',

        //数据库名称
        'dbname'            =>  'world',

        //用户名
        'username'          =>  'root',

        //密码
        'password'          =>  'root',

        //端口
        'hostport'          =>  '3307',

        //字符编码
        'charset'           =>  'UTF8',
    ],

    /**************** memcache配置 ****************/ 
    'memcache' => [
        // 连接地址
        'hostname'          => '127.0.0.1',

        // 端口
        'hostport'          => '11211',

        //过期时间
        'expiration'        => 0,

        //前缀
        'prefix'            => 'mem',

        //是否压缩
        'compression'       => FALSE,
    ],

    /**************** redis配置 ****************/ 
    'redis' => [
        // 连接地址
        'hostname'          =>  '127.0.0.1',

        //端口
        'hostport'          => '6379',

        //密码
        'password'          => 'Auth PassWord',

        //数据库索引号
        'select'            => 0,

        //超时时间
        'timeout'           => 0,

        //有效时间
        'expire'            => 0,

        //是否长连接 false=短连接
        'persistent'        => FALSE,

        //前缀
        'prefix'            => 'redis',
    ],
];