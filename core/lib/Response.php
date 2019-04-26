<?php
namespace core\lib;

class Response{
	
	/**
	 * [createdata 创建响应数据]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function createdata($data=null)
	{
		if(!$data) return false;

		if(is_array($data))
        {
            print_r($data);
        }else{
            if (is_scalar($data)) {
                echo $data;
            } elseif (!is_null($data)) {
                throw new Exception('不支持的数据类型输出：' . gettype($data));
            }
        }

        if (function_exists('fastcgi_finish_request')) {
            // 提高页面响应
            fastcgi_finish_request();
        }
	}
}