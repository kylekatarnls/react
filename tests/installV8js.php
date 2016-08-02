<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    shell_exec('printf "\n" | pecl install -f v8js 2>&1');
    shell_exec('echo "extension=v8js.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini 2>&1');
}
