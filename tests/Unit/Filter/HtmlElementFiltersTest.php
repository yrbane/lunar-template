<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Html\AbbrFilter;
use Lunar\Template\Filter\Html\AFilter;
use Lunar\Template\Filter\Html\AudioFilter;
use Lunar\Template\Filter\Html\BadgeFilter;
use Lunar\Template\Filter\Html\BlockquoteFilter;
use Lunar\Template\Filter\Html\ButtonFilter;
use Lunar\Template\Filter\Html\CodeFilter;
use Lunar\Template\Filter\Html\DivFilter;
use Lunar\Template\Filter\Html\EmFilter;
use Lunar\Template\Filter\Html\HFilter;
use Lunar\Template\Filter\Html\IframeFilter;
use Lunar\Template\Filter\Html\ImgFilter;
use Lunar\Template\Filter\Html\MeterFilter;
use Lunar\Template\Filter\Html\PFilter;
use Lunar\Template\Filter\Html\PreFilter;
use Lunar\Template\Filter\Html\ProgressFilter;
use Lunar\Template\Filter\Html\SmallFilter;
use Lunar\Template\Filter\Html\SpanFilter;
use Lunar\Template\Filter\Html\StrongFilter;
use Lunar\Template\Filter\Html\TimeFilter;
use Lunar\Template\Filter\Html\VideoFilter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(DivFilter::class)]
#[CoversClass(SpanFilter::class)]
#[CoversClass(StrongFilter::class)]
#[CoversClass(EmFilter::class)]
#[CoversClass(SmallFilter::class)]
#[CoversClass(CodeFilter::class)]
#[CoversClass(PreFilter::class)]
#[CoversClass(BlockquoteFilter::class)]
#[CoversClass(AbbrFilter::class)]
#[CoversClass(TimeFilter::class)]
#[CoversClass(ImgFilter::class)]
#[CoversClass(VideoFilter::class)]
#[CoversClass(AudioFilter::class)]
#[CoversClass(IframeFilter::class)]
#[CoversClass(ProgressFilter::class)]
#[CoversClass(MeterFilter::class)]
#[CoversClass(BadgeFilter::class)]
#[CoversClass(ButtonFilter::class)]
#[CoversClass(PFilter::class)]
#[CoversClass(HFilter::class)]
#[CoversClass(AFilter::class)]
final class HtmlElementFiltersTest extends TestCase
{
    // ==================== DivFilter Tests ====================

    public function testDivFilterName(): void
    {
        $filter = new DivFilter();
        $this->assertSame('div', $filter->getName());
    }

    public function testDivBasic(): void
    {
        $filter = new DivFilter();
        $this->assertSame('<div>content</div>', $filter->apply('content'));
    }

    public function testDivWithClass(): void
    {
        $filter = new DivFilter();
        $this->assertSame('<div class="container">content</div>', $filter->apply('content', ['container']));
    }

    public function testDivWithClassAndId(): void
    {
        $filter = new DivFilter();
        $this->assertSame('<div class="box" id="main">content</div>', $filter->apply('content', ['box', 'main']));
    }

    // ==================== SpanFilter Tests ====================

    public function testSpanFilterName(): void
    {
        $filter = new SpanFilter();
        $this->assertSame('span', $filter->getName());
    }

    public function testSpanBasic(): void
    {
        $filter = new SpanFilter();
        $this->assertSame('<span>text</span>', $filter->apply('text'));
    }

    public function testSpanWithClass(): void
    {
        $filter = new SpanFilter();
        $this->assertSame('<span class="highlight">text</span>', $filter->apply('text', ['highlight']));
    }

    // ==================== StrongFilter Tests ====================

    public function testStrongFilterName(): void
    {
        $filter = new StrongFilter();
        $this->assertSame('strong', $filter->getName());
    }

    public function testStrongBasic(): void
    {
        $filter = new StrongFilter();
        $this->assertSame('<strong>text</strong>', $filter->apply('text'));
    }

    public function testStrongWithClass(): void
    {
        $filter = new StrongFilter();
        $this->assertSame('<strong class="warning">text</strong>', $filter->apply('text', ['warning']));
    }

