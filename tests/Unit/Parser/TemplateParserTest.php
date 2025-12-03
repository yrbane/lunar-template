<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Parser;

use Lunar\Template\Parser\ParsedTemplate;
use Lunar\Template\Parser\ParserInterface;
use Lunar\Template\Parser\TemplateParser;
use PHPUnit\Framework\TestCase;

class TemplateParserTest extends TestCase
{
    private TemplateParser $parser;

    protected function setUp(): void
    {
        $this->parser = new TemplateParser();
    }

    public function testImplementsParserInterface(): void
    {
        $this->assertInstanceOf(ParserInterface::class, $this->parser);
    }

    public function testParseReturnsParsedTemplate(): void
    {
        $result = $this->parser->parse('Hello World');

        $this->assertInstanceOf(ParsedTemplate::class, $result);
    }

    public function testParsePreservesSource(): void
    {
        $source = 'Hello [[ name ]]';
        $result = $this->parser->parse($source);

        $this->assertSame($source, $result->getSource());
    }

    public function testParseExtends(): void
    {
        $source = "[% extends 'base.tpl' %]\n[% block content %]Hello[% endblock %]";
        $result = $this->parser->parse($source);

        $this->assertSame('base.tpl', $result->getExtends());
        $this->assertTrue($result->hasParent());
    }

    public function testParseExtendsDoubleQuotes(): void
    {
        $source = '[% extends "layout.tpl" %]';
        $result = $this->parser->parse($source);

        $this->assertSame('layout.tpl', $result->getExtends());
    }

    public function testParseNoExtends(): void
    {
        $source = 'Hello [[ name ]]';
        $result = $this->parser->parse($source);

        $this->assertNull($result->getExtends());
        $this->assertFalse($result->hasParent());
    }

    public function testParseBlocks(): void
    {
        $source = '[% block header %]<h1>Title</h1>[% endblock %][% block content %]<p>Body</p>[% endblock %]';
        $result = $this->parser->parse($source);

        $this->assertSame(['header' => '<h1>Title</h1>', 'content' => '<p>Body</p>'], $result->getBlocks());
    }

    public function testParseBlocksMultiline(): void
    {
        $source = "[% block content %]\n<p>Line 1</p>\n<p>Line 2</p>\n[% endblock %]";
        $result = $this->parser->parse($source);

        $this->assertSame("\n<p>Line 1</p>\n<p>Line 2</p>\n", $result->getBlock('content'));
    }

    public function testParseNoBlocks(): void
    {
        $source = 'Hello [[ name ]]';
        $result = $this->parser->parse($source);

        $this->assertSame([], $result->getBlocks());
    }

    public function testParseMacros(): void
    {
        $source = '##url("home")## and ##asset("style.css")##';
        $result = $this->parser->parse($source);

        $macros = $result->getMacros();
        $this->assertArrayHasKey('url', $macros);
        $this->assertArrayHasKey('asset', $macros);
        $this->assertSame([['"home"']], $macros['url']);
        $this->assertSame([['"style.css"']], $macros['asset']);
    }

    public function testParseMacrosWithMultipleArguments(): void
    {
        $source = '##url("user.show", userId)##';
        $result = $this->parser->parse($source);

        $macros = $result->getMacros();
        $this->assertSame([['"user.show"', 'userId']], $macros['url']);
    }

    public function testParseMacrosWithNoArguments(): void
    {
        $source = '##currentYear()##';
        $result = $this->parser->parse($source);

        $macros = $result->getMacros();
        $this->assertSame([[]], $macros['currentYear']);
    }

    public function testParseSameMacroMultipleTimes(): void
    {
        $source = '##url("home")## and ##url("about")##';
        $result = $this->parser->parse($source);

        $macros = $result->getMacros();
        $this->assertCount(2, $macros['url']);
        $this->assertSame(['"home"'], $macros['url'][0]);
        $this->assertSame(['"about"'], $macros['url'][1]);
    }

    public function testParseNoMacros(): void
    {
        $source = 'Hello [[ name ]]';
        $result = $this->parser->parse($source);

        $this->assertSame([], $result->getMacros());
    }

    public function testParseComplexTemplate(): void
    {
        $source = <<<'TPL'
            [% extends 'base.tpl' %]

            [% block title %]My Page[% endblock %]

            [% block content %]
            <h1>[[ title ]]</h1>
            <p>Welcome, [[ user.name ]]!</p>
            <a href="##url('home')##">Home</a>
            [% endblock %]
            TPL;

        $result = $this->parser->parse($source);

        $this->assertSame('base.tpl', $result->getExtends());
        $this->assertTrue($result->hasBlock('title'));
        $this->assertTrue($result->hasBlock('content'));
        $this->assertSame('My Page', $result->getBlock('title'));
        $this->assertArrayHasKey('url', $result->getMacros());
    }

    public function testParseEmptyTemplate(): void
    {
        $result = $this->parser->parse('');

        $this->assertSame('', $result->getSource());
        $this->assertSame([], $result->getBlocks());
        $this->assertNull($result->getExtends());
        $this->assertSame([], $result->getMacros());
    }

    public function testParseMacroWithQuotedArgContainingComma(): void
    {
        $source = '##translate("Hello, World")##';
        $result = $this->parser->parse($source);

        $macros = $result->getMacros();
        $this->assertSame([['"Hello, World"']], $macros['translate']);
    }
}
