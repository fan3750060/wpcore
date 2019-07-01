# Secure Remote Protocol

A client and server-side implementation in PHP of the Secure Remote Password (SRP-6a) protocol.

**Want to see a demo in Laravel?** Check out [dalabarge/srp-demo](https://github.com/dalabarge/srp-demo)

## Table of Contents

- [Installation](#installation)
- [Usage Guide](#usage-guide)
- [Running the Tests](#running-the-tests)
- [Licensing](#licensing)

# Installation

The package installs into a PHP application like any other PHP package:

```bash
composer require artisansdk/srp
```

# Usage Guide

The common use cases for this package should be documented including any troubleshooting.

# Running the Tests

The package is unit tested with 100% line coverage and path coverage. You can
run the tests by simply cloning the source, installing the dependencies, and then
running `./vendor/bin/phpunit`. Additionally included in the developer dependencies
are some Composer scripts which can assist with Code Styling and coverage reporting:

```bash
composer test
composer fix
composer report
```

See the `composer.json` for more details on their execution and reporting output.

# Licensing

Copyright (c) 2019 [Artisans Collaborative](https://artisanscollaborative.com)

This package is released under the MIT license. Please see the LICENSE file
distributed with every copy of the code for commercial licensing terms.

Special thanks goes to [simon_massey/thinbus-php-srp](https://bitbucket.org/simon_massey/thinbus-php/src/)
for initial inspiration for both the PHP and JavaScript libraries. This demo
would not be possible without his explanation of the mechanics of Secure Remote
Password protocol.
