<?php
namespace app\World\Packet;

/**
 * RC4加解密
 */
class Rc4
{
	protected static $_instance = null;
	protected static $instance_token = array();
    public $s = [];
    public $i = 0;
    public $j = 0;

    public function __construct($seed)
    {
        $this->rc4_setkey($seed);
    }

    /**
     * 防止克隆
     * 
     */
    private function __clone() {}

    /**
     * Singleton instance
     * 
     * @return Object
     */
    public static function getInstance($seed,$gono=null)
    {
        $token = $seed;

        if(array_key_exists($token,self::$instance_token) && !$gono)
        {
            if (FALSE == (self::$instance_token[$token] instanceof self)) {
                self::$_instance = new self($seed);
            }else{
                self::$_instance = self::$instance_token[$token];
            }
        }else{
            self::$_instance = new self($seed);
            self::$instance_token[$token] = self::$_instance;
        }

        return self::$_instance;
    }

    /**
     * [rc4_setkey RC4设置key]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-24
     * ------------------------------------------------------------------------------
     * @param   [type]          $seed [description]
     * @return  [type]                [description]
     */
    public function rc4_setkey($seed)
    {
        $key[]       = "";
        $this->s[]   = "";
        $seed_length = strlen($seed);

        for ($i = 0; $i < 256; $i++) {
            $key[$i]     = ord($seed[$i % $seed_length]);
            $this->s[$i] = $i;
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j                               = ($j + $this->s[$i] + $key[$i]) % 256;
            list($this->s[$i], $this->s[$j]) = [$this->s[$j], $this->s[$i]];
        }

        $this->i = 0;
        $this->j = 0;

        // 丢弃前1024个字节，因为WoW使用ARC4-drop1024。
        for ($c = 0; $c < 1024; $c++) {
            $this->i                                     = ($this->i + 1) % 256;
            $this->j                                     = ($this->j + $this->s[$this->i]) % 256;
            list($this->s[$this->i], $this->s[$this->j]) = [$this->s[$this->j], $this->s[$this->i]];
            $r                                           = $this->s[($this->s[$this->i] + $this->s[$this->j]) % 256];
        }
    }

    /**
     * [rc4_endecode 加解密]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-24
     * ------------------------------------------------------------------------------
     * @param   [type]          $data [description]
     * @return  [type]                [description]
     */
    public function rc4_endecode($data)
    {
        $Ciphertext  = '';
        $data_length = strlen($data);

        for ($c = 0; $c < $data_length; $c++) {
            $this->i                                     = ($this->i + 1) % 256;
            $this->j                                     = ($this->j + $this->s[$this->i]) % 256;
            list($this->s[$this->i], $this->s[$this->j]) = [$this->s[$this->j], $this->s[$this->i]];
            $r                                           = $this->s[($this->s[$this->i] + $this->s[$this->j]) % 256];
            $Ciphertext .= chr($r ^ ord($data[$c]));
        }

        return $Ciphertext;
    }
}
