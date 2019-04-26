<?php
namespace core\filter;

class Filter
{
	static $types = array('int','float','string','bool','array');

	/**
	 * [param 过滤参数]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-01-02
	 * ------------------------------------------------------------------------------
	 * @param   [type]          $key    [字段]
	 * @param   [type]          $filter [description]
	 * @return  [type]                  [description]
	 */
	static function param($key=null,$filter=null)
	{
		if(!$key)
		{
			$param = [];
			foreach ($_REQUEST as $k => $v)
			{
				if(is_array($_REQUEST[$k]))
				{
					$param_array = [];
					foreach ($v as $k1 => $v1)
					{
						$param_array[$k1] = self::string_action($v1);
					}
					$param[$k] = $param_array;
				}else{
					$function = $filter.'_action';
					if(in_array($filter, self::$types))
					{
						$param[$k] = self::$function($v);
					}else{
						$param[$k] = self::string_action($v);
					}
				}
			}
			return $param;
		}else{
			if(isset($_REQUEST[$key]))
			{
				if($filter != 'array')
				{
					$function = $filter.'_action';
					if(in_array($filter, self::$types))
					{
						return self::$function($_REQUEST[$key]);
					}else{
						return self::string_action($_REQUEST[$key]);
					}
				}else{
					$param = [];
					foreach ($_REQUEST[$key] as $k => $v)
					{
						$param[$k] = self::string_action($v);
					}
				}
				return $param;
			}else{
				return '';
			}
		}
	}

	/**
	 * [int_action int类型]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function int_action($value=null)
	{
		if($value == false) return 0;

		return intval($value);
	}

	/**
	 * [float_action float类型]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function float_action($value=null)
	{
		if($value == false) return 0;

		return floatval($value);
	}

	/**
	 * [bool_action bool类型]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function bool_action($value=null)
	{
		if($value == false) return false;
		return true;
	}

	/**
	 * [array_action array类型]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function array_action($value=null)
	{
		if($value == false) return false;
		
		foreach ($variable as $key => $value) {
			# code...
		}
	}

	/**
	 * [string_action string类型]
	 * ------------------------------------------------------------------------------
	 * @Autor    by.fan
	 * ------------------------------------------------------------------------------
	 * @DareTime 2017-12-07
	 * ------------------------------------------------------------------------------
	 * @return   [type]     [description]
	 */
	static function string_action($value=null)
	{
		if($value == false) return '';

		$value = filter_var($value, FILTER_SANITIZE_MAGIC_QUOTES);
		$value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);

		$ra=array(
	        '/script/','/javascript/','/vbscript/','/expression/','/applet/','/meta/','/xml/','/blink/','/link/','/embed/','/object/','/frame/','/layer/','/title/','/bgsound/','/base/','/onload/','/onunload/','/onchange/','/onsubmit/','/onreset/','/onselect/','/onblur/','/onfocus/','/onabort/','/onkeydown/','/onkeypress/','/onkeyup/','/onclick/','/ondblclick/','/onmousedown/','/onmousemove/','/onmouseout/','/onmouseover/','/onmouseup/'
	    );

	    // $value = preg_replace($ra,'',$value);//防止xss攻击

        $value=str_replace(chr(39),"&#39;",$value);
        $value=str_replace("select","sel&#101;ct",$value);
        $value=str_replace("join","jo&#105;n",$value);
        $value=str_replace("union","un&#105;on",$value);
        $value=str_replace("where","wh&#101;re",$value);
        $value=str_replace("insert","ins&#101;rt",$value);
        $value=str_replace("delete","del&#101;te",$value);
        $value=str_replace("update","up&#100;ate",$value);
        $value=str_replace("like","lik&#101;",$value);
        $value=str_replace("drop","dro&#112;",$value);
        $value=str_replace("create","cr&#101;ate",$value);
        $value=str_replace("modify","mod&#105;fy",$value);
        $value=str_replace("rename","ren&#097;me",$value);
        $value=str_replace("alter","alt&#101;r",$value);
        $value=str_replace("cast","ca&#115;",$value);
        $value=str_replace(" ；",";",$value);

		return $value;
	}
	
}