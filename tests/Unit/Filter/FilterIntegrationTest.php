<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Renderer\TemplateRenderer;
use PHPUnit\Framework\TestCase;

class FilterIntegrationTest extends TestCase
{
    private string $tempDir;

    private string $templatePath;

    private string $cachePath;

    private TemplateRenderer $renderer;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/filter-integration-test-' . uniqid();
        $this->templatePath = $this->tempDir . '/templates';
        $this->cachePath = $this->tempDir . '/cache';

        mkdir($this->templatePath, 0o755, true);
        mkdir($this->cachePath, 0o755, true);

        $this->renderer = new TemplateRenderer($this->templatePath, $this->cachePath);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        if ($files === false) {
            return;
        }
        foreach (array_diff($files, ['.', '..']) as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function createTemplate(string $name, string $content): void
    {
        file_put_contents($this->templatePath . '/' . $name, $content);
    }

    public function testUpperFilter(): void
    {
        $this->createTemplate('upper.tpl', 'Hello [[ name | upper ]]!');

        $result = $this->renderer->render('upper', ['name' => 'world']);

        $this->assertSame('Hello WORLD!', $result);
    }

    public function testLowerFilter(): void
    {
        $this->createTemplate('lower.tpl', 'Hello [[ name | lower ]]!');

        $result = $this->renderer->render('lower', ['name' => 'WORLD']);

        $this->assertSame('Hello world!', $result);
    }

    public function testChainedFilters(): void
    {
        $this->createTemplate('chain.tpl', '[[ text | trim | upper ]]');

        $result = $this->renderer->render('chain', ['text' => '  hello  ']);

        $this->assertSame('HELLO', $result);
    }

    public function testFilterWithArguments(): void
    {
        $this->createTemplate('truncate.tpl', '[[ text | truncate(10, "...") ]]');

        $result = $this->renderer->render('truncate', ['text' => 'Hello World Test']);

        $this->assertSame('Hello Worl...', $result);
    }

    public function testNumberFormatFilter(): void
    {
        $this->createTemplate('number.tpl', '[[ price | number_format(2) ]]');

        $result = $this->renderer->render('number', ['price' => 1234.5]);

        $this->assertSame('1,234.50', $result);
    }

    public function testDateFilter(): void
    {
        $this->createTemplate('date.tpl', '[[ publishedAt | date("Y-m-d") ]]');

        $result = $this->renderer->render('date', ['publishedAt' => '2024-06-15 10:30:00']);

        $this->assertSame('2024-06-15', $result);
    }

    public function testRawFilter(): void
    {
        $this->createTemplate('raw.tpl', '[[ html | raw ]]');

        $result = $this->renderer->render('raw', ['html' => '<strong>Bold</strong>']);

        $this->assertSame('<strong>Bold</strong>', $result);
    }

    public function testJoinFilter(): void
    {
        $this->createTemplate('join.tpl', '[[ items | join(", ") ]]');

        $result = $this->renderer->render('join', ['items' => ['a', 'b', 'c']]);

        $this->assertSame('a, b, c', $result);
    }

    public function testSlugFilter(): void
    {
        $this->createTemplate('slug.tpl', '[[ title | slug ]]');

        $result = $this->renderer->render('slug', ['title' => 'Hello World']);

        $this->assertSame('hello-world', $result);
    }

    public function testLengthFilter(): void
    {
        $this->createTemplate('length.tpl', '[[ items | length ]]');

        $result = $this->renderer->render('length', ['items' => [1, 2, 3]]);

        $this->assertSame('3', $result);
    }

    public function testFirstFilter(): void
    {
        $this->createTemplate('first.tpl', '[[ items | first ]]');

        $result = $this->renderer->render('first', ['items' => ['a', 'b', 'c']]);

        $this->assertSame('a', $result);
    }

    public function testCustomFilter(): void
    {
        $this->renderer->registerFilter('double', fn($value) => $value * 2);
        $this->createTemplate('custom.tpl', '[[ number | double ]]');

        $result = $this->renderer->render('custom', ['number' => 5]);

        $this->assertSame('10', $result);
    }

    public function testFilterRegistry(): void
    {
        $registry = $this->renderer->getFilterRegistry();

        $this->assertTrue($registry->has('upper'));
        $this->assertTrue($registry->has('lower'));
        $this->assertTrue($registry->has('slug'));
    }

    public function testComplexFilterChain(): void
    {
        $this->createTemplate('complex.tpl', '[[ text | trim | lower | slug ]]');

        $result = $this->renderer->render('complex', ['text' => '  Hello World!  ']);

        $this->assertSame('hello-world', $result);
    }

    public function testFilterWithDotNotation(): void
    {
        $this->createTemplate('dot.tpl', '[[ user.name | upper ]]');

        $result = $this->renderer->render('dot', ['user' => ['name' => 'alice']]);

        $this->assertSame('ALICE', $result);
    }

    public function testNoFilter(): void
    {
        $this->createTemplate('nofilter.tpl', '[[ name ]]');

        $result = $this->renderer->render('nofilter', ['name' => '<script>']);

        $this->assertStringContainsString('&lt;script&gt;', $result);
    }

    public function testEscapeFilterJs(): void
    {
        $this->createTemplate('escapejs.tpl', '[[ text | escape("js") ]]');

        $result = $this->renderer->render('escapejs', ['text' => 'alert("xss")']);

        $this->assertStringNotContainsString('"', $result);
    }

    public function testCurrencyFilter(): void
    {
        $this->createTemplate('currency.tpl', '[[ price | currency("$") ]]');

        $result = $this->renderer->render('currency', ['price' => 1234.56]);

        $this->assertSame('$1,234.56', $result);
    }

    public function testMapFilter(): void
    {
        $this->createTemplate('map.tpl', '[[ items | map("upper") | join(", ") ]]');

        $result = $this->renderer->render('map', ['items' => ['hello', 'world']]);

        $this->assertSame('HELLO, WORLD', $result);
    }
}