    public function testStrongEscapesHtml(): void
    {
        $filter = new StrongFilter();
        $this->assertSame('<strong>&lt;script&gt;</strong>', $filter->apply('<script>'));
    }

    // ==================== EmFilter Tests ====================

    public function testEmFilterName(): void
    {
        $filter = new EmFilter();
        $this->assertSame('em', $filter->getName());
    }

    public function testEmBasic(): void
    {
        $filter = new EmFilter();
        $this->assertSame('<em>text</em>', $filter->apply('text'));
    }

    public function testEmWithClass(): void
    {
        $filter = new EmFilter();
        $this->assertSame('<em class="note">text</em>', $filter->apply('text', ['note']));
    }

    // ==================== SmallFilter Tests ====================

    public function testSmallFilterName(): void
    {
        $filter = new SmallFilter();
        $this->assertSame('small', $filter->getName());
    }

    public function testSmallBasic(): void
    {
        $filter = new SmallFilter();
        $this->assertSame('<small>text</small>', $filter->apply('text'));
    }

    public function testSmallWithClass(): void
    {
        $filter = new SmallFilter();
        $this->assertSame('<small class="muted">text</small>', $filter->apply('text', ['muted']));
    }

    // ==================== CodeFilter Tests ====================

    public function testCodeFilterName(): void
    {
        $filter = new CodeFilter();
        $this->assertSame('code', $filter->getName());
    }

    public function testCodeBasic(): void
    {
        $filter = new CodeFilter();
        $this->assertSame('<code>console.log()</code>', $filter->apply('console.log()'));
    }

    public function testCodeWithLanguage(): void
    {
        $filter = new CodeFilter();
        $this->assertSame('<code class="language-php">echo $var;</code>', $filter->apply('echo $var;', ['php']));
    }

    public function testCodeEscapesHtml(): void
    {
        $filter = new CodeFilter();
        $this->assertSame('<code>&lt;div&gt;</code>', $filter->apply('<div>'));
    }

    // ==================== PreFilter Tests ====================

    public function testPreFilterName(): void
    {
        $filter = new PreFilter();
        $this->assertSame('pre', $filter->getName());
    }

    public function testPreBasic(): void
    {
        $filter = new PreFilter();
        $this->assertSame('<pre>formatted text</pre>', $filter->apply('formatted text'));
    }

    public function testPreWithClass(): void
    {
        $filter = new PreFilter();
        $this->assertSame('<pre class="code-block">code</pre>', $filter->apply('code', ['code-block']));
    }

    // ==================== BlockquoteFilter Tests ====================

    public function testBlockquoteFilterName(): void
    {
        $filter = new BlockquoteFilter();
        $this->assertSame('blockquote', $filter->getName());
    }

    public function testBlockquoteBasic(): void
    {
        $filter = new BlockquoteFilter();
        $this->assertSame('<blockquote>Quote text</blockquote>', $filter->apply('Quote text'));
    }

    public function testBlockquoteWithCite(): void
    {
        $filter = new BlockquoteFilter();
        $this->assertSame('<blockquote>Quote<footer>Author</footer></blockquote>', $filter->apply('Quote', ['Author']));
    }

    public function testBlockquoteWithCiteAndClass(): void
    {
        $filter = new BlockquoteFilter();
        $result = $filter->apply('Quote', ['Author', 'fancy-quote']);
        $this->assertStringContainsString('class="fancy-quote"', $result);
        $this->assertStringContainsString('<footer>Author</footer>', $result);
    }

    // ==================== AbbrFilter Tests ====================

    public function testAbbrFilterName(): void
    {
        $filter = new AbbrFilter();
        $this->assertSame('abbr', $filter->getName());
    }

    public function testAbbrBasic(): void
    {
        $filter = new AbbrFilter();
        $this->assertSame('<abbr>HTML</abbr>', $filter->apply('HTML'));
    }

    public function testAbbrWithTitle(): void
    {
        $filter = new AbbrFilter();
        $this->assertSame('<abbr title="HyperText Markup Language">HTML</abbr>', $filter->apply('HTML', ['HyperText Markup Language']));
    }

