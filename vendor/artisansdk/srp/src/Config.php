<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP;

use ArtisanSdk\SRP\Contracts\Config as Contract;
use phpseclib\Math\BigInteger;

class Config implements Contract
{
    /**
     * The large safe prime N for computing g^x mod N.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $prime;

    /**
     * The configured generator g of the multiplicative group.
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $generator;

    /**
     * The derived key k = H(N, g).
     *
     * @var \phpseclib\Math\BigInteger
     */
    protected $key;

    /**
     * Name of the hashing algorith H.
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Create a shared SRP configuration.
     *
     * @param string $prime     configured value N as a decimal
     * @param string $generator configured value g as a decimal
     * @param string $key       configured value k as a hexidecimal
     * @param string $algorithm name (e.g.: sha256)
     *
     * @return \ArtisanSdk\SRP\Config
     */
    public function __construct(string $prime, string $generator, string $key, string $algorithm)
    {
        $this->prime = new BigInteger($prime, 10);
        $this->generator = new BigInteger($generator, 10);
        $this->key = new BigInteger($key, 16);
        $this->algorithm = $algorithm;
    }

    /**
     * Get the large safe prime N for computing g^x mod N.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function prime(): BigInteger
    {
        return $this->prime;
    }

    /**
     * Get the configured generator g of the multiplicative group.
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function generator(): BigInteger
    {
        return $this->generator;
    }

    /**
     * Get the derived key k = H(N, g).
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function key(): BigInteger
    {
        return $this->key;
    }

    /**
     * Get the hashing algorithm name.
     *
     * @return string
     */
    public function algorithm(): string
    {
        return $this->algorithm;
    }

    /**
     * Convert to something that can be JSON serialized.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Create a new config from an array.
     *
     * @param array $config
     *
     * @return \ArtisanSdk\SRP\Contracts\Config
     */
    public static function fromArray(array $config): Contract
    {
        $config = (object) $config;

        return new static($config->prime, $config->generator, $config->key, $config->algorithm);
    }

    /**
     * Cast to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'prime'     => $this->prime()->toString(),
            'generator' => $this->generator()->toString(),
            'key'       => $this->key()->toHex(),
            'algorithm' => $this->algorithm(),
        ];
    }

    /**
     * Cast to JSON representation.
     *
     * @param int $options for encoding
     *
     * @return string
     */
    public function toJson(int $options): string
    {
        return json_encode($this->toArray(), $options);
    }
}
