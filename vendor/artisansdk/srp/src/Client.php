<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP;

use ArtisanSdk\SRP\Contracts\Client as Contract;
use ArtisanSdk\SRP\Exceptions\EmptyParameter;
use ArtisanSdk\SRP\Exceptions\InvalidKey;
use phpseclib\Math\BigInteger;

/**
 * Client-Side SRP-6a Implementation.
 *
 * @example $srp = Client::configure($N = '21766174458...', $g = '2', $k = '5b9e8ef0...');
 *            $s = $srp->salt($I = 'user123', $s = 'a18f921d1546...');
 *     $verifier = $srp->enroll($I = 'user123', $p = 'password');
 *            $A = $srp->identify($I = 'user123', $p = 'password');
 *           $M2 = $srp->challenge($B = '48147d013e3a2...', $s = '21d1546a18f9...');
 *       $result = $srp->confirm($M2 = '937ee2752d2d0a18eea2e7');
 */
class Client implements Contract
{
    use Concerns\Configuration,
        Concerns\Hashing,
        Concerns\Session;

    /**
     * The user's password P as plain text.
     *
     * @var string
     */
    protected $password;

    /**
     * Client's proof of password message M1.
     *
     * @var string
     */
    protected $proof;

    /**
     * Client's secret random number a.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $private;

    /**
     * Client's one-time challenge A as derived from a.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $public;

    /**
     * Step 0: Generate a new verifier v for the user identity I and password P with salt s.
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     value s chosen at random
     *
     * @return string
     */
    public function enroll(string $identity, string $password, string $salt): string
    {
        $this->identity = $identity;
        $this->salt = $salt;

        $signature = $this->signature($identity, $password, $this->salt);

        return $this->config->generator()->powMod($signature, $this->config->prime())->toHex();
    }

    /**
     * Step 1: Generates a one-time client key A encoded as a hexadecimal.
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     hexadecimal value s for user's password P
     *
     * @return string
     */
    public function identify(string $identity, string $password, string $salt): string
    {
        $this->identity = $identity;
        $this->password = $password;
        $this->salt = $salt;

        $one = new BigInteger(1);
        $zero = new BigInteger(0);
        while ( ! $this->public || $this->public->powMod($one, $this->config->prime())->equals($zero)) {
            $this->private = $this->number();
            $this->public = $this->config->generator()->powMod($this->private, $this->config->prime());
        }

        return $this->unpad($this->public->toHex());
    }

    /**
     * Step 2: Create challenge response to server's public key challenge B with a proof of password M1.
     *
     * @param string $server hexadecimal key B from server
     * @param string $salt   value s for user's public value A
     *
     * @throws \ArtisanSdk\SRP\Exceptions\InvalidKey for invalid public key B
     *
     * @return string
     */
    public function challenge(string $server, string $salt): string
    {
        // Verify valid public key
        $one = new BigInteger(1);
        $zero = new BigInteger(0);
        $server = new BigInteger($this->unpad($server), 16);
        if ($server->powMod($one, $this->config->prime())->equals($zero)) {
            throw new InvalidKey('Server public key failed B mod N == 0 check.');
        }

        // Create proof M1 of password using A and previously stored verifier v
        $union = new BigInteger($this->hash($this->public->toHex().$server->toHex()), 16);
        $signature = $this->signature($this->identity, $this->password, $salt);
        $exponent = $union->multiply($signature)->add($this->private);
        $shared = $this->unpad($server->subtract($this->config->generator()->modPow($signature, $this->config->prime())->multiply($this->config->key()))->modPow($exponent, $this->config->prime())->toHex());

        // Compute verification M = H(A | B | S)
        $message = $this->unpad($this->hash($this->public->toHex().$server->toHex().$shared));

        // Generate proof of password M1 = H(A | M | S) using client public key A and shared key S
        $this->proof = $this->unpad($this->hash($this->public->toHex().$message.$shared));

        // Clear stored state for P, s, a, and A
        $this->password = null;
        $this->salt = null;
        $this->private = null;
        $this->public = null;

        // Save shared session key K and
        $this->session = $this->hash($shared);

        // Respond with message M1
        return $message;
    }

    /**
     * Step 3: Confirm server's proof of shared key message M2 against
     * client's proof of password M1.
     *
     * @param string $proof of shared key M2 from server
     *
     * @return bool
     */
    public function confirm(string $proof): bool
    {
        return $this->proof
            && $this->proof === $proof;
    }

    /**
     * Compute the RFC 2945 signature X from x = H(s | H(I | ":" | P)).
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     value s chosen at random
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function signature(string $identity, string $password, string $salt): BigInteger
    {
        $this->assertNotEmpty(func_get_args());

        $hash = $this->unpad($this->hash($identity.':'.$password));

        return (new BigInteger($this->unpad($this->hash(strtoupper($salt.$hash))), 16))
            ->modPow(new BigInteger(1), $this->config->prime());
    }

    /**
     * Assert that none of the params were emtpy strings when trimmed.
     *
     * @param string[] $params
     *
     * @throws \ArtisanSdk\SRP\Exceptions\EmptyParameter for an empty parameter
     */
    protected function assertNotEmpty(array $params): void
    {
        foreach ($params as $param) {
            if ('' === trim($param)) {
                throw new EmptyParameter('An empty string was passed as parameter to signature.');
            }
        }
    }
}
