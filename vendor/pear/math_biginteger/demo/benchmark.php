<?php
/*
On a 1.6GHz Pentium M, the following takes about 1.33 seconds with the pure-PHP implementation,
0.66 seconds with BCmath, and 0.001 seconds with GMP.
*/

require(__DIR__.'/../vendor/autoload.php');
//define('MATH_BIGINTEGER_MODE', MATH_BIGINTEGER_MODE_INTERNAL);
//define('MATH_BIGINTEGER_MODE', MATH_BIGINTEGER_MODE_BCMATH);

$a = '0b078d385e9d05d9e029dc9732e75f94f59fdcfb989fe25e81edcb4f93c1dc53a9bb6ba09b5799bd' .
     'aa9e35cd4e00a8200b720d9c6034da9819a5c84e3c7106fcdf5e64c975221bfd9b606bf924bc2971' .
     'de66c470b88221b419ad32e0bff8fb234cbfa0f99e0909d46855a6751b7660b7178f0a661265ad23' .
     '8433331edb99e0ff';
$b = 'a6b9ac382a5f8d394ee83d9e6e21e993c8d240e1';
$c = 'aebbcd9a69b5116ce60400b4126c9e84173635abde4bfa56da904e75d752a51a47d3f088f13299a0' .
     '3b6bf66bf77a6accddeb16fc46a8a32164d7fad2ce4bb159e5caeddb40c25ae02c19e7426bd26398' .
     '14d36ead09509031fc423852c33ff0e6d402b2af825acc03ad6ad234eb5d269c17a026bd37c1b6e2' .
     '4c8c7248d09e12ef';

$a = new Math_BigInteger($a, 16);
$b = new Math_BigInteger($b, 16);
$c = new Math_BigInteger($c, 16);

$start = microtime(true);
$d = $a->modPow($b, $c);
$elapsed = microtime(true) - $start;

echo "took $elapsed seconds\r\n";
echo md5($d->toString()); // should equal aab326a2511ee857e16ce0cdd3243779