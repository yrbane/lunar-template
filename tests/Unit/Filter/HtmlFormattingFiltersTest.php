<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Html\AnchorFilter;
use Lunar\Template\Filter\Html\AttributesFilter;
use Lunar\Template\Filter\Html\ClassListFilter;
use Lunar\Template\Filter\Html\ExcerptHtmlFilter;
use Lunar\Template\Filter\Html\HeadingFilter;
use Lunar\Template\Filter\Html\HighlightFilter;
use Lunar\Template\Filter\Html\LinkifyFilter;
use Lunar\Template\Filter\Html\ListHtmlFilter;
use Lunar\Template\Filter\Html\MarkdownFilter;
use Lunar\Template\Filter\Html\ParagraphFilter;
use Lunar\Template\Filter\Html\TableFilter;
use Lunar\Template\Filter\Html\WrapFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(MarkdownFilter::class)]
#[CoversClass(LinkifyFilter::class)]
#[CoversClass(ListHtmlFilter::class)]
#[CoversClass(TableFilter::class)]
#[CoversClass(AttributesFilter::class)]
#[CoversClass(WrapFilter::class)]
#[CoversClass(HighlightFilter::class)]
#[CoversClass(ParagraphFilter::class)]
#[CoversClass(HeadingFilter::class)]
#[CoversClass(AnchorFilter::class)]
#[CoversClass(ExcerptHtmlFilter::class)]
#[CoversClass(ClassListFilter::class)]
final class HtmlFormattingFiltersTest extends TestCase
{
    // ==================== MarkdownFilter Tests ====================

    public function testMarkdownFilterName(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('markdown', $filter->getName());
    }

    public function testMarkdownBold(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<strong>bold</strong>', $filter->apply('**bold**'));
        $this->assertSame('<strong>bold</strong>', $filter->apply('__bold__'));
    }

    public function testMarkdownItalic(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<em>italic</em>', $filter->apply('*italic*'));
        $this->assertSame('<em>italic</em>', $filter->apply('_italic_'));
    }

    public function testMarkdownStrikethrough(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<del>strikethrough</del>', $filter->apply('~~strikethrough~~'));
    }

    public function testMarkdownInlineCode(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<code>code</code>', $filter->apply('`code`'));
    }

    public function testMarkdownCodeBlock(): void
    {
        $filter = new MarkdownFilter();
        $input = "```php\necho 'Hello';\n```";
        $expected = "<pre><code class=\"language-php\">echo 'Hello';\n</code></pre>";
        $this->assertSame($expected, $filter->apply($input));
    }

