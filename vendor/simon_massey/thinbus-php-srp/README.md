# Thinbus SRP PHP

Copyright (c) Simon Massey, 2015-2017

[Thinbus SRP PHP](https://bitbucket.org/simon_massey/thinbus-php) is an implementation of the SRP-6a Secure Remote Password protocol. It also includes and ships with [Thinbus](https://bitbucket.org/simon_massey/thinbus-srp-js) JavaScript SRP files. As this PHP package has PHP client and server code you can generate a verifier for a temporary password in PHP and have users login to a PHP server using a browser with the JavaScript. 

This PHP library project is published at [Packagist](https://packagist.org/packages/simon_massey/thinbus-php-srp). There is also a seperate repo that has a demo of how to use this library project at [thinbus-php-srp-demo](https://github.com/simbo1905/thinbus-php-srp-demo). 

**Note** Please read the [Thinbus documentation page](https://bitbucket.org/simon_massey/thinbus-srp-js) before attempting to use this library code as that is the documenation for the JavaScript that runs at the browser which is shipped with this PHP library. Also please try running the demo project at [thinbus-php-srp-demo](https://github.com/simbo1905/thinbus-php-srp-demo) and use browser developer tools to watch the SRP6a protocol run over AJAX. 

**Note** High performance PHP servers have a lot of native extensions by default including php-bcmath. If you are compling your own PHP you may find that Thinbus runs very slow. It uses the official Math_BigInteger class which tries to use a native library such as php-bcmath or php-gmp. If that isn't found it does the very large integer math as vanilla PHP which is too slow to be useful for cryptography. To fix this you need to install one or the other of the fast math php extensions. 

**Note** To use PHP compose you need php extensions such as `dom,mbstring,xml,xmlwriter,zip` (also don't forget `bcmath` for the cryptography math). Those may not be installed on your server by default. You need to install packages such as ` php-zip php-xml php-mbstring` (also don't foget `php-bcmath` for the cryptography math). How you build, install, and enable these is down to how you setup PHP. If you look in `Dockerfile` you can see how I installed and set them up on centos7 for php72. You can also look on the `php5.6` branch docker file to see how I installed them and set them up for debian. Please don't ask me questions about your distro/versions as I used stackoverflow to figure all this out and so can you. 

## Install Dependencies And Run Unit Tests

With php7+ use:

```sh
composer update
vendor/bin/phpunit --verbose test/ThinbusTest.php
```

With php5.6 use:

```sh
tar vxf php56.tar
wget https://phar.phpunit.de/phpunit-5.7.phar
php phpunit-5.7.phar --verbose ThinbusTest.php
```

There is a demo application that uses this library at [https://packagist.org/packages/simon_massey/thinbus-php-srp-demo](https://packagist.org/packages/simon_massey/thinbus-php-srp-demo). That shows that after running `composer update` the thinbus php code is stored under the `vendor` folder which is where the application loads it from. 

## Using In Your Application

The core PHP library files are in the `thinbus` folder:

* `thinbus/thinbus-srp-config.php` SRP configuration global variables. Must be included before the thinbus library code. Must match the values configured in the JavaScript. 
* `thinbus/thinbus-srp.php` PHP port of the Thinbus Java SRP server code based on code by [Ruslan Zavacky](https://github.com/RuslanZavacky/srp-6a-demo).
* `thinbus/thinbus-srp-common.php` common functions used by the client and server. 
* `thinbus/thinbus-srp-client.php` PHP client code contributed by Keith Wagner.     

The core Thinbus JavaScript library files are in the `resources/thinbus` folder: 

* `thinbus/rfc5054-safe-prime-config.js` A sample configuration. See the main thinbus documentation for how to create your own safe prime. 
* `thinbus/thinbus-srp6a-sha256-versioned.js` The thinbus JS library which is tested in the [main project](https://bitbucket.org/simon_massey/thinbus-srp-js). See the header in that file which states the version. 

The file `thinbus\thinbus-srp-config.php` contains the SRP constants which looks something like: 

```
$SRP6CryptoParams = [
    "N_base10" => "19502997308..."
    "g_base10" => "2",
    "k_base16" => "1a3d1769e1d..."
    "H" => "sha256"
];
```

The numeric constants must match the values configured in the JavaScript file `resource\thinbus\rfc5054-safe-prime-config.js`; see the [Thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js). 
Consider creating your own large safe prime values using openssl using the Thinbus instructions. 

You need to understand that:

* Every users has a password verifier and a unique salt that you store in your database. This implementation uses the [RFC2945](https://www.ietf.org/rfc/rfc2945.txt) approach of hashing the username into the password verifier. This means that if your application lets a user change their username then they will be locked out unless you generate and store a fresh password verifier.  
* At every login attempt the browser first makes an AJAX call to get a one-time random challenge and the user salt from the server. The browser then uses that to compute a one-time proof-of-password and then immediately posts the proof-of-password to the server. The server checks the proof-of-password for the one-time challenge using the information stored in the user database. This means the server has to hold the thinbus object that generated the challenge for a short period (either your favourite cache or in your main database). 

The following diagram shows what you need to know: 

![Thinbus SRP Login Diagram](https://camo.githubusercontent.com/d3f3723e01f53e402f7186d157dcefbc215a41f6/687474703a2f2f73696d6f6e6d61737365792e6269746275636b65742e696f2f7468696e6275732f6c6f67696e2d63616368652e706e67 "Thinbus SRP Login Diagram")

It is expected that you create your own code for loading and saving data to a real database. Do not use my demo application's SQLLite or RedBean code. Only use the PHP files at `thinbus\*.php` folder of this repo. It is expected that you use your own code for handling authorisation of which pages users can or cannot access. Trying to modifying the demo files to support your application may be harder than just modifying your current application to simply use the core Thinbus library at `thinbus\*.php`. 

Please read the recommendations in the [main thinbus documentation](https://bitbucket.org/simon_massey/thinbus-srp-js) and take additional steps such as using HTTPS and encrypting the password verifier in the database which are not shown in this demo. 

**Note:** With PHP7 the source of random numbers is now the official [string random_bytes ( int $length )](http://php.net/manual/en/function.random-bytes.php). With PHP5.2+ Thinbus uses the polyfill library [random_compat](https://github.com/paragonie/random_compat). Note that PHP5.6 and PHP7.0 are no longer actively supported but do get security patches through 2018. So you really need to upgrade to PHP7.1+ today to have security patches for the next few years. See http://php.net/supported-versions.php

## Troubleshooting

Note that the [Math_BigInteger](http://phpseclib.sourceforge.net/documentation/math.html) that Thinbus uses to do the crypto math runs very slow (and possibly hangs or possibly gives failing unit tests) unless a native math extension is installed. Usually high performance PHP server installation have a native math exention installed. If you find Thinbus runs slow on your host try installing "bcmath" (or "gmp"). The CI build runs the image created by `Dockerfile` which is Centos7 with PHP7.2 with `php-bcmath` which provides fast (native) and accurate very large integer maths need to run the SRP crypo math fast. (That images is published on hub.docker.com you can see the details in the bitbucket-pipelines.yaml file.)

If you are having problems first check that the PHP unit code runs locally on your workstation using the exact same version of PHP as you run on your server: 

```sh
composer update
vendor/bin/phpunit --verbose ThinbusTest.php
```

If all test pass should output a final line such as `OK (xx tests, yyy assertions)`. If not raise an issue with the verbose output of the phpunit command and the output of `phpinfo();` on your system. 

If you find that the code runs slow in a server it is likely that the `pear/math_biginteger` library cannot find a native implimentation.  It uses the GMP or BCMath extensions, if available, and an internal implementation, otherwise. The fix is to install one of those extensions so that the very large number math is done at native speed rather than scripting speed. 

### Big Thanks

Cross-browser Testing Platform and Open Source <3 Provided by [Sauce Labs][homepage]

Using Sauce Labs the demo app code [thinbus-php-srp-demo](https://packagist.org/packages/simon_massey/thinbus-php-srp-demo) has been tested to work on:

 * Android 6.0 
 * Android 5.1
 * Android 5.0
 * Android 4.4
 * iOS 11.0
 * iOS 8.1
 * Microsoft Edge 15
 * Microsoft Edge 13
 * Microsoft Explorer 11 (note all previous versions were end of life Jan 2016)
 * Chrome 63
 * Chrome 26
 * Firefox 57
 * Firefox 4 (released March 22, 2011!)
 * Safari 11 
 * Safari 7 

[homepage]: https://saucelabs.com

## License

```
   Copyright 2015-2017 Simon Massey

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
```

End.