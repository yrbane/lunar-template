<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Html\EscapeFilter;
use Lunar\Template\Filter\Html\Nl2brFilter;
use Lunar\Template\Filter\Html\RawFilter;
use Lunar\Template\Filter\Html\SpacelessFilter;
use Lunar\Template\Filter\Html\StripTagsFilter;
use PHPUnit\Framework\TestCase;

class HtmlFiltersTest extends TestCase
{
    public function testRawFilter(): void
    {
        $filter = new RawFilter();

        $this->assertSame('raw', $filter->getName());
        $this->assertSame('<script>alert("xss")</script>', $filter->apply('<script>alert("xss")</script>'));
    }

    public function testEscapeFilter(): void
    {
        $filter = new EscapeFilter();

        $this->assertSame('escape', $filter->getName());
        $this->assertSame('&lt;script&gt;', $filter->apply('<script>'));
        $this->assertSame('&lt;script&gt;', $filter->apply('<script>', ['html']));
        $this->assertSame('&lt;script&gt;', $filter->apply('<script>', ['attr']));
    }

    public function testEscapeFilterJs(): void
    {
        $filter = new EscapeFilter();

        $result = $filter->apply('alert("test")', ['js']);
        $this->assertStringNotContainsString('"', $result);
    }

    public function testEscapeFilterCss(): void
    {
        $filter = new EscapeFilter();

        $result = $filter->apply('color: red;', ['css']);
        $this->assertStringNotContainsString(':', $result);
    }

    public function testEscapeFilterUrl(): void
    {
        $filter = new EscapeFilter();

        $result = $filter->apply('hello world', ['url']);
        $this->assertSame('hello%20world', $result);
    }

    public function testStripTagsFilter(): void
    {
        $filter = new StripTagsFilter();

        $this->assertSame('striptags', $filter->getName());
        $this->assertSame('Hello World', $filter->apply('<p>Hello <strong>World</strong></p>'));
        $this->assertSame('<p>Hello World</p>', $filter->apply('<p>Hello <strong>World</strong></p>', ['<p>']));
        $this->assertSame('<p>Hello World</p>', $filter->apply('<p>Hello <strong>World</strong></p>', [['p']]));
    }

    public function testNl2brFilter(): void
    {
        $filter = new Nl2brFilter();

        $this->assertSame('nl2br', $filter->getName());
        $this->assertSame("Hello<br />\nWorld", $filter->apply("Hello\nWorld"));
        $this->assertSame("Hello<br>\nWorld", $filter->apply("Hello\nWorld", [false]));
    }

    public function testSpacelessFilter(): void
    {
        $filter = new SpacelessFilter();

        $this->assertSame('spaceless', $filter->getName());

        $html = '<div>   <span>   Hello   </span>   </div>';
        $result = $filter->apply($html);

        $this->assertSame('<div><span>   Hello   </span></div>', $result);
    }
}
