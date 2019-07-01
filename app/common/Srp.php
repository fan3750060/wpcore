<?php
namespace app\Common;

use phpseclib\Math\BigInteger;
use Riimu\Kit\SecureRandom\SecureRandom;

class Srp
{
    /** @var BigInteger Password verifier */
    protected $verifier;
    /** @var BigInteger Password salt */
    protected $salt;
    /** @var BigInteger|string */
    protected $N;
    protected $g;
    protected $k;
    protected $v;
    protected $A;
    protected $Ahex;
    /** @var BigInteger|null Secure Random Number */
    protected $b = null;
    /** @var BigInteger|null */
    protected $B = null;
    protected $Bhex;
    protected $M;
    protected $HAMK;
    protected $key;
    protected $randomGenerator;
    public function __construct(SecureRandom $randomGenerator)
    {
        $this->randomGenerator = $randomGenerator;
    }
    public function getBigInteger($x = 0, $base = 16)
    {
        return new BigInteger($x, $base);
    }
    public function prepare($verifier, $salt)
    {
        $this->verifier = $verifier;
        $this->salt     = $salt;
        $this->N        = $this->getBigInteger('0x894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7', 16);
        var_dump($this->N);die;
        $this->g        = $this->getBigInteger('2', 16);
        $this->k        = $this->getBigInteger($this->hash($this->N->toHex() . $this->g), 16);
        $this->v        = $this->getBigInteger($verifier, 16);
        $this->key      = '';
        while (!$this->B || bcmod($this->B, $this->N) == 0) {
            $this->b = $this->getBigInteger($this->binary2hex($this->randomGenerator->getBytes(64)), 16);
            $gPowed  = $this->g->powMod($this->b, $this->N);
            $this->B = $this->k->multiply($this->v)->add($gPowed)->powMod($this->getBigInteger(1), $this->N);
        }
        $this->Bhex = $this->B->toHex();
    }
    public function issueChallenge($A = '')
    {
        $this->A    = $this->getBigInteger($A, 16);
        $this->Ahex = $this->A->toHex();
        if ($this->A->powMod($this->getBigInteger(1), $this->N) === 0) {
            echolog('Client sent invalid key: A mod N == 0.');
        }

        $u          = $this->getBigInteger($this->hash($this->Ahex . $this->Bhex), 16);
        $v          = $this->getBigInteger($this->getVerifier(), 16);
        $avu        = $this->A->multiply($v->powMod($u, $this->N));
        $S          = $avu->modPow($this->b, $this->N);
        $Shex       = $S->toHex();
        $this->key  = $this->hash($Shex);
        $this->M    = $this->hash($this->Ahex . $this->Bhex . $Shex);
        $this->HAMK = $this->hash($this->Ahex . $this->M . $Shex);
        return array(
            'salt' => $this->getSalt(),
            'B'    => $this->Bhex,
        );
    }
    public function hash($x)
    {
        return strtolower(hash('sha1', $x));
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
    public function getN()
    {
        return $this->N;
    }
    public function getG()
    {
        return $this->g;
    }
    public function getM()
    {
        return $this->M;
    }
    public function getHAMK()
    {
        return $this->HAMK;
    }
    public function getSesionKey()
    {
        return $this->key;
    }
    public function getVerifier()
    {
        return $this->verifier;
    }
    public function getSalt()
    {
        return $this->salt;
    }
}
