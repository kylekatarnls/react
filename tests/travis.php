<?php

if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    chdir(dirname(__DIR__));
    switch ($argv[1]) {
        case 'before_install':
            $commands = array(
                'make -f Makefile.travis before_install',
            );
            break;
        case 'install':
            $commands = array(
                'make -f Makefile.travis install',
            );
            break;
        case 'after_script':
            $commands = array(
                'vendor/bin/test-reporter --coverage-report coverage.xml',
            );
            break;
        case 'after_success':
            $commands = array(
                'bash <(curl -s https://codecov.io/bash)',
            );
            break;
    }
    foreach ($commands as $command) {
        echo "\n$command\n\n";
        echo shell_exec("$command 2>&1") . "\n";
    }
}
