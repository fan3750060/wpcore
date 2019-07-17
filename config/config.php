<?php
return [

    //Login configuration
    'LogonServer' => [
        'Address' => env('LOGON_ADDRESS','127.0.0.1'),
        'Port'    => env('LOGON_Port',3724),
    ],

    // Gameversion
    'Gameversion' => '3.3.5',

    //缓存配置
    'cache'       => [
        //缓存路径
        'path' => RUNTIME_PATH . 'cache/',
    ],

    //session设置
    'session'     => [
        // 驱动方式,留空为默认文件驱动,memcache,memcached,redis
        'type'          => '',

        // 服务器地址
        'hostname'      => '',

        // 服务器端口
        'hostport'      => '',

        // 验证密码(当redis设置密码时用)
        'requirepass'   => '',

        //  session跨页传送
        'use_trans_sid' => 1,
    ],

    //cookie设置
    'cookie'      => [
        // 是否由服务器向客户端发送cookie,建议开启
        'setcookie' => true,

        // 是否仅http可读( 参数 1:js无法读取cookie信息)
        'httponly'  => '',

        // 是否通过安全的 HTTPS 连接来传输 cookie
        'secure'    => false,

        // cookie 默认有效期(当cookie未设置过期时间,采用当前配置,如设置为0则表示不过期)
        'expire'    => 0,

        // cookie 的服务器路径
        'path'      => '/',

        // cookie 的域名
        'domain'    => '',
    ],
];
