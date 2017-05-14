<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    foreach (array(
        'sudo apt-get install php-pear php5-dev libv8-dev libv8-dbg g++ cpp',
        'pecl install v8js-0.1.3',
        'echo "extension=v8js.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini',
    ) as $command) {
        echo "\n$command\n\n";
        echo shell_exec("$command 2>&1") . "\n";
    }
}
