<?php

require_once('vendor/autoload.php');

$N = new Math_BigInteger('00a7ff43e28003cb6f1dc7ed7a7f0da9683ae99eef2683f31abd31804e20fc62504103f92d8e3f683c64a07bbdc5bbf618b00679c4829c3ea638c8e1d337cfa0c945fac851b4731e860dd8fb1991b752b9d35b7a0d47ceb9ee812fed33e4c230738f28495cd21f5abbadc9be365842caeb46000578e265577195ad88d1d133f0a8324dcee0eaa86db99fffc59bd5ac97b7fa84414d83b0d13164bdfdce62a70310d22ff972d02c64ff2c6f40b78260481b4d81a7361e3db752bebcceab0625cad1fe5d2e2eda63f61758fb12b3f59feefc0f86d4be3bd1d6d0ec57918e8a9d0e83deaf0a22bed8d5edf823a5c1095d14b7b52c98108b09773e90b5aca29c50445d', 16);
$g = new Math_BigInteger('2 (0x2)', 10);
$length = strlen($N->toBits());
$data = str_pad($N->toBits(), $length, '0', STR_PAD_LEFT);
$data .= str_pad($g->toBits(), $length, '0', STR_PAD_LEFT);
$k = new Math_BigInteger(hash('sha256', $data), 16);

$config = json_encode([
	'N_base10' => $N->toString(),
        'g_base10' => $g->toString(),
        'k_base16' => $k->toHex(),
        'H' => 'sha256',
], JSON_PRETTY_PRINT);

echo $config.PHP_EOL;
