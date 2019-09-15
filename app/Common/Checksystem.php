<?php
namespace app\Common;

/**
 *
 */
class Checksystem
{
    public static function check()
    {
        self::checksystem();
        self::checkbredis();
        self::checkphpversion();
        self::checkswooleversion();
        self::checkbcmath();
    }

    public static function checksystem()
    {
        if (strtoupper(PHP_OS) == 'LINUX') {
            echolog('The current system is: Linux');
        } elseif (strpos(strtoupper(PHP_OS), 'WIN') != false) {
            echolog('The current system is: Windows');
        }
    }

    public static function checkbredis()
    {
        if (!extension_loaded('redis')) {
            echolog('This core needs to use php\'s redis extension for high-precision calculations.', 'error');die;
        }
    }

    public static function checkphpversion()
    {
        if (version_compare(phpversion(), '7.0', '<')) {
            echolog('The core version of PHP that needs to be relied on is 7.0 and above. The current PHP version is:: ' . phpversion(), 'error');die;
        }
    }

    public static function checkswooleversion()
    {
        if (!extension_loaded('swoole')) {
            echolog('It is detected that there is no Swoole extension currently, please install Swoole extension for PHP', 'error');die;
        }
    }

    public static function checkbcmath()
    {
        if (!extension_loaded('bcmath')) {
            echolog('This core needs to use php\'s bcmath extension for high-precision calculations.', 'error');die;
        }
    }
}
