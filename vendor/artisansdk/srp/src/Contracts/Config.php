<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Contracts;

use JsonSerializable;
use phpseclib\Math\BigInteger;

interface Config extends JsonSerializable
{
    /**
     * Get the large safe prime N for computing g^x mod N.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function prime(): BigInteger;

    /**
     * Get the configured generator g of the multiplicative group.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function generator(): BigInteger;

    /**
     * Get the derived key k = H(N, g).
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function key(): BigInteger;

    /**
     * Get the hashing algorithm name.
     *
     * @return string
     */
    public function algorithm(): string;

    /**
     * Create a new config from an array.
     *
     * @param array $config
     *
     * @return \ArtisanSdk\SRP\Contracts\Config
     */
    public static function fromArray(array $config): Config;

    /**
     * Cast to an array.
     *
     * @return array
     */
    public function toArray(): array;

    /**
     * Cast to JSON representation.
     *
     * @param int $options for encoding
     *
     * @return string
     */
    public function toJson(int $options): string;
}
