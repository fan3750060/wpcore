<?php

require_once __DIR__ . '/../vendor/autoload.php';
$rng = new \Riimu\Kit\SecureRandom\SecureRandom();

var_dump(base64_encode($rng->getBytes(32)));     // Returns a random byte string
var_dump($rng->getInteger(100, 1000));           // Returns a random integer between 100 and 1000
var_dump($rng->getRandom());                     // Returns a random float between 0 and 1 (not including one)
var_dump($rng->getFloat());                      // Returns a random float between 0 and 1
var_dump($rng->getArray(range(0, 100), 5));      // Returns 5 randomly selected elements from the array
var_dump($rng->choose(range(0, 100)));           // Returns one randomly chosen value from the array
var_dump($rng->shuffle(range(0, 9)));            // Returns the array in random order
var_dump($rng->getSequence('01', 32));           // Returns a random sequence of 0s and 1s with length of 32
var_dump($rng->getSequence(['a', 'b', 'c'], 5)); // Returns an array with 5 elements randomly chosen from 'a', 'b', and 'c'
var_dump($rng->getUuid());                       // Returns a random version UUID, e.g.
