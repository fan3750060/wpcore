<?php
use core\Config;
use core\filter\Filter;
use core\lib\Session;
use core\lib\Cookie;

if (!function_exists('config'))
{
    /**
     * [config 获取和设置配置参数]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-04
     * ------------------------------------------------------------------------------
     * @param   string          $name  [参数名]
     * @param   [type]          $value [参数值]
     * @param   string          $range [作用域]
     * @return  [type]                 [description]
     */
    function config($name = '', $value = null, $range = '')
    {
        if (is_null($value) && is_string($name)) {
            return 0 === strpos($name, '?') ? Config::has(substr($name, 1), $range) : Config::get($name, $range);
        } else {
            return Config::set($name, $value, $range);
        }
    }
}

if (!function_exists('input'))
{
    /**
     * [input 获取输入数据 支持默认值和过滤]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-04
     * ------------------------------------------------------------------------------
     * @param   string          $key    [获取的变量名]
     * @param   string          $filter [过滤方法 int,string,float,bool]
     * @return  [type]                  [description]
     */
    function input($key = '',$filter = '')
    {
        $param = json_decode(ARGV,true);
        unset($param[0]);
        unset($param[1]);
        $array = [];
        foreach ($param as $key => $value) 
        {
            $array[] = $value;
        }
        return $array;
    }
}

if (!function_exists('session'))
{
    /**
     * [session]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-02
     * ------------------------------------------------------------------------------
     * @param   string          $key   [参数名]
     * @param   string          $value [参数值]
     * @return  [type]                 [description]
     */
    function session($key = null,$value = '_null')
    {

        if (is_null($key) || !$key)
        {
            return Session::boot()->all();
        }elseif($key && $value === '_null')
        {
            return Session::boot()->get($key);
        }elseif($key && $value !== '_null')
        {
            return Session::boot()->set($key,$value);
        }
    }
}

if (!function_exists('cookie'))
{
    /**
     * [cookie]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-05
     * ------------------------------------------------------------------------------
     * @param   string          $key   [参数名]
     * @param   string          $value [参数值]
     * @param   integer         $time  [过期时间]
     * @return  [type]                 [description]
     */
    function cookie($key = null,$value = '_null',$time = 0)
    {
        if (is_null($key) || !$key)
        {
            return Cookie::boot()->all();
        }elseif($key && $value === '_null')
        {
            return Cookie::boot()->get($key);
        }elseif($key && $value !== '_null')
        {
            return Cookie::boot()->set($key,$value,$time);
        }
    }
}

if (!function_exists('echolog'))
{
    /**
     * [echolog]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-01-05
     * ------------------------------------------------------------------------------
     * @param   string          $string   [内容]
     * @return  [type]                 [description]
     */
    function echolog($string = null)
    {
        if(is_array($string))
        {
            $string = var_export($string,TRUE).PHP_EOL;
        }
        echo '['.date('Y-m-d H:i:s').']：'.$string.PHP_EOL;
    }
}

if (!function_exists('import'))
{
    /**
     * [import 加载第三方类库]
     * ------------------------------------------------------------------------------
     * @Autor    by.fan
     * ------------------------------------------------------------------------------
     * @DareTime 2017-06-29
     * ------------------------------------------------------------------------------
     * @param    [type]     $folder [目录] 多级目录用'/'间隔
     * @param    [type]     $name   [名称]
     * @param    [type]     $class  [类]  可不填,不填为引入文件
     * @return   [type]             [description]
     *
     * 加载类库: import('PHPMailer','PHPMailerAutoload','PHPMailer')
     */
    function import($folder,$name,$class=null)
    {
        //参数处理
        if(!is_string($name)) return false;
        $file_path = $folder.'/'.$name.'.php';
        if(!file_exists($file_path)) return false;
        require_once($file_path);
        if(!class_exists($class)) return false;
        return new $class();//实例化模型
    }
}

if(!function_exists('http_curl'))
{
    /**
     * [http_curl 获取]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-06-12
     * ------------------------------------------------------------------------------
     * @param   [type]          $url [description]
     * @return  [type]               [description]
     */
    function http_curl($param = [])
    {
        if(!$param || !$param['url'])
        {
            return 'url为必填';
        }

        // 初始化
        $ch = curl_init();        

        // 设置浏览器的特定header
        $header = [
            "Connection: keep-alive",
            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8",
            "Upgrade-Insecure-Requests: 1",
            "DNT:1",
            "Accept-Language: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        ];

        if(!empty($param['header']))
        {
            $header = $param['header'];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        

        //访问网页
        curl_setopt($ch, CURLOPT_URL, $param['url']);

        //代理服务器设置
        if(!empty($param['proxy']))
        {
            curl_setopt($ch, CURLOPT_PROXYAUTH, CURLAUTH_BASIC); //代理认证模式
            curl_setopt($ch, CURLOPT_PROXY, $param['proxy'][0]); //代理服务器地址
            curl_setopt($ch, CURLOPT_PROXYPORT,$param['proxy'][1]); //代理服务器端口
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $param['proxy'][2].":".$param['proxy'][3]); //http代理认证帐号，username:password的格式
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP); //使用SOCKS5代理模式
        }

        //浏览器设置
        $user_agent = 'User-Agent: Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.87 Mobile Safari/537.36';
        if(!empty($param['user_agent']))
        {
            $user_agent = $param['user_agent'];
        }

        curl_setopt($ch, CURLOPT_USERAGENT,$user_agent); 

        if(!empty($param['autoreferer']))
        {
            //重定向
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            //多级自动跳转
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            //设置跳转location 最多10次
            curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        }
        
        //来源
        if(!empty($param['referer']))
        {
            curl_setopt ($ch, CURLOPT_REFERER, $param['referer']);  
        }

        //cookie设置
        if (!empty($param['cookiepath']))
        {
            curl_setopt($ch, CURLOPT_COOKIEJAR, $param['cookiepath']); //存储cookies
            curl_setopt($ch, CURLOPT_COOKIEFILE,$param['cookiepath']); //发送cookie
        }

        //是否显示头信息
        if(!empty($param['showheader']))
        {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        //是否post提交
        if(!empty($param['data']))
        {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');    // 请求方式
            curl_setopt($ch, CURLOPT_POST, true);    // post提交
            curl_setopt($ch, CURLOPT_POSTFIELDS, $param['data']);// post的变量
        }

        //超时设置
        $timeout = isset($param['timeout']) && (int)$param['timeout'] ? $param['timeout'] : 30;
        curl_setopt($ch, CURLOPT_TIMEOUT,$timeout);

        //是否为https请求
        if(!empty($param['https']))
        {
            // 针对https的设置
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        }
        
        //获取内容不直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // 执行
        $response = curl_exec($ch);

        //关闭
        curl_close($ch);

        if (!empty($param['returndecode'])) {
            $response = json_decode($response,true);
        }
        
        return $response;
    }
}

