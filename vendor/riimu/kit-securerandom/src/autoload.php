<?php

namespace Riimu\Kit\SecureRandom;

// Bundled autoloader provided as an optional alternative to composer autoloader
spl_autoload_register(function ($class) {
    if (strncmp($class, __NAMESPACE__, strlen(__NAMESPACE__)) === 0) {
        $path = __DIR__ . strtr(substr($class, strlen(__NAMESPACE__)), ['\\' => DIRECTORY_SEPARATOR]) . '.php';

        if (file_exists($path)) {
            require $path;
        }
    }
});
