<?php

namespace Riimu\Kit\SecureRandom\Generator;

use Riimu\Kit\SecureRandom\GeneratorException;

/**
 * Interface for generators that can also generate random numbers.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2016-2017 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
interface NumberGenerator extends Generator
{
    /**
     * Returns a securely generated random number between minimum and maximum.
     * @param int $min Minimum number to generate
     * @param int $max Maximum number to generate
     * @return int Random number between the given limits
     * @throws GeneratorException If any errors occurs in the random number generation
     */
    public function getNumber($min, $max);
}
