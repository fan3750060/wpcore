<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Contracts;

use phpseclib\Math\BigInteger;

interface Client extends Service
{
    /**
     * Step 0: Generate a new verifier v for the user identity I and password P with salt s.
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     value s chosen at random
     *
     * @return string
     */
    public function enroll(string $identity, string $password, string $salt): string;

    /**
     * Step 1: Generates a one-time client key A encoded as a hexadecimal.
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     hexadecimal value s for user's password P
     *
     * @return string
     */
    public function identify(string $identity, string $password, string $salt): string;

    /**
     * Step 2: Create challenge response to server's public key challenge B with a proof of password M1.
     *
     * @param string $server hexadecimal key B from server
     * @param string $salt   value s for user's public value A
     *
     * @throws \InvalidArgumentException for invalid public key B
     *
     * @return string
     */
    public function challenge(string $server, string $salt): string;

    /**
     * Step 3: Confirm server's proof of shared key message M2 against
     * client's proof of password M1.
     *
     * @param string $proof of shared key M2 from server
     *
     * @return bool
     */
    public function confirm(string $proof): bool;

    /**
     * Compute the RFC 2945 signature X from x = H(s | H(I | ":" | P)).
     *
     * @param string $identity I of user
     * @param string $password P for user
     * @param string $salt     value s chosen at random
     *
     * @return \phpseclib\Math\BigInteger
     */
    public function signature(string $identity, string $password, string $salt): BigInteger;
}
