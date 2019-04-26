<?php

require_once __DIR__ . '/../vendor/autoload.php';

$rng = new \Riimu\Kit\SecureRandom\SecureRandom();

$timer = microtime(true);
$rng->shuffle(range(0, 100000));
//$rng->getSequence(range(0, 100000), 100000);

echo 'Time: ' . round(microtime(true) - $timer, 2) . ' s' . PHP_EOL;
echo 'Memory: ' . round(memory_get_peak_usage(true) / 1024, 2) . ' kb' . PHP_EOL;
