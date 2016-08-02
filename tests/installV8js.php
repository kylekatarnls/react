<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    echo "\nprintf \"\\n\" | pecl install -f v8js\n\n";
    echo shell_exec('printf "\n" | pecl install -f v8js 2>&1');
    echo "\necho \"extension=v8js.so\" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini\n\n";
    echo shell_exec('echo "extension=v8js.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini 2>&1');
}
