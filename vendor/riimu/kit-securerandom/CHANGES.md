# Changelog #

## v1.3.1 (2017-09-09) ##

  * The `ByteNumberGenerator::getNumber()` will now throw an `GeneratorException`
    if the difference between minimum and maximum is not an integer
  * Rely on `unpack()` rather than `hexdec(bin2hex())` due to being less likely
    to be affected by timing vulnerabilities.
  * Require phpcs and php-cs-fixer as external dependencies in the travis build
    instead of including them as dev dependencies

## v1.3.0 (2017-07-14) ##

  * The minimum required PHP version has been increased to 5.6
  * Updated the test suite to work with PHPUnit 6
  * Updated the travis build to test for PHP 7.1
  * Updated to latest coding standards
  * Added `SecureRandom::getRandom()` which returns a random float between 0 and
    1 with more uniform distribution and always less than 1.
  * Added `SecureRandom::getUuid()` which returns a random version 4 UUID.
  * Added `Generator\ByteNumberGenerator` that wraps non `NumberGenerator`
    generators for generating random numbers
  * Improved the bundled autoloader slightly

## v1.2.0 (2016-05-12) ##

  * Added support for PHP's internal CSPRNG in php 7.0 (which is used by default)
  * Added NumberGenerator interface for generators that can natively generate numbers

## v1.1.2 (2015-08-14) ##

  * Address some unlikely corner cases
  * Improve coding standards in some areas of code

## v1.1.1 (2015-01-25) ##

  * Improvements in code quality and documentation
  * Added a simple test for even distribution
  * composer.json now lists openssl and mcrypt as suggested packages instead of
    being listed as requirements

## v1.1.0 (2014-07-17) ##

  * Reading from /dev/urandom now uses buffered reads instead of custom buffer
  * Generators now throw GeneratorException instead of returning false on error
  * Made corrections to some parts of the documentation
  * Zero length sequence from empty choices now returns an empty sequence

## v1.0.0 (2014-07-10) ##

  * Initial release