    // ==================== TimeFilter Tests ====================

    public function testTimeFilterName(): void
    {
        $filter = new TimeFilter();
        $this->assertSame('time', $filter->getName());
    }

    public function testTimeBasic(): void
    {
        $filter = new TimeFilter();
        $result = $filter->apply('2025-01-15');
        $this->assertStringContainsString('<time datetime=', $result);
        $this->assertStringContainsString('2025-01-15', $result);
    }

    public function testTimeWithFormat(): void
    {
        $filter = new TimeFilter();
        $result = $filter->apply('2025-01-15', ['F j, Y']);
        $this->assertStringContainsString('January 15, 2025', $result);
    }

    public function testTimeWithTimestamp(): void
    {
        $filter = new TimeFilter();
        $result = $filter->apply(1704067200); // 2024-01-01
        $this->assertStringContainsString('<time datetime=', $result);
    }

    public function testTimeEmpty(): void
    {
        $filter = new TimeFilter();
        $this->assertSame('', $filter->apply(''));
    }

    public function testTimeInvalidDate(): void
    {
        $filter = new TimeFilter();
        $this->assertSame('', $filter->apply('not a date'));
    }

    // ==================== ImgFilter Tests ====================

    public function testImgFilterName(): void
    {
        $filter = new ImgFilter();
        $this->assertSame('img', $filter->getName());
    }

    public function testImgBasic(): void
    {
        $filter = new ImgFilter();
        $this->assertSame('<img src="image.jpg" alt="">', $filter->apply('image.jpg'));
    }

    public function testImgWithAlt(): void
    {
        $filter = new ImgFilter();
        $this->assertSame('<img src="image.jpg" alt="My Image">', $filter->apply('image.jpg', ['My Image']));
    }

    public function testImgWithClass(): void
    {
        $filter = new ImgFilter();
        $result = $filter->apply('image.jpg', ['Alt', 'photo']);
        $this->assertStringContainsString('class="photo"', $result);
    }

    public function testImgWithLazyLoading(): void
    {
        $filter = new ImgFilter();
        $result = $filter->apply('image.jpg', ['Alt', 'photo', true]);
        $this->assertStringContainsString('loading="lazy"', $result);
    }

