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
     * @expectedExceptionCode 2
     */
    public function testBadSyntax()
    {
        $react = new React('<!!>Bad<Syntax!');
        $react->compile();
    }

    public function testGoodSyntaxWithErrorWord()
    {
        $expected = static::simpleJs(
            "\"use strict\";\n\n" .
            "/** @jsx dom */\n\n" .
            "var dom = React.createElement;\n" .
            "ReactDOM.render(dom(\"div\", { error: foo.getException() }), document.getElementById(\"map\"));"
        );
        $react = new React(
            "\"use strict\";\n\n" .
            "/** @jsx dom */\n" .
            "var dom = React.createElement;\n" .
            "ReactDOM.render(\n" .
            "    <div error={foo.getException()} />,\n" .
            "    document.getElementById(\"map\")\n" .
            ");"
        );
        $javascript = static::simpleJs($react->compile());
        $javascript = explode('//# sourceMappingURL=', $javascript);
        $javascript = trim($javascript[0]);

        $this->assertSame($expected, $javascript, 'React should render code with error word.');
    }

    public function testGetSourceMapFile()
    {
        $react = new React(__DIR__ . '/test.jsx');
        $this->assertSame(null, $react->getSourceMapFile());
        $this->assertSame(null, $react->getSourceMap());

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
        $expected = static::simpleJs($expected[0]);
        $map = json_decode($react->getSourceMap());
        $map = $map->sourcesContent;
        $map = static::simpleJs($map[0]);

        $this->assertSame($expected, $map);
    }

    public function testLambda()
    {
        $react = new React('_ => 5');
        $actual = static::simpleJs($react->compile());
        $expected = '/^"use\sstrict";\s+\(function\s+\(_\)\s+\{\s+return\s+5;\s+\}\)/';

        $this->assertRegExp($expected, $actual);
    }

    /**
     * @expectedException \ErrorException
     * @expectedExceptionCode 1
     */
    public function testFallbackFailure()
    {
        $react = new React(__DIR__ . '/test.jsx');
        $react->fallback();
    }

    public function testFallbackSuccess()
    {
        if (version_compare(PHP_VERSION, '7.0.0') < 0 || !extension_loaded('v8js')) {
            return $this->markTestSkipped('This test can be done only with PHP >= 7 and ext-v8js installed.');
        }
        shell_exec('composer require reactjs/react-php-v8js ">=2.0.0" 2>&1');
        $reactFile = __DIR__ . '/../vendor/reactjs/react-php-v8js/ReactJS.php';
        if (!file_exists($reactFile)) {
            $reactFile = __DIR__ . '/../../../vendor/reactjs/react-php-v8js/ReactJS.php';
        }
        if (!file_exists($reactFile)) {
            throw new \ErrorException('reactjs/react-php-v8js installation failed.', 1);
        }
        include_once $reactFile;
        $expected = static::simpleJs(file_get_contents(__DIR__ . '/test.js'));
        $react = new React(__DIR__ . '/test.jsx');
        $javascript = $react->fallback();
        $this->assertSame($expected, $javascript, 'React should render JSX without node.');
    }
}
