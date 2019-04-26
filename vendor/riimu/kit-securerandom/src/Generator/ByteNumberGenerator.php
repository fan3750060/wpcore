<?php

namespace Riimu\Kit\SecureRandom\Generator;

use Riimu\Kit\SecureRandom\GeneratorException;

/**
 * A random number generator that wraps the given byte generator for generating integers.
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2017 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class ByteNumberGenerator implements NumberGenerator
{
    /** @var string[] Formats for different numbers of bytes */
    private static $byteFormats = ['Ca', 'na', 'Cb/na', 'Na', 'Cc/Na', 'nc/Na', 'Cd/nc/Na', 'Ja'];

    /** @var int[] Default values for byte format values */
    private static $byteDefaults = ['a' => 0, 'b' => 0, 'c' => 0, 'd' => 0];

    /** @var Generator The underlying byte generator */
    private $byteGenerator;

    /**
     * NumberByteGenerator constructor.
     * @param Generator $generator The underlying byte generator used to generate random bytes
     */
    public function __construct(Generator $generator)
    {
        $this->byteGenerator = $generator;
    }

    /**
     * Tells if the underlying byte generator is supported by the system.
     * @return bool True if the generator is supported, false if not
     */
    public function isSupported()
    {
        return $this->byteGenerator->isSupported();
    }

    /**
     * Returns bytes read from the provided byte generator.
     * @param int $count The number of bytes to read
     * @return string A string of bytes
     * @throws GeneratorException If there was an error generating the bytes
     */
    public function getBytes($count)
    {
        return $this->byteGenerator->getBytes($count);
    }

    /**
     * Returns a random integer between given minimum and maximum.
     * @param int $min The minimum possible value to return
     * @param int $max The maximum possible value to return
     * @return int A random number between the lower and upper limit (inclusive)
     * @throws \InvalidArgumentException If the provided values are invalid
     * @throws GeneratorException If an error occurs generating the number
     */
    public function getNumber($min, $max)
    {
        $min = (int) $min;
        $max = (int) $max;

        if ($min > $max) {
            throw new \InvalidArgumentException('Invalid minimum and maximum value');
        }

        if ($min === $max) {
            return $min;
        }

        $difference = $max - $min;

        if (!is_int($difference)) {
            throw new GeneratorException('Too large difference between minimum and maximum');
        }

        return $min + $this->getByteNumber($difference);
    }

    /**
     * Returns a random number generated using the random byte generator.
     * @param int $limit Maximum value for the random number
     * @return int The generated random number between 0 and the limit
     * @throws GeneratorException If error occurs generating the random number
     */
    private function getByteNumber($limit)
    {
        $bits = 1;
        $mask = 1;

        while ($limit >> $bits > 0) {
            $mask |= 1 << $bits;
            $bits++;
        }

        $bytes = (int) ceil($bits / 8);

        do {
            $result = $this->readByteNumber($bytes) & $mask;
        } while ($result > $limit);

        return $result;
    }

    /**
     * Returns a number from byte generator based on given number of bytes.
     * @param int $bytes The number of bytes to read
     * @return int A random number read from the bytes of the byte generator
     * @throws GeneratorException If the errors occurs generating the bytes for the number
     */
    private function readByteNumber($bytes)
    {
        $values = unpack(self::$byteFormats[$bytes - 1], $this->byteGenerator->getBytes($bytes)) + self::$byteDefaults;
        return $values['a'] | $values['b'] << 16 | $values['c'] << 32 | $values['d'] << 48;
    }
}
