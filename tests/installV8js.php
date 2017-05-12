<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    foreach (array(
        'sudo apt-get install libv8-dev libv8-dbg g++ cpp',
        'git clone https://github.com/phpv8/v8js.git',
        'cd v8js',
        'make -f Makefile.travis before_install',
        'make -f Makefile.travis install',
        'cd ..',
        'printf "/usr/lib\n" | pecl install -f v8js',
        'echo "extension=v8js.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini',
    ) as $command) {
        echo "\n$command\n\n";
        echo shell_exec("$command 2>&1") . "\n";
    }
}