    public function testMarkdownLinks(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<a href="https://example.com">Link</a>', $filter->apply('[Link](https://example.com)'));
    }

    public function testMarkdownImages(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<img src="image.jpg" alt="Alt text">', $filter->apply('![Alt text](image.jpg)'));
    }

    public function testMarkdownHeaders(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<h1>Header 1</h1>', $filter->apply('# Header 1'));
        $this->assertSame('<h2>Header 2</h2>', $filter->apply('## Header 2'));
        $this->assertSame('<h3>Header 3</h3>', $filter->apply('### Header 3'));
        $this->assertSame('<h4>Header 4</h4>', $filter->apply('#### Header 4'));
        $this->assertSame('<h5>Header 5</h5>', $filter->apply('##### Header 5'));
        $this->assertSame('<h6>Header 6</h6>', $filter->apply('###### Header 6'));
    }

    public function testMarkdownBlockquote(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<blockquote>Quote</blockquote>', $filter->apply('> Quote'));
    }

    public function testMarkdownHorizontalRule(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('<hr>', $filter->apply('---'));
        $this->assertSame('<hr>', $filter->apply('***'));
        $this->assertSame('<hr>', $filter->apply('___'));
    }

    public function testMarkdownUnorderedList(): void
    {
        $filter = new MarkdownFilter();
        $input = "- Item 1\n- Item 2\n- Item 3";
        $expected = '<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>';
        $this->assertSame($expected, $filter->apply($input));
    }

    public function testMarkdownOrderedList(): void
    {
        $filter = new MarkdownFilter();
        $input = "1. Item 1\n2. Item 2\n3. Item 3";
        $expected = '<ol><li>Item 1</li><li>Item 2</li><li>Item 3</li></ol>';
        $this->assertSame($expected, $filter->apply($input));
    }

    public function testMarkdownWithEmptyString(): void
    {
        $filter = new MarkdownFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== LinkifyFilter Tests ====================

    public function testLinkifyFilterName(): void
    {
        $filter = new LinkifyFilter();
        $this->assertSame('linkify', $filter->getName());
    }

    public function testLinkifyUrls(): void
    {
        $filter = new LinkifyFilter();
        $result = $filter->apply('Visit https://example.com for more');
        $this->assertStringContainsString('<a href="https://example.com"', $result);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }

    public function testLinkifyEmails(): void
    {
        $filter = new LinkifyFilter();
        $result = $filter->apply('Contact us at test@example.com');
        $this->assertStringContainsString('<a href="mailto:test@example.com"', $result);
        $this->assertStringContainsString('>test@example.com</a>', $result);
    }

    public function testLinkifyWithCustomTarget(): void
    {
        $filter = new LinkifyFilter();
        $result = $filter->apply('Visit https://example.com', ['_self']);
        $this->assertStringContainsString('target="_self"', $result);
        $this->assertStringNotContainsString('noopener', $result);
    }

    public function testLinkifyWithNoTarget(): void
    {
        $filter = new LinkifyFilter();
        $result = $filter->apply('Visit https://example.com', ['']);
        $this->assertStringNotContainsString('target=', $result);
    }

    public function testLinkifyEmptyString(): void
    {
        $filter = new LinkifyFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== ListHtmlFilter Tests ====================

    public function testListHtmlFilterName(): void
    {
        $filter = new ListHtmlFilter();
        $this->assertSame('list', $filter->getName());
    }

    public function testListHtmlUnordered(): void
    {
        $filter = new ListHtmlFilter();
        $result = $filter->apply(['Item 1', 'Item 2', 'Item 3']);
        $this->assertSame('<ul><li>Item 1</li><li>Item 2</li><li>Item 3</li></ul>', $result);
    }

    public function testListHtmlOrdered(): void
    {
        $filter = new ListHtmlFilter();
        $result = $filter->apply(['Item 1', 'Item 2'], ['ol']);
        $this->assertSame('<ol><li>Item 1</li><li>Item 2</li></ol>', $result);
    }

    public function testListHtmlWithClass(): void
    {
        $filter = new ListHtmlFilter();
        $result = $filter->apply(['Item'], ['ul', 'my-list']);
        $this->assertSame('<ul class="my-list"><li>Item</li></ul>', $result);
    }

    public function testListHtmlWithNumbers(): void
    {
        $filter = new ListHtmlFilter();
        $result = $filter->apply([1, 2, 3]);
        $this->assertSame('<ul><li>1</li><li>2</li><li>3</li></ul>', $result);
    }

    public function testListHtmlEmptyArray(): void
    {
        $filter = new ListHtmlFilter();
        $this->assertSame('', $filter->apply([]));
    }

    public function testListHtmlNonArray(): void
    {
        $filter = new ListHtmlFilter();
        $this->assertSame('', $filter->apply('not an array'));
    }

    public function testListHtmlInvalidType(): void
    {
        $filter = new ListHtmlFilter();
        $result = $filter->apply(['Item'], ['invalid']);
        $this->assertStringContainsString('<ul>', $result);
    }

    // ==================== TableFilter Tests ====================

    public function testTableFilterName(): void
    {
        $filter = new TableFilter();
        $this->assertSame('table', $filter->getName());
    }

    public function testTableBasic(): void
    {
        $filter = new TableFilter();
        $data = [
            ['A', 'B'],
            ['C', 'D'],
        ];
        $result = $filter->apply($data);
        $this->assertStringContainsString('<table>', $result);
        $this->assertStringContainsString('<tr><td>A</td><td>B</td></tr>', $result);
        $this->assertStringContainsString('<tr><td>C</td><td>D</td></tr>', $result);
    }

    public function testTableWithHeader(): void
    {
        $filter = new TableFilter();
        $data = [
            ['Name', 'Age'],
            ['John', '30'],
        ];
        $result = $filter->apply($data, [true]);
        $this->assertStringContainsString('<thead>', $result);
        $this->assertStringContainsString('<th>Name</th><th>Age</th>', $result);
        $this->assertStringContainsString('<tbody>', $result);
        $this->assertStringContainsString('<td>John</td><td>30</td>', $result);
    }

    public function testTableWithClass(): void
    {
        $filter = new TableFilter();
        $result = $filter->apply([['A']], [false, 'my-table']);
        $this->assertStringContainsString('<table class="my-table">', $result);
    }

    public function testTableEmptyArray(): void
    {
        $filter = new TableFilter();
        $this->assertSame('', $filter->apply([]));
    }

    public function testTableNonArray(): void
    {
        $filter = new TableFilter();
        $this->assertSame('', $filter->apply('not an array'));
    }

    public function testTableWithScalarRow(): void
    {
        $filter = new TableFilter();
        $result = $filter->apply(['Single value']);
        $this->assertStringContainsString('<td>Single value</td>', $result);
    }

    // ==================== AttributesFilter Tests ====================

    public function testAttributesFilterName(): void
    {
        $filter = new AttributesFilter();
        $this->assertSame('attributes', $filter->getName());
    }

    public function testAttributesBasic(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['class' => 'btn', 'id' => 'submit']);
        $this->assertSame('class="btn" id="submit"', $result);
    }

    public function testAttributesBooleanTrue(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['disabled' => true, 'checked' => true]);
        $this->assertSame('disabled checked', $result);
    }

    public function testAttributesBooleanFalse(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['disabled' => false, 'class' => 'btn']);
        $this->assertSame('class="btn"', $result);
    }

