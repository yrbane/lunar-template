<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Parser;

use Lunar\Template\Parser\ParsedTemplate;
use PHPUnit\Framework\TestCase;

class ParsedTemplateTest extends TestCase
{
    public function testGetSource(): void
    {
        $template = new ParsedTemplate('Hello [[ name ]]');

        $this->assertSame('Hello [[ name ]]', $template->getSource());
    }

    public function testGetBlocksEmpty(): void
    {
        $template = new ParsedTemplate('source');

        $this->assertSame([], $template->getBlocks());
    }

    public function testGetBlocksWithContent(): void
    {
        $blocks = ['header' => '<h1>Title</h1>', 'content' => '<p>Body</p>'];
        $template = new ParsedTemplate('source', $blocks);

        $this->assertSame($blocks, $template->getBlocks());
    }

    public function testGetExtends(): void
    {
        $template = new ParsedTemplate('source', [], 'base.tpl');

        $this->assertSame('base.tpl', $template->getExtends());
    }

    public function testGetExtendsNull(): void
    {
        $template = new ParsedTemplate('source');

        $this->assertNull($template->getExtends());
    }

    public function testHasParentTrue(): void
    {
        $template = new ParsedTemplate('source', [], 'base.tpl');

        $this->assertTrue($template->hasParent());
    }

    public function testHasParentFalse(): void
    {
        $template = new ParsedTemplate('source');

        $this->assertFalse($template->hasParent());
    }

    public function testGetBlock(): void
    {
        $template = new ParsedTemplate('source', ['title' => 'Hello']);

        $this->assertSame('Hello', $template->getBlock('title'));
    }

    public function testGetBlockNull(): void
    {
        $template = new ParsedTemplate('source', ['title' => 'Hello']);

        $this->assertNull($template->getBlock('nonexistent'));
    }

    public function testHasBlockTrue(): void
    {
        $template = new ParsedTemplate('source', ['title' => 'Hello']);

        $this->assertTrue($template->hasBlock('title'));
    }

    public function testHasBlockFalse(): void
    {
        $template = new ParsedTemplate('source', ['title' => 'Hello']);

        $this->assertFalse($template->hasBlock('nonexistent'));
    }

    public function testGetMacros(): void
    {
        $macros = ['url' => [['route' => 'home']]];
        $template = new ParsedTemplate('source', [], null, $macros);

        $this->assertSame($macros, $template->getMacros());
    }

    public function testGetMacrosEmpty(): void
    {
        $template = new ParsedTemplate('source');

        $this->assertSame([], $template->getMacros());
    }
}
