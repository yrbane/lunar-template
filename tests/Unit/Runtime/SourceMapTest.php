<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Runtime;

use Lunar\Template\Runtime\SourceMap;
use PHPUnit\Framework\TestCase;

class SourceMapTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/lunar_sourcemap_unit_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        $files = glob($this->tempDir . '/*');
        if ($files !== false) {
            foreach ($files as $f) {
                @unlink($f);
            }
        }
        @rmdir($this->tempDir);
    }

    public function testInjectAddsMarkerToEveryLine(): void
    {
        $source = "Line 1\nLine 2\nLine 3";
        $injected = SourceMap::inject($source, '/path/to/file.tpl');

        $this->assertStringContainsString('L:/path/to/file.tpl:1', $injected);
        $this->assertStringContainsString('L:/path/to/file.tpl:2', $injected);
        $this->assertStringContainsString('L:/path/to/file.tpl:3', $injected);
    }

    public function testInjectPreservesLineCount(): void
    {
        $source = "A\nB\nC";
        $injected = SourceMap::inject($source, 'x.tpl');

        $this->assertSame(3, substr_count($injected, "\n") + 1);
    }

    public function testResolveReturnsNullForMissingFile(): void
    {
        $this->assertNull(SourceMap::resolve('/nope/missing.php', 1));
    }

    public function testResolveFindsNearestMarkerBefore(): void
    {
        $compiled = $this->tempDir . '/compiled.php';
        $content = SourceMap::inject("Line 1\nLine 2\nLine 3", '/orig.tpl');
        file_put_contents($compiled, $content);

        // Ligne 2 du compilé doit résoudre vers /orig.tpl:2
        $resolved = SourceMap::resolve($compiled, 2);

        $this->assertNotNull($resolved);
        $this->assertSame('/orig.tpl', $resolved['file']);
        $this->assertSame(2, $resolved['line']);
    }

    public function testResolveReturnsNullWhenNoMarkers(): void
    {
        $compiled = $this->tempDir . '/no-markers.php';
        file_put_contents($compiled, "Line 1\nLine 2");

        $this->assertNull(SourceMap::resolve($compiled, 2));
    }
}
