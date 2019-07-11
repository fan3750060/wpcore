<?php
namespace app;

use app\Common\Account;

/**
 * account create username password
 *
 * account set gmlevel username 3 1
 *
 */
class Command
{
    /**
     * [run 命令操作]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-04
     * ------------------------------------------------------------------------------
     * @return  [type]          [description]
     */
    public function run()
    {
        sleep(1);
        fwrite(STDOUT, 'WC>');
        while (true) {
            $param = fgets(STDIN);
            $param = explode(' ', trim($param));
            if ($param && $param[0] != 'auth' && $param[0] != 'world') {
                switch (strtolower($param[0])) {
                    case 'account':
                        (new Account())->command($param);
                        break;

                }

                echo 'WC>';
            }
        }
    }
}
