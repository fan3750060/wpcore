<?php

namespace Riimu\Kit\SecureRandom\Generator;

use Riimu\Kit\SecureRandom\GeneratorException;

/**
 * Abstract generator for handling byte generator errors.
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2014-2017 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
abstract class AbstractGenerator implements Generator
{
    public function getBytes($count)
    {
        $count = (int) $count;

        if ($count === 0) {
            return '';
        }

        $bytes = $this->readBytes($count);

        if (!is_string($bytes)) {
            throw new GeneratorException('The random byte generator did not return a string');
        }

        if (strlen($bytes) !== $count) {
            throw new GeneratorException('The random byte generator returned an invalid number of bytes');
        }

        return $bytes;
    }

    /**
     * Reads bytes from the randomness source.
     * @param int $count number of bytes to read
     * @return string|false The bytes read from the randomness source or false on error
     * @throws GeneratorException If error occurs in byte generation
     */
    abstract protected function readBytes($count);
}
