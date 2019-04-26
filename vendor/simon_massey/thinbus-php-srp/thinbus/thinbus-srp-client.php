<?php

namespace Thinbus;

/*
 * Copyright 2017 Keith Wagner
 * Copyright 2017 Simon Massey
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
use Math_BigInteger;

class ThinbusSrpClient extends ThinbusSrpCommon
{

    protected $N_base10 = "21766174458617435773191008891802753781907668374255538511144643224689886235383840957210909013086056401571399717235807266581649606472148410291413364152197364477180887395655483738115072677402235101762521901569820740293149529620419333266262073471054548368736039519702486226506248861060256971802984953561121442680157668000761429988222457090413873973970171927093992114751765168063614761119615476233422096442783117971236371647333871414335895773474667308967050807005509320424799678417036867928316761272274230314067548291133582479583061439577559347101961771406173684378522703483495337037655006751328447510550299250924469288819";

    protected $g_base10 = "2";

    protected $k_base16 = "5b9e8ef059c6b32ea59fc1d322d37f04aa30bae5aa9003b8321e21ddb04e300";

    protected $params;

    protected $password;

    protected $A;

    protected $a;

    protected $Astr;

    protected $M;

    protected $K;

    protected $HAMK;

    protected $S;

    protected $k;

    protected $g;

    protected $N;

    protected $v;

    protected $b;

    protected $B;

    protected $Bstr;

    protected $H;

    /**
     *
     * @param string $N_base10str
     *            The N crypto parameter as string in base 10. Must match the parameter the client is using.
     * @param string $g_base10str
     *            The g crypto parameter as string in base 10. Must match the parameter the client is using.
     * @param string $k_base16str
     *            The k value as string in base 16. Must match the parameter that the client is using (signed bits and binary padding means Java libs create a specific value).
     * @param string $Hstr
     *            The name of the hashing algorith to use e.g. 'sha256'
     */
    public function __construct($N_base10str, $g_base10str, $k_base16str, $Hstr)
    {
        $this->N = new Math_BigInteger($N_base10str, 10);
        $this->g = new Math_BigInteger($g_base10str, 10);
        $this->k = new Math_BigInteger($k_base16str, 16);
        $this->H = $Hstr;
    }

    /**
     * Set the username and password collected from the user.
     *
     * @param unknown $userId            
     * @param unknown $password            
     */
    public function step1($userId, $password)
    {
        $this->userID = $userId;
        $this->password = $password;
        while (! $this->A || $this->A->powMod(new Math_BigInteger(1), $this->N) === 0) {
            $this->a = $this->createRandomBigIntegerInRange($this->N);
            // echo "a:".$this->a."\n";
            $this->A = $this->g->powMod($this->a, $this->N);
        }
        $this->Astr = $this->stripLeadingZeros($this->A->toHex());
        return $this->Astr;
    }

    /**
     * Computes x = H(s | H(I | ":" | P))
     * <p> Uses string concatenation before hashing. Trims leading zeros and and uses upper case for cross language interoperability.
     * Sets $this->password = null to that the password is not lying around in memory. 
     *
     * <p> Specification RFC 2945
     *
     * @param string $salt The salt 's'. Must not be null or empty.
     * @param string $identity The user identity/email 'I'. Must not be null or empty.
     * @param string $password The user password 'P'. Must not be null or empty
     */
    function generateX($salt, $identify, $password)
    {
        if(trim($salt) === '' || trim($identify) === '' || trim($password) === '') {
            throw new \Exception('one or more parameters provided is blank when checked with trim($x) === \'\'.');
        }
        
        $this->salt = $salt;
        
        $hash1 = $this->hash($identify . ":" . $password);
        
        // null the password as we don't want to leave it around in memory
        $this->password = null;
        
        // trim leading zeros for cross language interoperability
        $hash1 = $this->stripLeadingZeros($hash1);
        // strtoupper for cross language interoperability
        $hash = $this->hash(strtoupper($salt . $hash1));
        // trim leading zeros for cross language interoperability
        $x = new Math_BigInteger($this->stripLeadingZeros($hash), 16);
        // the following is simply mod(N) as all math is modulo N
        $X = $x->modPow(new Math_BigInteger("1"), $this->N);
        return $X;
    }

    function generateRandomSalt()
    {
        $ranum = $this->createRandomBigIntegerInRange($this->N);
        $salt = $this->hash((time()) . ":" . $ranum);
        return $salt;
    }

    /**
     * Generate a new SRP verifier.
     * Password is the plaintext password
     *
     * x = H(s, p) (s is chosen randomly)
     * v = g^x (computes password verifier)
     */
    function generateVerifier($salt, $identify, $password)
    {
        // echo "generateVerifier\n";
        
        $this->salt = $salt;
        $X = $this->generateX($this->salt, $identify, $password);
        
        $this->v = $this->g->powMod($X, $this->N)->toHex();
        
        // echo "c s:".$salt."\n";
        // echo "c i:".$identify."\n";
        // echo "c p:".$password."\n";
        // echo "c x:".$X."\n";
        // echo "c x:".$X->toHex()."\n";
        // echo "c v:".$this->v."\n";
        
        return $this->v;
    }

    /**
     * Initiate an SRP exchange.
     *
     * returns { A: 'client public ephemeral key. hex encoded integer.' }
     */
    function startExchange()
    {
        return $this->Astr;
    }

    /**
     * Respond to the server's challenge with a proof of password.
     *
     * challenge is an object with
     * @param B server public ephemeral key. hex encoded integer.
     * @param salt: user's salt a public value. 
     *
     * @return array(A,M) where A is the client public ephemeral key and M is tthe client proof of password. Both hex encoded integers. 
     * throws an error if it got an invalid challenge.
     */
    function step2($salt, $B)
    {
        // echo "client step2\n";
        $this->salt = $salt;
        $this->Bstr = $B;
        
        $this->B = new Math_BigInteger($this->Bstr, 16);
        
        // echo "c rawb:".$B."\n";
        // echo "c b:".$this->B."\n";
        
        if ($this->B->powMod(new Math_BigInteger(1), $this->N) === 0) {
            throw new \Exception('Server sent invalid key: B mod N == 0.');
        }
        
        $rawu = $this->hash($this->Astr . $this->Bstr);
        
        // echo "c rawu:".$rawu."\n";
        
        $u = new Math_BigInteger($rawu, 16);
        
        // echo "c u:".$u->toHex()."\n";
        
        $x = $this->generateX($this->salt, $this->userID, $this->password);
        
        // echo "c x:".$x."\n";
        
        $uxa = $u->multiply($x)->add($this->a);
        
        // echo "c uxa:".$uxa."\n";
        
        $xk = $this->g->modPow($x, $this->N)->multiply($this->k);
        
        // echo "c xk:".$xk."\n";
        
        // echo "c B:".$this->B."\n";
        // echo "c B:".$this->B->toHex()."\n";
        
        $delete = $this->B->subtract($xk);
        
        // echo "c delete:".$delete."\n";
        
        $S = $this->B->subtract($xk)->modPow($uxa, $this->N);
        
        // echo "c s:".$S."\n";
        
        $Shex = $this->stripLeadingZeros($S->toHex());
        
        // echo "c s:".$Shex."\n";
        
        $this->K = $this->hash($Shex);
        
        $M = $this->stripLeadingZeros($this->hash($this->Astr . $this->Bstr . $Shex));
        
        // echo "c M1:".$M."\n";
        
        $HAMK = $this->stripLeadingZeros($this->hash($this->Astr . $M . $Shex));
        
        // echo "c M2:".$HAMK."\n";
        
        $this->S = $S;
        $this->HAMK = $HAMK;
        $this->M = $M;
        
        return array($this->Astr, $this->M);
    }

    /**
     * Verify server's confirmation message.
     *
     * confirmation is an object with
     * - HAMK: server's proof of password.
     *
     * returns true or false.
     */
    function verifyConfirmation($confirmation)
    {
        return ($this->HAMK && ($confirmation == $this->HAMK));
    }

    /**
     * Return the shared session key.
     * Note that the server only has the same key if it verifyConfirmation returns true.
     * Defaults to hashing the session key as that is additional protection of the password in case the key is accidentally exposed to an attacker.
     *
     * returns S or H(S).
     */
    function sessionKey($hash = true)
    {
        if( !$hash ) {
            return $this->S->toHex();
        } else {
            return $this->hash($this->S->toHex());
        }
    }

    public function hash($x)
    {
        return strtolower(hash($this->H, $x));
    }

    /**
     *
     * @return string The user id 'I'.
     */
    public function getUserID()
    {
        return $this->userID;
    }
}
?>
