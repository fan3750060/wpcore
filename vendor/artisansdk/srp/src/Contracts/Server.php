<?php

declare(strict_types=1);

namespace ArtisanSdk\SRP\Contracts;

interface Server extends Service
{
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
    public function challenge(string $identity, string $verifier, string $salt): string;

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
    public function verify(string $client, string $proof): string;
}
