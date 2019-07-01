<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Concerns;

use phpseclib\Math\BigInteger;

trait Session
{
    /**
     * The user's identity I.
     *
     * @var string
     */
    protected $identity;

    /**
     * The shared strong session key K = H(S).
     *
     * @var string
     */
    protected $session;

    /**
     * The salt s for the user's password verifier v encoded as a hexadecimal.
     *
     * @var string
     */
    protected $salt;

    /**
     * Get the user's identity I.
     *
     * @return string
     */
    public function identity(): string
    {
        return $this->identity;
    }

    /**
     * Get shared session key K = H(S).
     *
     * @return string
     */
    public function session(): string
    {
        return $this->session;
    }

    /**
     * Generate a new random salt.
     *
     * @param string $identity
     * @param string $salt
     *
     * @return string
     */
    public function salt(string $identity, string $salt): string
    {
        $this->identity = $identity;
        $this->salt = $salt;

        return $this->hash(time().':'.$this->number());
    }

    /**
     * Generate an RFC 5054 compliant private key value (a or b) which is in the
     * range [1, N-1] of at least 256 bits.
     *
     * A nonce based on H(I|:|s|:|t) is added to ensure random number generation.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function number(): BigInteger
    {
        $bits = max([256, $this->config->prime()->getPrecision()]);

        $one = new BigInteger(1);
        $number = $zero = new BigInteger(0);

        while ($number->equals($zero)) {
            $number = $this->bytes(1 + $bits / 8)
                ->add($this->nonce($this->identity, $this->salt))
                ->modPow($one, $this->config->prime());
        }

        return $number;
    }
}
