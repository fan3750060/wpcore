<?php
namespace app\Common;
use phpseclib\Math\BigInteger;
use Riimu\Kit\SecureRandom\SecureRandom;

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
    public $s = '';

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
        $this->N = base_convert($this->N, 16, 10);
        $this->b = $this->get_random(152);
        
        $this->_x();

        $gmod = bcmod($this->b,$this->N);
        $this->v = bcmod($this->x,$this->N);
        $this->B = bcmod((($this->v * 3) + $gmod), $this->N);

        echolog($this->B);return;
        
        // $x_int = int.from_bytes($this->x, byteorder='little');
        // $this->v = pow($this->g, $x_int, $this->N);

        // $gmod = pow($this->g, $this->b, $this->N);
        // $B = ((3 * $this->v) + $gmod) % $this->N;
        // $this->B = int.to_bytes($B, 32, byteorder='little');
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
        $temp = sha1($this->I.':'.$this->P);
        $this->x = sha1($this->s.$temp);
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
