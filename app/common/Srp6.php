<?php
namespace app\Common;
use phpseclib\Math\BigInteger;
use phpseclib\Crypt\Random;
use Riimu\Kit\SecureRandom\SecureRandom;
use app\Common\int_helper;

class Srp6
{
    # A is the client's public value.
    public $A = null;

    # B is the server's public value.
    public $B = null;

    # b is the server's secret value.
    public $b = 0;
    public $g = 7;

    # N is a safe-prime.
    public $N = 0x894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7;

    # s is the salt, a random value.
    public $s = [0xF4, 0x3C, 0xAA, 0x7B, 0x24, 0x39, 0x81, 0x44,
         0xBF, 0xA5, 0xB5, 0x0C, 0x0E, 0x07, 0x8C, 0x41,
         0x03, 0x04, 0x5B, 0x6E, 0x57, 0x5F, 0x37, 0x87,
         0x31, 0x9F, 0xC4, 0xF8, 0x0D, 0x35, 0x94, 0x29];

    # v is the SRP6 Verification value.
    public $v = null;

    # x = SHA1(s | SHA1(I | ":" | P))
    public $x = null;

    # u is the so called "Random scrambling parameter".
    public $u = null;

    # Identifier (username).
    public $I = '';

    # Cleartext password.
    public $P = '';

    # Sessionkey
    public $S = null;

    # K is the hashed session key, hashed with SHA1.
    public $K = null;

    # M = H(H(N) xor H(g), H(I), s, A, B, K)
    public $M = null;

    protected $randomGenerator;

    /* Initializes a new instance of the SRP6 class.
        :param username: The client´s identifier.
        :param password: The client´s password.
        */
    function __construct($username, $password)
    {
        $this->I = $username;
        $this->P = $password;
        $this->b = $this->myfmod(bin2hex(Random::string(152)),$this->N);
    
        var_dump($thi->b);die;


        $this->_x();
        $x_int = strrev($this->x);
        $this->v = $this->myfmod(pow($this->g,$x_int) , $this->N);

        $gmod = $this->myfmod(pow($this->g,$this->b) , $this->N);
        $this->B = $this->myfmod(((3 * $this->v) + $gmod) , $this->N);
        echolog($this->B);return;
        
        // self.I = username
        // self.P = password
        // self.b = int.from_bytes(os.urandom(152), "little") % self.N
        // self._x()
        // x_int = int.from_bytes(self.x, byteorder='little')
        // self.v = pow(self.g, x_int, self.N)

        // gmod = pow(self.g, self.b, self.N)
        // B = ((3 * self.v) + gmod) % self.N
        // self.B = int.to_bytes(B, 32, byteorder='little')

    }

    function myfmod($x,$y)
    {
        return fmod($x , $y);
    }

    function hash_sha1($str)
    {
        return sha1($str);
    }

    function get_random($len=3){
        //range 是将10到99列成一个数组 
        $numbers = range (0,9);
        
        $random = "";
        for ($i=0;$i<$len;$i++){
            $random.= $numbers[rand(0,9)];
        }
        return $random;
    }

    public function getBigInteger($x = 0, $base = 16)
    {
        return new BigInteger($x, $base);
    }

    function _x()
    {
        /* Generates x and sets it. */
        $this->x = sha1(int_helper::toStr($this->s).$this->P);
    }

    public function binary2hex($string)
    {
        $chars  = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        $length = strlen($string);
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $b      = ord($string[$i]);
            $result = $result . $chars[($b & 0xF0) >> 4];
            $result = $result . $chars[$b & 0x0F];
        }
        return $result;
    }
}
