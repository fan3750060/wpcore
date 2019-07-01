<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP;

use ArtisanSdk\SRP\Contracts\Server as Contract;
use ArtisanSdk\SRP\Exceptions\InvalidKey;
use ArtisanSdk\SRP\Exceptions\PasswordMismatch;
use ArtisanSdk\SRP\Exceptions\StepReplay;
use phpseclib\Math\BigInteger;

/**
 * Server-Side SRP-6a Implementation.
 *
 * @example $srp = Server::configure($N = '21766174458...', $g = '2', $k = '5b9e8ef0...');
 *            $B = $srp->challenge($I = 'user123', $v = 'a636254492e...');
 *           $M2 = $srp->verify($A = '48147d013e3a2...', $M1 = '21d1546a18f9...');
 */
class Server implements Contract
{
    use Concerns\Configuration,
        Concerns\Hashing,
        Concerns\Session;

    /**
     * State machine for current step.
     *
     * This is used to protect against replay and dictionary attacks. A step
     * cannot be repeated or attempted out of order otherwise the protocol
     * will abort and the challenge sequence must be restarted.
     */
    protected $step = 0;

    /**
     * The user's secret password verifier v.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $verifier;

    /**
     * Server's secret random number b.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $private;

    /**
     * Server's one-time challenge B as derived from b.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $public;

    /**
     * Step 1: Generates a one-time server challenge B encoded as a hexadecimal.
     *
     * @param string $identity of user
     * @param string $verifier hexadecimal value v to verify user's password
     * @param string $salt     hexadecimal value s for user's verifier
     *
     * @throws \Exception against dictionary and replay attacks
     *
     * @return string
     */
    public function challenge(string $identity, string $verifier, string $salt): string
    {
        $this->assertIsStep(0);

        $this->identity = $identity;
        $this->verifier = new BigInteger($verifier, 16);
        $this->salt = $salt;

        $one = new BigInteger(1);
        $zero = new BigInteger(0);
        while ( ! $this->public || $this->public->powMod($one, $this->config->prime())->equals($zero)) {
            $this->private = $this->number();
            $this->public = $this->config->key()
                ->multiply($this->verifier)
                ->add($this->config->generator()->powMod($this->private, $this->config->prime()))
                ->powMod($one, $this->config->prime());
        }

        ++$this->step;

        return $this->unpad($this->public->toHex());
    }

    /**
     * Step 2: Verifies the password proof M1 based on the client's one-time public
     * key A and if validated returns the server's proof M2 of shared key S.
     *
     * @param string $client hexadecimal key A from client
     * @param string $proof  hexadecimal proof of password M1 from client
     *
     * @throws \Exception                against dictionary and replay attacks
     * @throws \InvalidArgumentException for invalid public key A
     * @throws \InvalidArgumentException for invalid proof of password M1
     *
     * @return string
     */
    public function verify(string $client, string $proof): string
    {
        $this->assertIsStep(1);

        // Verify valid public key
        $one = new BigInteger(1);
        $zero = new BigInteger(0);
        $client = new BigInteger($this->unpad($client), 16);
        if ($client->powMod($one, $this->config->prime())->equals($zero)) {
            throw new InvalidKey('Client public key failed A mod N == 0 check.');
        }

        // Verify proof M1 of password using A and previously stored verifier v
        $union = new BigInteger($this->hash($client->toHex().$this->public->toHex()), 16);
        $multiplier = $this->verifier->powMod($union, $this->config->prime());
        $shared = $this->unpad($client->multiply($multiplier)->modPow($this->private, $this->config->prime())->toHex());

        // Compute verification M = H(A | B | S)
        $message = $this->unpad($this->hash($client->toHex().$this->public->toHex().$shared));
        if ($proof !== $message) {
            throw new PasswordMismatch('Proof of password does not match password verifier.');
        }

        // Clear stored state for v, s, b, and B
        $this->verifier = null;
        $this->salt = null;
        $this->private = null;
        $this->public = null;

        // Save shared session key K
        $this->session = $this->hash($shared);

        ++$this->step;

        // Generate proof M2 of share key S
        return $this->unpad($this->hash($client->toHex().$message.$shared));
    }

    /**
     * Assert that the current step matches the expected step.
     *
     * @param int
     *
     * @throws \ArtisanSdk\SRP\Exceptions\StepReplay if replay occurs
     */
    protected function assertIsStep(int $expected): void
    {
        if ($this->step !== $expected) {
            throw new StepReplay('Possible dictionary attack. Aborting protocol.');
        }
    }
}
