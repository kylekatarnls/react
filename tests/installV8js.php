<?php
if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
    shell_exec('pecl install v8js 2>&1');
}
