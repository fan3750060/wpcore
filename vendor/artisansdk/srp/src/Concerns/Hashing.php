<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Concerns;

use phpseclib\Math\BigInteger;

trait Hashing
{
    /**
     * Name of the hashing algorith H.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Hash key derivative function x using H algorithm.
     *
     * @param string $value
     *
     * @return string
     */
    public function hash($value): string
    {
        return strtolower(hash($this->config->algorithm(), $value));
    }

    /**
     * Generate a new nonce H(I|:|s|:|t) string based on the user's identity I, salt s, and t time.
     *
     * @param string $identity
     * @param string $salt
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function nonce(string $identity, string $salt): BigInteger
    {
        return new BigInteger($this->hash($identity.':'.$salt.':'.time().microtime()), 16);
    }

    /**
     * Strip leading zeros off hexadecimal value.
     *
     * @param string $hexadecimal
     *
     * @return string
     */
    public function unpad(string $hexadecimal): string
    {
        return ltrim($hexadecimal, '0');
    }

    /**
     * Generate random bytes as hexadecimal string.
     *
     * @param int $bytes
     *
     * @return \phpseclib\Math\BigInteger
     */
    protected function bytes(int $bytes = 32): BigInteger
    {
        $bytes = bin2hex(random_bytes($bytes));

        return new BigInteger($bytes, 16);
    }
}
