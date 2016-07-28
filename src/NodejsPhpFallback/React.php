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
        $logFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'error.log';
        $appDirectory = NodejsPhpFallback::getPrefixPath();
        $transform = implode(DIRECTORY_SEPARATOR, array($appDirectory, 'node_modules', 'babel-plugin-transform-react-jsx'));
        $transform = escapeshellarg($transform);
        $preset = implode(DIRECTORY_SEPARATOR, array($appDirectory, 'node_modules', 'babel-preset-react'));
        $preset = escapeshellarg($preset);
        $arguments = '--presets ' . $preset . ' --plugins ' . $transform . ' ' . $inFile . '  --out-file ' . $outFile . ' --source-maps --debug';
        $output = $this->execModuleScript('babel-cli', 'bin/babel.js', $arguments);
        if (is_null($output)) {
            $output = file_get_contents($destination);
        }
        if (preg_match('/error|exception/i', $output)) {
            throw new \ErrorException("Command failure\n$input\n$output", 1);
        }
        if (file_exists($logFile)) {
            $error = file_get_contents($logFile);
            unlink($logFile);
            if (!empty($error)) {
                throw new \ErrorException("Command: $input\nOutput: $error", 2);
            }
        }

        return $output;
    }

    public function fallback()
    {
        $fallback = 'ReactJS';
        if (!class_exists($fallback)) {
            throw new \ErrorException("If you can not use the native babel npm package, you have to install the v8js PHP extension (or update it to 0.1.3 or a newer version), then install a ReactJS with the command: composer require reactjs/react-php-v8js >=2.0.0", 1);
        }

        $app = file_get_contents(__DIR__ . '/../../lib/react.js') .
            file_get_contents(__DIR__ . '/../../lib/react-dom.js');
        $react = new $fallback($app, $this->getSource());

        return $react->getJS('#main');
    }
}
