<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Contracts;

use phpseclib\Math\BigInteger;

interface Service
{
    /**
     * Inject the dependencies for the SRP service.
     *
     * @param \ArtisanSdk\SRP\Contracts\Config $config
     */
    public function __construct(Config $config);

    /**
     * Configure the SRP shared settings.
     *
     * @param string $prime     configured value N as a decimal
     * @param string $generator configured value g as a decimal
     * @param string $key       configured value k as a hexidecimal
     * @param string $algorithm name (e.g.: sha256)
     *
     * @return \ArtisanSdk\SRP\Service
     */
    public static function configure(string $prime, string $generator, string $key, string $algorithm): Service;

    /**
     * Generate an RFC 5054 compliant private key value (a or b) which is in the
     * range [1, N-1] of at least 256 bits.
     *
     * A nonce based on H(I|:|s|:|t) is added to ensure random number generation.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function number(): BigInteger;

    /**
     * Get the user's identity I.
     *
     * @return string
     */
    public function identity(): string;

    /**
     * Get shared session key K = H(S).
     *
     * @return string
     */
    public function session(): string;
}