    public function testImgEmpty(): void
    {
        $filter = new ImgFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== VideoFilter Tests ====================

    public function testVideoFilterName(): void
    {
        $filter = new VideoFilter();
        $this->assertSame('video', $filter->getName());
    }

    public function testVideoBasic(): void
    {
        $filter = new VideoFilter();
        $result = $filter->apply('video.mp4');
        $this->assertStringContainsString('<video src="video.mp4"', $result);
        $this->assertStringContainsString('controls', $result);
    }

    public function testVideoWithAutoplay(): void
    {
        $filter = new VideoFilter();
        $result = $filter->apply('video.mp4', [true]);
        $this->assertStringContainsString('autoplay', $result);
    }

    public function testVideoWithLoop(): void
    {
        $filter = new VideoFilter();
        $result = $filter->apply('video.mp4', [false, true]);
        $this->assertStringContainsString('loop', $result);
    }

    public function testVideoWithMuted(): void
    {
        $filter = new VideoFilter();
        $result = $filter->apply('video.mp4', [false, false, true]);
        $this->assertStringContainsString('muted', $result);
    }

    public function testVideoEmpty(): void
    {
        $filter = new VideoFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== AudioFilter Tests ====================

    public function testAudioFilterName(): void
    {
        $filter = new AudioFilter();
        $this->assertSame('audio', $filter->getName());
    }

    public function testAudioBasic(): void
    {
        $filter = new AudioFilter();
        $result = $filter->apply('audio.mp3');
        $this->assertStringContainsString('<audio src="audio.mp3"', $result);
        $this->assertStringContainsString('controls', $result);
    }

    public function testAudioWithAutoplay(): void
    {
        $filter = new AudioFilter();
        $result = $filter->apply('audio.mp3', [true]);
        $this->assertStringContainsString('autoplay', $result);
    }

    public function testAudioWithLoop(): void
    {
        $filter = new AudioFilter();
        $result = $filter->apply('audio.mp3', [false, true]);
        $this->assertStringContainsString('loop', $result);
    }

    public function testAudioEmpty(): void
    {
        $filter = new AudioFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== IframeFilter Tests ====================

    public function testIframeFilterName(): void
    {
        $filter = new IframeFilter();
        $this->assertSame('iframe', $filter->getName());
    }

    public function testIframeBasic(): void
    {
        $filter = new IframeFilter();
        $result = $filter->apply('https://example.com');
        $this->assertStringContainsString('<iframe src="https://example.com"', $result);
        $this->assertStringContainsString('allowfullscreen', $result);
    }

    public function testIframeWithTitle(): void
    {
        $filter = new IframeFilter();
        $result = $filter->apply('https://example.com', ['Video Player']);
        $this->assertStringContainsString('title="Video Player"', $result);
    }

    public function testIframeWithLazyLoading(): void
    {
        $filter = new IframeFilter();
        $result = $filter->apply('https://example.com', ['', '', true]);
        $this->assertStringContainsString('loading="lazy"', $result);
    }

    public function testIframeEmpty(): void
    {
        $filter = new IframeFilter();
        $this->assertSame('', $filter->apply(''));
    }

    // ==================== ProgressFilter Tests ====================

    public function testProgressFilterName(): void
    {
        $filter = new ProgressFilter();
        $this->assertSame('progress', $filter->getName());
    }

    public function testProgressBasic(): void
    {
        $filter = new ProgressFilter();
        $this->assertSame('<progress value="75" max="100"></progress>', $filter->apply(75));
    }

    public function testProgressWithCustomMax(): void
    {
        $filter = new ProgressFilter();
        $this->assertSame('<progress value="30" max="50"></progress>', $filter->apply(30, [50]));
    }

    public function testProgressWithClass(): void
    {
        $filter = new ProgressFilter();
        $result = $filter->apply(75, [100, 'progress-bar']);
        $this->assertStringContainsString('class="progress-bar"', $result);
    }

    // ==================== MeterFilter Tests ====================

    public function testMeterFilterName(): void
    {
        $filter = new MeterFilter();
        $this->assertSame('meter', $filter->getName());
    }

    public function testMeterBasic(): void
    {
        $filter = new MeterFilter();
        $this->assertSame('<meter value="0.6"></meter>', $filter->apply(0.6));
    }

    public function testMeterWithMinMax(): void
    {
        $filter = new MeterFilter();
        $result = $filter->apply(75, [0, 100]);
        $this->assertStringContainsString('min="0"', $result);
        $this->assertStringContainsString('max="100"', $result);
    }

    public function testMeterWithLowHigh(): void
    {
        $filter = new MeterFilter();
        $result = $filter->apply(75, [0, 100, 30, 80]);
        $this->assertStringContainsString('low="30"', $result);
        $this->assertStringContainsString('high="80"', $result);
    }

    // ==================== BadgeFilter Tests ====================

    public function testBadgeFilterName(): void
    {
        $filter = new BadgeFilter();
        $this->assertSame('badge', $filter->getName());
    }

    public function testBadgeBasic(): void
    {
        $filter = new BadgeFilter();
        $this->assertSame('<span class="badge">New</span>', $filter->apply('New'));
    }

    public function testBadgeWithVariant(): void
    {
        $filter = new BadgeFilter();
        $this->assertSame('<span class="badge badge-danger">5</span>', $filter->apply('5', ['danger']));
    }

    // ==================== ButtonFilter Tests ====================

    public function testButtonFilterName(): void
    {
        $filter = new ButtonFilter();
        $this->assertSame('button', $filter->getName());
    }

    public function testButtonBasic(): void
    {
        $filter = new ButtonFilter();
        $this->assertSame('<button type="button">Click</button>', $filter->apply('Click'));
    }

    public function testButtonSubmit(): void
    {
        $filter = new ButtonFilter();
        $this->assertSame('<button type="submit">Submit</button>', $filter->apply('Submit', ['submit']));
    }

    public function testButtonWithClass(): void
    {
        $filter = new ButtonFilter();
        $result = $filter->apply('Send', ['submit', 'btn btn-primary']);
        $this->assertStringContainsString('class="btn btn-primary"', $result);
    }

    public function testButtonDisabled(): void
    {
        $filter = new ButtonFilter();
        $result = $filter->apply('Disabled', ['button', '', true]);
        $this->assertStringContainsString('disabled', $result);
    }

    public function testButtonInvalidType(): void
    {
        $filter = new ButtonFilter();
        $result = $filter->apply('Click', ['invalid']);
        $this->assertStringContainsString('type="button"', $result);
    }

    // ==================== Alias Filters Tests ====================

    public function testPFilterName(): void
    {
        $filter = new PFilter();
        $this->assertSame('p', $filter->getName());
    }

    public function testPFilterBehavesLikeParagraph(): void
    {
        $filter = new PFilter();
        $this->assertSame('<p>Hello World</p>', $filter->apply('Hello World'));
    }

    public function testPFilterMultipleParagraphs(): void
    {
        $filter = new PFilter();
        $input = "First paragraph.\n\nSecond paragraph.";
        $result = $filter->apply($input);
        $this->assertStringContainsString('<p>First paragraph.</p>', $result);
        $this->assertStringContainsString('<p>Second paragraph.</p>', $result);
    }

    public function testHFilterName(): void
    {
        $filter = new HFilter();
        $this->assertSame('h', $filter->getName());
    }

    public function testHFilterBehavesLikeHeading(): void
    {
        $filter = new HFilter();
        $this->assertSame('<h1>Title</h1>', $filter->apply('Title'));
        $this->assertSame('<h2>Title</h2>', $filter->apply('Title', [2]));
        $this->assertSame('<h3>Title</h3>', $filter->apply('Title', [3]));
    }

    public function testHFilterWithClassAndId(): void
    {
        $filter = new HFilter();
        $result = $filter->apply('Title', [2, 'section-title', 'intro']);
        $this->assertSame('<h2 class="section-title" id="intro">Title</h2>', $result);
    }

    public function testAFilterName(): void
    {
        $filter = new AFilter();
        $this->assertSame('a', $filter->getName());
    }

    public function testAFilterBehavesLikeAnchor(): void
    {
        $filter = new AFilter();
        $this->assertSame('<a href="https://example.com">https://example.com</a>', $filter->apply('https://example.com'));
    }

    public function testAFilterWithText(): void
    {
        $filter = new AFilter();
        $result = $filter->apply('https://example.com', ['Click here']);
        $this->assertSame('<a href="https://example.com">Click here</a>', $result);
    }

    public function testAFilterWithTarget(): void
    {
        $filter = new AFilter();
        $result = $filter->apply('https://example.com', ['Link', '_blank']);
        $this->assertStringContainsString('target="_blank"', $result);
        $this->assertStringContainsString('rel="noopener noreferrer"', $result);
    }

    // ==================== Integration Test ====================

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
            'div' => ['div', new DivFilter()],
            'span' => ['span', new SpanFilter()],
            'strong' => ['strong', new StrongFilter()],
            'em' => ['em', new EmFilter()],
            'small' => ['small', new SmallFilter()],
            'code' => ['code', new CodeFilter()],
            'pre' => ['pre', new PreFilter()],
            'blockquote' => ['blockquote', new BlockquoteFilter()],
            'abbr' => ['abbr', new AbbrFilter()],
            'time' => ['time', new TimeFilter()],
            'img' => ['img', new ImgFilter()],
            'video' => ['video', new VideoFilter()],
            'audio' => ['audio', new AudioFilter()],
            'iframe' => ['iframe', new IframeFilter()],
            'progress' => ['progress', new ProgressFilter()],
            'meter' => ['meter', new MeterFilter()],
            'badge' => ['badge', new BadgeFilter()],
            'button' => ['button', new ButtonFilter()],
            'p' => ['p', new PFilter()],
            'h' => ['h', new HFilter()],
            'a' => ['a', new AFilter()],
        ];
    }
}
