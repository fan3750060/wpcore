<?php

return [
    /**************** 数据库配置 ****************/
    'database'   => [
        // 数据库类型
        'type'     => env('AUTH_CONNECTION', 'mysql'),

        // 服务器地址
        'hostname' => env('AUTH_HOST', '127.0.0.1'),

        //数据库名称
        'dbname'   => env('AUTH_DATABASE', 'test'),

        //用户名
        'username' => env('AUTH_USERNAME', 'root'),

        //密码
        'password' => env('AUTH_PASSWORD', ''),

        //端口
        'hostport' => env('AUTH_PORT', '3306'),

        //字符编码
        'charset'  => env('AUTH_CHARSET', 'UTF8'),

    ],

    /**************** 数据库配置 ****************/
    'database_2' => [
        // 数据库类型
        'type'     => 'mysql',

        // 服务器地址
        'hostname' => '127.0.0.1',

        //数据库名称
        'dbname'   => 'test',

        //用户名
        'username' => 'root',

        //密码
        'password' => 'root',

        //端口
        'hostport' => '3306',

        //字符编码
        'charset'  => 'UTF8',
    ],

    /**************** memcache配置 ****************/
    'memcache'   => [
        // 连接地址
        'hostname'    => '127.0.0.1',

        // 端口
        'hostport'    => '11211',

        //过期时间
        'expiration'  => 0,

        //前缀
        'prefix'      => 'mem',

        //是否压缩
        'compression' => false,
    ],

    /**************** redis配置 ****************/
    'redis'      => [
        // 连接地址
        'hostname'   => env('REDIS_HOST', '127.0.0.1'),

        //端口
        'hostport'   => env('REDIS_PORT', 6379),

        //密码
        'password'   => env('REDIS_PASSWORD', null),

        //数据库索引号
        'select'     => env('REDIS_DB', 0),

        //超时时间
        'timeout'    => 0,

        //有效时间
        'expire'     => 0,

        //是否长连接 false=短连接
        'persistent' => false,

        //前缀
        'prefix'     => 'redis',
    ],

    /**************** mongo配置 ****************/
    'mongo'      => [
        // 连接地址
        'hostname' => env('MOGODB_PRIMARY', 'localhost'),

        //端口
        'hostport' => env('MOGODB_PORT',27017),

        //库名称
        'dbname'   => env('MOGODB_DATABASE','task_manager'),

        //用户
        'username' => env('MOGODB_USERNAME', 'forge'),

        //密码
        'password' => env('MOGODB_PASSWORD', ''),

        //audb
        'authdb'   => env('MOGODB_AUTHDB', 'rule_engine'),

    ],
];