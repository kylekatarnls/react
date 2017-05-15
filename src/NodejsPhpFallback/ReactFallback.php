<?php

namespace NodejsPhpFallback;

use SimpleXMLIterator;

class ReactFallback
{
    protected $source;

    protected $createElement = 'React.createElement';

    protected $validXmlTagNameStartCharacters = 'A-Za-z\\p{L}_:';

    protected $validXmlTagNameOtherCharacters = '0-9\\p{L}\\.\\-';

    protected $attributesPattern = '(?:[^>"\']|"(?:\\\\[\\s\\S]|[^"\\\\])*"|\'(?:\\\\[\\s\\S]|[^\'\\\\])*\')*';

    protected $htmlTags = array(
        "a", "abbr", "address", "area", "article", "aside", "audio", "b",
        "base", "bdi", "bdo", "big", "blockquote", "body", "br", "button",
        "canvas", "caption", "cite", "code", "col", "colgroup", "data",
        "datalist", "dd", "del", "details", "dfn", "dialog", "div", "dl",
        "dt", "em", "embed", "fieldset", "figcaption", "figure", "footer",
        "form", "h1", "h2", "h3", "h4", "h5", "h6", "head", "header",
        "hgroup", "hr", "html", "i", "iframe", "img", "input", "ins", "kbd",
        "keygen", "label", "legend", "li", "link", "main", "map", "mark",
        "menu", "menuitem", "meta", "meter", "nav", "noscript", "object",
        "ol", "optgroup", "option", "output", "p", "param", "picture",
        "pre", "progress", "q", "rp", "rt", "ruby", "s", "samp", "script",
        "section", "select", "small", "source", "span", "strong", "style",
        "sub", "summary", "sup", "table", "tbody", "td", "textarea",
        "tfoot", "th", "thead", "time", "title", "tr", "track", "u", "ul",
        "var", "video", "wbr", "circle", "clipPath", "defs", "ellipse",
        "g", "image", "line", "linearGradient", "mask", "path", "pattern",
        "polygon", "polyline", "radialGradient", "rect", "stop", "svg",
        "text", "tspan",
    );

    public function __construct($source)
    {
        $this->source = $source;
    }

    protected function startWithTag($buffer, &$matches)
    {
        $start = '[' . $this->validXmlTagNameStartCharacters . ']';
        $middle = '[' . $this->validXmlTagNameStartCharacters . $this->validXmlTagNameOtherCharacters . ']';
        if (preg_match('/^<(' . $start . $middle . '*)(\s|\\/|>)/', $buffer, $tagMatches)) {
            $tagName = preg_quote($tagMatches[1], '/');
            if (
                preg_match('/^<' . $tagName . $this->attributesPattern . '\\/>/', $buffer, $matches) ||
                preg_match('/^<' . $tagName . $this->attributesPattern . '>((?>(?R)|[\\s\\S])*)<\\/' . $tagName . '>/U', $buffer, $matches)
            ) {
                return true;
            }
        }

        return false;
    }

    protected function digestArgument(&$args, &$arg)
    {
        if ($arg !== '') {
            $args[] = json_encode($arg);
        }

        $arg = '';
    }

    protected function elementToJs($element, $content, $indent = 0)
    {
        $name = $element->getName();
        if (in_array($name, $this->htmlTags)) {
            $name = json_encode($name);
        }

        $attributes = array();
        foreach ($element->attributes() as $key => $value) {
            $attributes[$key] = is_array($value) || is_object($value)
                ? end($value)
                : $value;
        }

        $args = array(
            $name,
            empty($attributes) ? 'null' : json_encode($attributes)
        );
        $spaces = str_repeat(' ', $indent * 2);

        $arg = '';
        while (mb_strlen($content)) {
            if ($this->startWithTag($content, $matches)) {
                $this->digestArgument($args, $arg);
                $xml = simplexml_load_string($matches[0]);
                $args[] = $this->elementToJs($xml, isset($matches[1]) ? $matches[1] : '', $indent + 1);
                $content = mb_substr($content, mb_strlen($matches[0]));
                continue;
            }

            $arg .= mb_substr($content, 0, 1);
            $content = mb_substr($content, 1);
        }

        $this->digestArgument($args, $arg);
        $childIndent = "\n" . str_repeat(' ', $indent * 2 + 2);
        $indent = "\n" . str_repeat(' ', $indent * 2);
        if (count($args) === 2) {
            $indent = '';
            $childIndent = '';
        }

        return $this->createElement . '(' . $childIndent .
            implode(
                ',' . $childIndent,
                $args
            ) . $indent .
        ')';
    }

    public function parseJsx()
    {
        $this->result = '';
        $this->buffer = $this->source;
        while (mb_strlen($this->buffer)) {
            $input = mb_substr($this->buffer, 0, 1);
            $consume = null;
            if (
                preg_match('/^\/\*([\s\S]+)\*\//U', $this->buffer, $matches) ||
                preg_match('/^\/\/(\N+)\n/U', $this->buffer, $matches)
            ) {
                if (preg_match('/@jsx\s+(\S+)/', $matches[1], $commentMatches)) {
                    $this->createElement = $commentMatches[1];
                }
                $input = $matches[0];
            }
            if (preg_match('/^("(?:\\\\[\\s\\S]|[^"\\\\])*"|\'(?:\\\\[\\s\\S]|[^\'\\\\])*\')/', $this->buffer, $matches)) {
                $input = $matches[0];
            }
            if ($this->startWithTag($this->buffer, $matches)) {
                $xml = simplexml_load_string($matches[0]);
                $consume = mb_strlen($matches[0]);
                $input = $this->elementToJs($xml, isset($matches[1]) ? $matches[1] : '');
            }
            $this->result .= $input;
            $this->buffer = mb_substr($this->buffer, is_null($consume) ? mb_strlen($input) : $consume);
        }

        return '"use strict";' . "\n\n" . $this->result;
    }
}
