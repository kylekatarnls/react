<?php

namespace NodejsPhpFallback;

class React extends Wrapper
{
    protected $sourceMap;
    protected $lastFile;

    public function __construct($file, $sourceMap = true)
    {
        $this->sourceMap = $sourceMap;
        parent::__construct($file);
    }

    public function write($file)
    {
        $this->lastFile = $file;
        parent::write($file);
    }

    public function getSourceMapFile()
    {
        if (!$this->lastFile) {
            return;
        }

        return $this->lastFile . '.map';
    }

    public function getSourceMap()
    {
        if (!($file = $this->getSourceMapFile())) {
            return;
        }

        return file_get_contents($file);
    }

    public function compile()
    {
        $path = $this->getPath('source.jsx');
        if (!$this->lastFile) {
            $this->lastFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . preg_replace('/\.jsx$/i', '', basename($path)) . '.js';
        }
        $destination = $this->lastFile;
        $inFile = escapeshellarg($path);
        $outFile = escapeshellarg($destination);
        $appDirectory = NodejsPhpFallback::getPrefixPath();
        $plugins = implode(',', array_map(function ($plugin) use ($appDirectory) {
            return escapeshellarg(implode(DIRECTORY_SEPARATOR, array($appDirectory, 'node_modules', 'babel-plugin-' . $plugin)));
        }, array(
            'transform-es2015-arrow-functions',
            'transform-react-jsx',
        )));
        $presets = implode(',', array_map(function ($preset) use ($appDirectory) {
            return escapeshellarg(implode(DIRECTORY_SEPARATOR, array($appDirectory, 'node_modules', 'babel-preset-' . $preset)));
        }, array(
            // 'es2015',
            'react',
        )));
        $arguments =
            '--presets ' . $presets .
            ' --plugins ' . $plugins . ' ' . $inFile .
            ' --out-file ' . $outFile .
            ' --source-maps --debug' .
            ' 2>&1';
        $output = $this->execModuleScript('babel-cli', 'bin/babel.js', $arguments);
        if (preg_match('/Exception|Error/i', $output)) {
            throw new \ErrorException("Command error: $output", 2);
        }
        if (is_null($output) && file_exists($destination)) {
            $output = file_get_contents($destination);
        }

        return $output;
    }

    public function fallback()
    {
        $fallback = 'ReactJS';
        if (!class_exists($fallback)) {
            throw new \ErrorException("If you can not use the native babel npm package, you have to install the v8js PHP extension (or update it to 0.1.3 or a newer version), then install a ReactJS with the command: composer require reactjs/react-php-v8js '>=2.0.0'", 1);
        }

        $app = file_get_contents(__DIR__ . '/../../lib/react.js') .
            file_get_contents(__DIR__ . '/../../lib/react-dom.js');
        $react = new $fallback($app, $this->getSource());

        return $react->getJS('#main');
    }
}
