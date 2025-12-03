<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Security;

use Lunar\Template\Security\EscaperInterface;
use Lunar\Template\Security\HtmlEscaper;
use PHPUnit\Framework\TestCase;
use stdClass;

class HtmlEscaperTest extends TestCase
{
    private HtmlEscaper $escaper;

    protected function setUp(): void
    {
        $this->escaper = new HtmlEscaper();
    }

    public function testImplementsEscaperInterface(): void
    {
        $this->assertInstanceOf(EscaperInterface::class, $this->escaper);
    }

    public function testEscapeString(): void
    {
        $result = $this->escaper->escape('<script>alert("xss")</script>');

        $this->assertSame('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', $result);
    }

    public function testEscapeHtmlEntities(): void
    {
        $result = $this->escaper->escape('Tom & Jerry < Rock & Roll');

        $this->assertSame('Tom &amp; Jerry &lt; Rock &amp; Roll', $result);
    }

    public function testEscapeQuotes(): void
    {
        $result = $this->escaper->escape("Single 'quotes' and double \"quotes\"");

        $this->assertSame('Single &#039;quotes&#039; and double &quot;quotes&quot;', $result);
    }

    public function testEscapeInteger(): void
    {
        $result = $this->escaper->escape(42);

        $this->assertSame('42', $result);
    }

    public function testEscapeFloat(): void
    {
        $result = $this->escaper->escape(3.14159);

        $this->assertSame('3.14159', $result);
    }

    public function testEscapeBoolean(): void
    {
        $this->assertSame('1', $this->escaper->escape(true));
        $this->assertSame('', $this->escaper->escape(false));
    }

    public function testEscapeNull(): void
    {
        $result = $this->escaper->escape(null);

        $this->assertSame('', $result);
    }

    public function testEscapeArray(): void
    {
        $result = $this->escaper->escape(['a', 'b']);

        $this->assertSame('Array', $result);
    }

    public function testEscapeObjectWithToString(): void
    {
        $object = new class () {
            public function __toString(): string
            {
                return '<b>bold</b>';
            }
        };

        $result = $this->escaper->escape($object);

        $this->assertSame('&lt;b&gt;bold&lt;/b&gt;', $result);
    }

    public function testEscapeObjectWithoutToString(): void
    {
        $object = new stdClass();

        $result = $this->escaper->escape($object);

        $this->assertSame('Object', $result);
    }

    public function testGetStrategy(): void
    {
        $this->assertSame('html', $this->escaper->getStrategy());
    }

    public function testEscapeEmptyString(): void
    {
        $result = $this->escaper->escape('');

        $this->assertSame('', $result);
    }

    public function testEscapePreservesUtf8(): void
    {
        $result = $this->escaper->escape('Héllo Wörld 中文 🚀');

        $this->assertSame('Héllo Wörld 中文 🚀', $result);
    }

    public function testEscapeAlreadyEscaped(): void
    {
        // Should double-escape to prevent XSS via pre-escaped content
        $result = $this->escaper->escape('&lt;script&gt;');

        $this->assertSame('&amp;lt;script&amp;gt;', $result);
    }

    public function testCustomCharset(): void
    {
        $escaper = new HtmlEscaper('ISO-8859-1');

        $this->assertSame('html', $escaper->getStrategy());
    }
}
