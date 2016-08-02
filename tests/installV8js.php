<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    foreach (array(
        'printf "\n" | pecl install -f v8js',
        'echo "extension=v8js.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini',
    ) as $command) {
        echo "\n$command\n\n";
        echo shell_exec("$command 2>&1") . "\n";
    }
}
