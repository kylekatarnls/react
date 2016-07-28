<?php

use NodejsPhpFallback\React;

class ReactTest extends PHPUnit_Framework_TestCase
{
    protected static function simpleJs($value)
    {
        $value = explode("\n", trim(str_replace("\r", '', $value)));
        $indent = null;
        foreach ($value as &$line) {
            if (!preg_match('/^\s+/', $line, $match)) {
                continue;
            }
            if (is_null($indent)) {
                $indent = $match[0];
            }
            $len = strlen($match[0]);
            $line = str_repeat("\t", $len / strlen($indent)) . substr($line, $len);
        }

        return implode("\n", $value);
    }

    public function testCompile()
    {
        $expected = static::simpleJs(file_get_contents(__DIR__ . '/test.js'));
        $react = new React(__DIR__ . '/test.jsx');
        $javascript = static::simpleJs($react->compile());

        $this->assertSame($expected, $javascript, 'React should render JSX with node.');

        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.js';
        $react->write($file);
        $javascript = static::simpleJs(file_get_contents($file));
        $this->assertSame($expected, $javascript, 'React should render JSX with node.');
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 1
     */
    public function testFallback()
    {
        $react = new React(__DIR__ . '/test.jsx');
        $react->fallback();
    }

    public function testGetSourceMapFile()
    {
        $react = new React(__DIR__ . '/test.jsx');
        $this->assertSame(null, $react->getSourceMapFile());

        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.js';
        $react->write($file);

        $this->assertSame($file . '.map', $react->getSourceMapFile());
    }

    public function testGetSourceMap()
    {
        $react = new React(__DIR__ . '/test.jsx');
        $file = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'test.js';
        $react->write($file);
        $expected = json_decode(trim(file_get_contents(__DIR__ . '/test.js.map')));
        $map = json_decode($react->getSourceMap());
        $map = $map->sourcesContent;

        $this->assertSame($expected, $map);
    }
}
