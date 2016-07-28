# react
[![Latest Stable Version](https://poser.pugx.org/nodejs-php-fallback/react/v/stable.png)](https://packagist.org/packages/nodejs-php-fallback/react)
[![Build Status](https://travis-ci.org/kylekatarnls/react.svg?branch=master)](https://travis-ci.org/kylekatarnls/react)
[![StyleCI](https://styleci.io/repos/64409266/shield?style=flat)](https://styleci.io/repos/64409266)
[![Test Coverage](https://codeclimate.com/github/kylekatarnls/react/badges/coverage.svg)](https://codecov.io/github/kylekatarnls/react?branch=master)
[![Code Climate](https://codeclimate.com/github/kylekatarnls/react/badges/gpa.svg)](https://codeclimate.com/github/kylekatarnls/react)

PHP wrapper to execute babel with react-jsx trasformer plugin or fallback to a PHP alternative.

## Usage

First you need [composer](https://getcomposer.org/) if you have not already. Then get the package with ```composer require nodejs-php-fallback/react``` then require the composer autload in your PHP file if it's not already:
```php
<?php

use NodejsPhpFallback\React;

// Require the composer autload in your PHP file if it's not already.
// You do not need to if you use a framework with composer like Symfony, Laravel, etc.
require 'vendor/autoload.php';

$react = new React('path/to/my-react-file.jsx');

// Output to a file:
$react->write('path/to/my-js-file.js');

// Get JS contents:
$jsContents = $react->getResult();

// Output to the browser:
header('Content-type: text/javascript');
echo $react;

// You can also get react code from a string:
$react = new React('
ReactDOM.render(
    <h1>Hello world!</h1>,
    document.getElementById("main")
);
');
// Then write JS with:
$react->write('path/to/my-js-file.js');
// or get it with:
$jsContents = $react->getResult();

// Get source map contents:
$sourceMap = $react->getSourceMap();

// Get source map path:
$sourceMap = $react->getSourceMapFile();

// Pass false to the React constructor if you do not need source map:
$react = new React('path/to/my-react-file.jsx', false);
```
