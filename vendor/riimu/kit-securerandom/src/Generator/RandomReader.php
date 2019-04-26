<?php

namespace Riimu\Kit\SecureRandom\Generator;

/**
 * Generates bytes reading directly from the random device.
 *
 * RandomReader generates random bytes by reading directly from the random
 * device (i.e. from /dev/urandom or /dev/random).
 *
 * @author Riikka Kalliomäki <riikka.kalliomaki@gmail.com>
 * @copyright Copyright (c) 2014-2017 Riikka Kalliomäki
 * @license http://opensource.org/licenses/mit-license.php MIT License
 */
class RandomReader extends AbstractGenerator
{
    /** @var string Path to the random device */
    private $source;

    /** @var resource|null File pointer to the random source */
    private $pointer;

    /**
     * Creates new instance of RandomReader.
     * @param bool $urandom True to read from /dev/urandom and false to read from /dev/random
     */
    public function __construct($urandom = true)
    {
        $this->source = $urandom ? '/dev/urandom' : '/dev/random';
        $this->pointer = null;
    }

    /**
     * Closes the file pointer.
     */
    public function __destruct()
    {
        if (isset($this->pointer)) {
            fclose($this->pointer);
        }

        $this->pointer = null;
    }

    public function isSupported()
    {
        return is_readable($this->source);
    }

    protected function readBytes($count)
    {
        return fread($this->getPointer(), $count);
    }

    /**
     * Returns the pointer to the random source.
     * @return resource The pointer to the random source
     */
    private function getPointer()
    {
        if (!isset($this->pointer)) {
            $this->pointer = fopen($this->source, 'r');

            // File read buffering is not supported on HHVM
            if (!defined('HHVM_VERSION')) {
                stream_set_chunk_size($this->pointer, 32);
                stream_set_read_buffer($this->pointer, 32);
            }
        }

        return $this->pointer;
    }
}
