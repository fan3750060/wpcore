<?php
namespace core;

/**
 * 脚本帮助
 */
class Help
{
	public $files;

	public $allclassaction;

	/**
	 * [scriptlist 列表]
	 * ------------------------------------------------------------------------------
	 * @author  by.fan <fan3750060@163.com>
	 * ------------------------------------------------------------------------------
	 * @version date:2018-05-09
	 * ------------------------------------------------------------------------------
	 * @return  [type]          [description]
	 */
	public function scriptlist()
	{
		$this->getfiles(APP_PATH);
		$namespace = '\app';

		foreach ($this->files as $key => $value) 
		{
			$classname = explode('.', $value)[0];
			$class = $namespace.'\\'.$classname;
			foreach (get_class_methods($class) as $k1 => $v1) 
			{
				$this->allclassaction[] = PHP_EOL."\t".$classname.'/'.$v1.PHP_EOL;
			}
		}

		if($this->allclassaction)
		{
			echo 'Help version 0.1.1'.PHP_EOL.'程序脚本列表';

			foreach ($this->allclassaction as $key => $value) 
			{
				echo $value;
			}
		}
	}

	/**
     * [getfiles 获取所有文件]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2018-04-21
     * ------------------------------------------------------------------------------
     * @param   [type]          $path [description]
     * @return  [type]                [description]
     */
    public function getfiles($path)
    { 
        foreach(scandir($path) as $afile)
        {
            if($afile=='.'||$afile=='..'|| substr($afile,0,1) == '~' ) continue;
            if(is_dir($path.'/'.$afile)) 
            { 
                $this->getfiles($path.'/'.$afile); 
            } else {

                if((substr($afile,-3) == 'php' ) && substr($afile,0,1) != '.')
                {
                    $this->files[] = $afile;
                }
            } 
        } 
    }
}