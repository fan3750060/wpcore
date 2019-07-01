<?php

namespace ArtisanSdk\SRP\Concerns;

use ArtisanSdk\SRP\Config;
use ArtisanSdk\SRP\Contracts\Config as Contract;
use ArtisanSdk\SRP\Contracts\Service;

trait Configuration
{
    /**
     * The configuration values for the SRP service.
     *
     * @var \ArtisanSdk\SRP\Contracts\Config
     */
    protected $config;

    /**
     * Inject the dependencies for the SRP service.
     *
     * @param \ArtisanSdk\SRP\Contracts\Config $config
     */
    public function __construct(Contract $config)
    {
        $this->config = $config;
    }

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
    public static function configure(string $prime, string $generator, string $key, string $algorithm): Service
    {
        return new static(new Config($prime, $generator, $key, $algorithm));
    }
}
