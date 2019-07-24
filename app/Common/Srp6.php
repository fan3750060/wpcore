<?php
namespace app\Common;

use app\Common\Math_BigInteger;

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
    public $N = '0x894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7';

    # s is the salt, a random value.
    public $s = null;

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

    public $data;

    public $sessionkey;

    /**
     * [BigInteger 大数字]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-11
     * ------------------------------------------------------------------------------
     * @param   integer         $numstr [description]
     * @param   integer         $base   [description]
     */
    public function BigInteger($numstr = 0, $base = 10)
    {
        return new Math_BigInteger($numstr, $base);
    }

    /**
     * [_random_number_helper 随机数]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-16
     * ------------------------------------------------------------------------------
     * @param   [type]          $Bytes_length [description]
     * @return  [type]                        [description]
     */
    public function _random_number_helper($Bytes_length)
    {
        return (new Math_BigInteger())->_random_number_helper($Bytes_length);
    }

    /**
     * [authSrp6 description]
     * ------------------------------------------------------------------------------
     * @author  by.fan <fan3750060@163.com>
     * ------------------------------------------------------------------------------
     * @version date:2019-07-05
     * ------------------------------------------------------------------------------
     * @param   [type]          $username [用户名]
     * @param   [type]          $password [用户名密码哈希值]
     */
    public function authSrp6($username, $password)
    {
        $this->I = $username;
        $this->P = $password;

        $N = $this->BigInteger($this->N, 16);
        $g = $this->BigInteger($this->g, 10);
        $s = (new Math_BigInteger())->_random_number_helper(32);
        $b = (new Math_BigInteger())->_random_number_helper(19 * 8);
        $b = $this->Littleendian($b->toHex());

        // //删除
        // $s = '110471522494970994944798342883164591279279963255104947336945878247464192283689';
        // $b = '36643632094460098713645300148642114838734081063340387910680922598953262708594';
        // $s = new Math_BigInteger($s, 10);
        // $b = new Math_BigInteger($b, 10);
        // //删除

        list(, $b) = $b->divide($N);

        $tempsp = $s->toHex() . $this->P;
        $tempsp = $this->BigInteger($tempsp, 16);
        $x      = sha1($tempsp->toBytes());
        $x      = $this->BigInteger($x, 16);
        $x      = $this->Littleendian($x->toHex());

        $v    = $g->modPow($x, $N);
        $gmod = $g->modPow($b, $N);

        $newint  = $this->BigInteger(3, 10);
        $newgmod = $newint->multiply($v);
        $newgmod = $newgmod->add($gmod);

        list(, $B) = $newgmod->divide($N);
        $B         = $this->Littleendian($B->toHex());

        $N = $this->Littleendian($N->toHex());

        $B_hex      = $B->toHex();
        $B          = $B->toBytes();
        $N          = $N->toBytes();
        $s_bytes    = $s->toBytes();
        $s          = $s->toHex();
        $v          = $v->toHex();
        $b          = $b->toHex();
        $g          = $g->toHex();
        $this->data = [
            'B_hex'   => $B_hex,
            'B'       => GetBytes($B),
            'N'       => GetBytes($N),
            's_bytes' => GetBytes($s_bytes),
            's'       => $s,
            'v'       => $v,
            'b'       => $b,
            'g'       => $g,
        ];
    }

    //16进制小端字节序
    public function Littleendian($str)
    {
        // return $this->BigInteger(strrev(unpack('h*',$this->BigInteger($str, 16)->toBytes())[1]),16);
        return $this->BigInteger(strrev($this->BigInteger(pack('h*', $str), 256)->toHex()), 16);
    }

    //16进制大端字节序
    public function Bigend($str)
    {
        $str = pack('H*', $str);
        $str = $this->BigInteger($str, 256);
        $str = strrev($str->toHex());
        return $this->BigInteger($str, 16);
    }

    public function configvs($v, $s, $b, $B, $I)
    {
        $this->I = $I;
        $this->v = $this->BigInteger($v, 16);
        $this->s = $this->BigInteger($s, 16);
        $this->b = $this->BigInteger($b, 16);
        $this->B = $this->BigInteger($B, 16);
        $this->N = $this->BigInteger($this->N, 16);
        $this->g = $this->BigInteger($this->g, 10);
    }

    public function getM($A, $M1)
    {
        $this->A = $this->BigInteger($A, 16);
        $strinfo = $this->A->toHex() . $this->B->toHex();
        $strinfo = $this->BigInteger($strinfo, 16);
        $this->u = sha1($strinfo->toBytes());
        $this->u = $this->BigInteger($this->u, 16);

        $temp_A  = $this->Littleendian($this->A->toHex());
        $temp_u  = $this->Littleendian($this->u->toHex());
        $this->S = $temp_A->multiply($this->v->modPow($temp_u, $this->N))->modPow($this->b, $this->N);
        $S_bytes = $this->Littleendian($this->S->toHex());
        $S_bytes = $S_bytes->toHex() . '0000000000000000';

        $S_bytes = $this->BigInteger($S_bytes, 16);
        $this->K = sha1($S_bytes->toBytes());

        // var_dump('A:' . $this->A->toHex());
        // var_dump('B:' . $this->B->toHex());
        // var_dump('u:' . $this->u->toHex());
        // var_dump('v:' . $this->v->toString());
        // var_dump('b:' . $this->b->toString());

        $check = $this->set_client_proof($M1);
        return $check;
    }

    public function set_client_proof($M1)
    {
        $t  = $this->Littleendian($this->S->toHex());
        $t  = $t->toHex() . '0000000000000000';
        $t  = $this->BigInteger($t, 16);
        $t  = $t->toBytes();
        $t  = GetBytes($t);
        $t1 = [];
        $vk = [];

        foreach (range(0, 39) as $k => $v) {
            $vk[] = 0;
        }

        foreach (range(0, 15) as $k => $v) {
            $t1[] = $t[$v * 2];
        }

        $t11     = ToStr($t1);
        $t11     = $this->BigInteger($t11, 256);
        $t1_hash = sha1($t11->toBytes());

        $t1_hash = $this->BigInteger($t1_hash, 16);
        $t1_hash = $t1_hash->toBytes();
        $t1_hash = GetBytes($t1_hash);

        foreach (range(0, 19) as $k => $v) {
            $vk[$v * 2] = $t1_hash[$v];
        }

        foreach (range(0, 15) as $k => $v) {
            $t1[$v] = $t[$v * 2 + 1];
        }

        $t11     = ToStr($t1);
        $t11     = $this->BigInteger($t11, 256);
        $t1_hash = sha1($t11->toBytes());

        $t1_hash = $this->BigInteger($t1_hash, 16);
        $t1_hash = $t1_hash->toBytes();
        $t1_hash = GetBytes($t1_hash);

        foreach (range(0, 19) as $k => $v) {
            $vk[$v * 2 + 1] = $t1_hash[$v];
        }

        $sessionkey       = ToStr($vk);
        $this->sessionkey = $sessionkey = $this->BigInteger($sessionkey, 256);

        $N_byte = $this->Littleendian($this->N->toHex());
        $g_byte = $this->Littleendian($this->g->toHex());
        $N_hash = sha1($N_byte->toBytes());
        $g_hash = sha1($g_byte->toBytes());

        $N_hash = $this->BigInteger($N_hash, 16);
        $N_hash = $N_hash->toBytes();
        $N_hash = GetBytes($N_hash);

        $g_hash = $this->BigInteger($g_hash, 16);
        $g_hash = $g_hash->toBytes();
        $g_hash = GetBytes($g_hash);

        $t3 = [];
        foreach (range(0, 19) as $k => $v) {
            $t3[] = $N_hash[$v] ^ $g_hash[$v];
        }
        $t3 = ToStr($t3);
        $t3 = $this->BigInteger($t3, 256);

        $t4 = sha1($this->I);
        $t4 = $this->BigInteger($t4, 16);

        $c_proof = sha1($t3->toBytes() . $t4->toBytes() . $this->s->toBytes() . $this->A->toBytes() . $this->B->toBytes() . $sessionkey->toBytes());

        echolog('server_auth: ' . $c_proof, 'warning');
        echolog('client_auth: ' . $M1, 'warning');

        if ($c_proof != $M1) {
            return false;
        }

        $c_proof = $this->BigInteger($c_proof, 16);
        $this->M = sha1($this->A->toBytes() . $c_proof->toBytes() . $sessionkey->toBytes());
        $this->M = $this->BigInteger($this->M, 16);
        return true;
    }
}
