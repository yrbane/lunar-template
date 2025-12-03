<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\String\CapitalizeFilter;
use Lunar\Template\Filter\String\ExcerptFilter;
use Lunar\Template\Filter\String\LowerFilter;
use Lunar\Template\Filter\String\LtrimFilter;
use Lunar\Template\Filter\String\PadLeftFilter;
use Lunar\Template\Filter\String\PadRightFilter;
use Lunar\Template\Filter\String\RepeatFilter;
use Lunar\Template\Filter\String\ReplaceFilter;
use Lunar\Template\Filter\String\ReverseFilter;
use Lunar\Template\Filter\String\RtrimFilter;
use Lunar\Template\Filter\String\SlugFilter;
use Lunar\Template\Filter\String\SplitFilter;
use Lunar\Template\Filter\String\TitleFilter;
use Lunar\Template\Filter\String\TrimFilter;
use Lunar\Template\Filter\String\TruncateFilter;
use Lunar\Template\Filter\String\UpperFilter;
use Lunar\Template\Filter\String\WordwrapFilter;
use PHPUnit\Framework\TestCase;

class StringFiltersTest extends TestCase
{
    public function testUpperFilter(): void
    {
        $filter = new UpperFilter();

        $this->assertSame('upper', $filter->getName());
        $this->assertSame('HELLO WORLD', $filter->apply('hello world'));
        $this->assertSame('CAFÉ', $filter->apply('café'));
        $this->assertSame('123', $filter->apply(123));
        $this->assertSame('', $filter->apply(null));
    }

    public function testLowerFilter(): void
    {
        $filter = new LowerFilter();

        $this->assertSame('lower', $filter->getName());
        $this->assertSame('hello world', $filter->apply('HELLO WORLD'));
        $this->assertSame('café', $filter->apply('CAFÉ'));
    }

    public function testCapitalizeFilter(): void
    {
        $filter = new CapitalizeFilter();

        $this->assertSame('capitalize', $filter->getName());
        $this->assertSame('Hello world', $filter->apply('hello world'));
        $this->assertSame('Hello world', $filter->apply('HELLO WORLD'));
        $this->assertSame('', $filter->apply(''));
    }

    public function testTitleFilter(): void
    {
        $filter = new TitleFilter();

        $this->assertSame('title', $filter->getName());
        $this->assertSame('Hello World', $filter->apply('hello world'));
        $this->assertSame('The Quick Brown Fox', $filter->apply('THE QUICK BROWN FOX'));
    }

    public function testTrimFilter(): void
    {
        $filter = new TrimFilter();

        $this->assertSame('trim', $filter->getName());
        $this->assertSame('hello', $filter->apply('  hello  '));
        $this->assertSame('hello', $filter->apply('xxhelloxx', ['x']));
    }

    public function testLtrimFilter(): void
    {
        $filter = new LtrimFilter();

        $this->assertSame('ltrim', $filter->getName());
        $this->assertSame('hello  ', $filter->apply('  hello  '));
        $this->assertSame('helloxx', $filter->apply('xxhelloxx', ['x']));
    }

    public function testRtrimFilter(): void
    {
        $filter = new RtrimFilter();

        $this->assertSame('rtrim', $filter->getName());
        $this->assertSame('  hello', $filter->apply('  hello  '));
        $this->assertSame('xxhello', $filter->apply('xxhelloxx', ['x']));
    }

    public function testSlugFilter(): void
    {
        $filter = new SlugFilter();

        $this->assertSame('slug', $filter->getName());
        $this->assertSame('hello-world', $filter->apply('Hello World'));
        $this->assertSame('cafe-francais', $filter->apply('Café Français'));
        $this->assertSame('hello_world', $filter->apply('Hello World', ['_']));
    }

    public function testTruncateFilter(): void
    {
        $filter = new TruncateFilter();

        $this->assertSame('truncate', $filter->getName());
        $this->assertSame('Hello...', $filter->apply('Hello World', [5]));
        $this->assertSame('Hello--', $filter->apply('Hello World', [5, '--']));
        $this->assertSame('Short', $filter->apply('Short', [10]));
    }

    public function testWordwrapFilter(): void
    {
        $filter = new WordwrapFilter();

        $this->assertSame('wordwrap', $filter->getName());
        $result = $filter->apply('Hello World Test', [5, "\n"]);
        $this->assertStringContainsString("\n", $result);
    }

    public function testReverseFilter(): void
    {
        $filter = new ReverseFilter();

        $this->assertSame('reverse', $filter->getName());
        $this->assertSame('dlrow', $filter->apply('world'));
        $this->assertSame([3, 2, 1], $filter->apply([1, 2, 3]));
    }

    public function testRepeatFilter(): void
    {
        $filter = new RepeatFilter();

        $this->assertSame('repeat', $filter->getName());
        $this->assertSame('abcabcabc', $filter->apply('abc', [3]));
        $this->assertSame('', $filter->apply('abc', [0]));
        $this->assertSame('', $filter->apply('abc', [-1]));
    }

    public function testPadLeftFilter(): void
    {
        $filter = new PadLeftFilter();

        $this->assertSame('pad_left', $filter->getName());
        $this->assertSame('00042', $filter->apply('42', [5, '0']));
        $this->assertSame('   42', $filter->apply('42', [5]));
    }

    public function testPadRightFilter(): void
    {
        $filter = new PadRightFilter();

        $this->assertSame('pad_right', $filter->getName());
        $this->assertSame('42000', $filter->apply('42', [5, '0']));
        $this->assertSame('42   ', $filter->apply('42', [5]));
    }

    public function testReplaceFilter(): void
    {
        $filter = new ReplaceFilter();

        $this->assertSame('replace', $filter->getName());
        $this->assertSame('Hello Universe', $filter->apply('Hello World', ['World', 'Universe']));
    }

    public function testSplitFilter(): void
    {
        $filter = new SplitFilter();

        $this->assertSame('split', $filter->getName());
        $this->assertSame(['a', 'b', 'c'], $filter->apply('a,b,c', [',']));
        $this->assertSame(['a', 'b,c'], $filter->apply('a,b,c', [',', 2]));
        $this->assertSame(['a', 'b', 'c'], $filter->apply('abc', ['']));
    }

    public function testExcerptFilter(): void
    {
        $filter = new ExcerptFilter();

        $this->assertSame('excerpt', $filter->getName());

        $text = '<p>This is a long paragraph with some <strong>HTML</strong> content.</p>';
        $result = $filter->apply($text, [20]);

        $this->assertStringNotContainsString('<p>', $result);
        $this->assertStringNotContainsString('<strong>', $result);
        $this->assertStringEndsWith('...', $result);
    }

    public function testExcerptShortText(): void
    {
        $filter = new ExcerptFilter();
        $this->assertSame('Short text', $filter->apply('Short text', [100]));
    }
}