    public function testAttributesWithNull(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['class' => 'btn', 'id' => null]);
        $this->assertSame('class="btn"', $result);
    }

    public function testAttributesWithArrayValue(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['class' => ['btn', 'btn-primary']]);
        $this->assertSame('class="btn btn-primary"', $result);
    }

    public function testAttributesEmptyArray(): void
    {
        $filter = new AttributesFilter();
        $this->assertSame('', $filter->apply([]));
    }

    public function testAttributesNonArray(): void
    {
        $filter = new AttributesFilter();
        $this->assertSame('', $filter->apply('not an array'));
    }

    public function testAttributesEscapesSpecialChars(): void
    {
        $filter = new AttributesFilter();
        $result = $filter->apply(['title' => 'Say "Hello"']);
        $this->assertSame('title="Say &quot;Hello&quot;"', $result);
    }

    // ==================== WrapFilter Tests ====================

    public function testWrapFilterName(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('wrap', $filter->getName());
    }

    public function testWrapBasic(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('<div>content</div>', $filter->apply('content'));
    }

    public function testWrapWithTag(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('<span>content</span>', $filter->apply('content', ['span']));
    }

    public function testWrapWithClass(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('<div class="wrapper">content</div>', $filter->apply('content', ['div', 'wrapper']));
    }

    public function testWrapWithClassAndId(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('<div class="wrapper" id="main">content</div>', $filter->apply('content', ['div', 'wrapper', 'main']));
    }

    public function testWrapSelfClosingTagIgnored(): void
    {
        $filter = new WrapFilter();
        $this->assertSame('content', $filter->apply('content', ['br']));
        $this->assertSame('content', $filter->apply('content', ['img']));
    }

    public function testWrapInvalidTagName(): void
    {
        $filter = new WrapFilter();
        // Special characters are stripped, so '<script>' becomes 'script'
        $this->assertSame('<script>content</script>', $filter->apply('content', ['<script>']));
        // Empty tag falls back to div
        $this->assertSame('<div>content</div>', $filter->apply('content', ['!!!']));
    }

    // ==================== HighlightFilter Tests ====================

    public function testHighlightFilterName(): void
    {
        $filter = new HighlightFilter();
        $this->assertSame('highlight', $filter->getName());
    }

    public function testHighlightBasic(): void
    {
        $filter = new HighlightFilter();
        $result = $filter->apply('Hello World', ['World']);
        $this->assertSame('Hello <mark>World</mark>', $result);
    }

    public function testHighlightCaseInsensitive(): void
    {
        $filter = new HighlightFilter();
        $result = $filter->apply('Hello WORLD', ['world']);
        $this->assertSame('Hello <mark>WORLD</mark>', $result);
    }

    public function testHighlightMultipleOccurrences(): void
    {
        $filter = new HighlightFilter();
        $result = $filter->apply('test test test', ['test']);
        $this->assertSame('<mark>test</mark> <mark>test</mark> <mark>test</mark>', $result);
    }

    public function testHighlightWithClass(): void
    {
        $filter = new HighlightFilter();
        $result = $filter->apply('Hello World', ['World', 'highlight-yellow']);
        $this->assertStringContainsString('class="highlight-yellow"', $result);
    }

    public function testHighlightEmptyTerm(): void
    {
        $filter = new HighlightFilter();
        $this->assertSame('Hello World', $filter->apply('Hello World', ['']));
    }

    public function testHighlightEmptyText(): void
    {
        $filter = new HighlightFilter();
        $this->assertSame('', $filter->apply('', ['term']));
    }

    // ==================== ParagraphFilter Tests ====================

    public function testParagraphFilterName(): void
    {
        $filter = new ParagraphFilter();
        $this->assertSame('paragraph', $filter->getName());
    }

    public function testParagraphBasic(): void
    {
        $filter = new ParagraphFilter();
        $this->assertSame('<p>Hello World</p>', $filter->apply('Hello World'));
    }

    public function testParagraphMultiple(): void
    {
        $filter = new ParagraphFilter();
        $input = "First paragraph.\n\nSecond paragraph.";
        $result = $filter->apply($input);
        $this->assertStringContainsString('<p>First paragraph.</p>', $result);
        $this->assertStringContainsString('<p>Second paragraph.</p>', $result);
    }

    public function testParagraphWithSingleNewlines(): void
    {
        $filter = new ParagraphFilter();
        $input = "Line 1\nLine 2";
        $result = $filter->apply($input);
        $this->assertStringContainsString('<br>', $result);
    }

    public function testParagraphEmpty(): void
    {
        $filter = new ParagraphFilter();
        $this->assertSame('', $filter->apply(''));
    }

    public function testParagraphNormalizesLineEndings(): void
    {
        $filter = new ParagraphFilter();
        $input = "Para 1\r\n\r\nPara 2";
        $result = $filter->apply($input);
        $this->assertStringContainsString('<p>Para 1</p>', $result);
        $this->assertStringContainsString('<p>Para 2</p>', $result);
    }

    // ==================== HeadingFilter Tests ====================

    public function testHeadingFilterName(): void
    {
        $filter = new HeadingFilter();
        $this->assertSame('heading', $filter->getName());
    }

    public function testHeadingDefault(): void
    {
        $filter = new HeadingFilter();
        $this->assertSame('<h1>Title</h1>', $filter->apply('Title'));
    }

    public function testHeadingWithLevel(): void
    {
        $filter = new HeadingFilter();
        $this->assertSame('<h2>Title</h2>', $filter->apply('Title', [2]));
        $this->assertSame('<h3>Title</h3>', $filter->apply('Title', [3]));
        $this->assertSame('<h6>Title</h6>', $filter->apply('Title', [6]));
    }

    public function testHeadingLevelBounds(): void
    {
        $filter = new HeadingFilter();
        $this->assertSame('<h1>Title</h1>', $filter->apply('Title', [0]));
        $this->assertSame('<h6>Title</h6>', $filter->apply('Title', [10]));
    }

    public function testHeadingWithClass(): void
    {
        $filter = new HeadingFilter();
        $result = $filter->apply('Title', [1, 'page-title']);
        $this->assertSame('<h1 class="page-title">Title</h1>', $result);
    }

    public function testHeadingWithClassAndId(): void
    {
        $filter = new HeadingFilter();
        $result = $filter->apply('Title', [2, 'section-title', 'intro']);
        $this->assertSame('<h2 class="section-title" id="intro">Title</h2>', $result);
    }

    public function testHeadingEmpty(): void
    {
        $filter = new HeadingFilter();
        $this->assertSame('', $filter->apply(''));
    }

    public function testHeadingEscapesHtml(): void
    {
        $filter = new HeadingFilter();
        $result = $filter->apply('<script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $result);
        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    // ==================== AnchorFilter Tests ====================

    public function testAnchorFilterName(): void
    {
        $filter = new AnchorFilter();
        $this->assertSame('anchor', $filter->getName());
    }

    public function testAnchorBasic(): void
    {
        $filter = new AnchorFilter();
        $result = $filter->apply('https://example.com');
        $this->assertSame('<a href="https://example.com">https://example.com</a>', $result);
    }

    public function testAnchorWithText(): void
    {
        $filter = new AnchorFilter();
        $result = $filter->apply('https://example.com', ['Click here']);
        $this->assertSame('<a href="https://example.com">Click here</a>', $result);
    }

    public function testAnchorWithTarget(): void
    {
        $filter = new AnchorFilter();
        $result = $filter->apply('https://example.com', ['Link', '_blank']);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }

    public function testAnchorWithClass(): void
    {
        $filter = new AnchorFilter();
        $result = $filter->apply('https://example.com', ['Link', '_self', 'btn']);
        $this->assertStringContainsString('class="btn"', $result);
    }

    public function testAnchorEmpty(): void
    {
        $filter = new AnchorFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== ExcerptHtmlFilter Tests ====================

    public function testExcerptHtmlFilterName(): void
    {
        $filter = new ExcerptHtmlFilter();
        $this->assertSame('excerpt_html', $filter->getName());
    }

    public function testExcerptHtmlBasic(): void
    {
        $filter = new ExcerptHtmlFilter();
        $html = '<p><strong>Hello</strong> World! This is a test.</p>';
        $result = $filter->apply($html, [10]);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('...', $result);
    }

    public function testExcerptHtmlStripsAllTags(): void
    {
        $filter = new ExcerptHtmlFilter();
        $html = '<div class="test"><p><em>Text</em></p></div>';
        $result = $filter->apply($html);
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringContainsString('Text', $result);
    }

    public function testExcerptHtmlCustomSuffix(): void
    {
        $filter = new ExcerptHtmlFilter();
        $html = '<p>This is a very long text that needs to be truncated.</p>';
        $result = $filter->apply($html, [20, ' [more]']);
        $this->assertStringContainsString('[more]', $result);
    }

    public function testExcerptHtmlShortText(): void
    {
        $filter = new ExcerptHtmlFilter();
        $html = '<p>Short</p>';
        $result = $filter->apply($html, [100]);
        $this->assertSame('Short', $result);
    }

    public function testExcerptHtmlEmpty(): void
    {
        $filter = new ExcerptHtmlFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== ClassListFilter Tests ====================

    public function testClassListFilterName(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('class_list', $filter->getName());
    }

    public function testClassListFromString(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('foo bar baz', $filter->apply('foo bar baz'));
    }

    public function testClassListFromArray(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('foo bar', $filter->apply(['foo', 'bar']));
    }

    public function testClassListFromAssociativeArray(): void
    {
        $filter = new ClassListFilter();
        $result = $filter->apply([
            'foo' => true,
            'bar' => false,
            'baz' => true,
        ]);
        $this->assertSame('foo baz', $result);
    }

    public function testClassListMixed(): void
    {
        $filter = new ClassListFilter();
        $result = $filter->apply([
            'always',
            'conditional' => true,
            'hidden' => false,
        ]);
        $this->assertSame('always conditional', $result);
    }

    public function testClassListNormalizesWhitespace(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('foo bar', $filter->apply('  foo    bar  '));
    }

    public function testClassListEmpty(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('', $filter->apply([]));
        $this->assertSame('', $filter->apply(''));
    }

    public function testClassListNonStringNonArray(): void
    {
        $filter = new ClassListFilter();
        $this->assertSame('', $filter->apply(123));
    }

    public function testClassListEscapesHtml(): void
    {
        $filter = new ClassListFilter();
        $result = $filter->apply(['<script>']);
        $this->assertSame('&lt;script&gt;', $result);
    }

    // ==================== Integration Tests ====================

    #[DataProvider('filterNamesProvider')]
    public function testAllFiltersHaveCorrectNames(string $expectedName, object $filter): void
    {
        $this->assertSame($expectedName, $filter->getName());
    }

    /**
     * @return array<string, array{string, object}>
     */
    public static function filterNamesProvider(): array
    {
        return [
            'markdown' => ['markdown', new MarkdownFilter()],
            'linkify' => ['linkify', new LinkifyFilter()],
            'list' => ['list', new ListHtmlFilter()],
            'table' => ['table', new TableFilter()],
            'attributes' => ['attributes', new AttributesFilter()],
            'wrap' => ['wrap', new WrapFilter()],
            'highlight' => ['highlight', new HighlightFilter()],
            'paragraph' => ['paragraph', new ParagraphFilter()],
            'heading' => ['heading', new HeadingFilter()],
            'anchor' => ['anchor', new AnchorFilter()],
            'excerpt_html' => ['excerpt_html', new ExcerptHtmlFilter()],
            'class_list' => ['class_list', new ClassListFilter()],
        ];
    }
}
