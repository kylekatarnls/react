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
        $react = static::simpleJs($react->compile());

        $this->assertSame($expected, $react, 'React should render JSX with node.');
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
}
