<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Cache;

use Lunar\Template\Cache\CacheWarmer;
use Lunar\Template\Cache\FilesystemCache;
use PHPUnit\Framework\TestCase;

class CacheWarmerTest extends TestCase
{
    private string $tempDir;

    private string $templateDir;

    private string $cacheDir;

    private FilesystemCache $cache;

    private CacheWarmer $warmer;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/warmer-test-' . uniqid();
        $this->templateDir = $this->tempDir . '/templates';
        $this->cacheDir = $this->tempDir . '/cache';

        mkdir($this->templateDir, 0o755, true);
        mkdir($this->cacheDir, 0o755, true);

        $this->cache = new FilesystemCache($this->cacheDir);
        $this->warmer = new CacheWarmer($this->templateDir, $this->cache);
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
        file_put_contents($this->templateDir . '/' . $name, $content);
    }

    public function testWarmTemplate(): void
    {
        $this->createTemplate('hello.tpl', 'Hello [[ name ]]!');

        $result = $this->warmer->warmTemplate('hello.tpl');

        $this->assertTrue($result);

        // Cache should have the compiled content
        $files = glob($this->cacheDir . '/*.php') ?: [];
        $this->assertCount(1, $files);
    }

    public function testWarmTemplateWithoutExtension(): void
    {
        $this->createTemplate('greeting.tpl', 'Greeting: [[ msg ]]');

        $result = $this->warmer->warmTemplate('greeting');

        $this->assertTrue($result);
    }

    public function testWarmTemplateNonexistent(): void
    {
        $result = $this->warmer->warmTemplate('nonexistent.tpl');

        $this->assertFalse($result);
    }

    public function testWarmDirectory(): void
    {
        $this->createTemplate('one.tpl', 'One');
        $this->createTemplate('two.tpl', 'Two');
        $this->createTemplate('three.txt', 'Not a template');

        $results = $this->warmer->warmDirectory();

        $this->assertArrayHasKey('one.tpl', $results);
        $this->assertArrayHasKey('two.tpl', $results);
        $this->assertArrayNotHasKey('three.txt', $results);
        $this->assertTrue($results['one.tpl']);
        $this->assertTrue($results['two.tpl']);
    }

    public function testWarmDirectoryWithPattern(): void
    {
        $this->createTemplate('page.tpl', 'Page');
        $this->createTemplate('component.tpl', 'Component');

        $results = $this->warmer->warmDirectory('page.tpl');

        $this->assertCount(1, $results);
        $this->assertArrayHasKey('page.tpl', $results);
    }

    public function testWarmDirectoryEmpty(): void
    {
        $results = $this->warmer->warmDirectory();

        $this->assertEmpty($results);
    }

    public function testWarmRecursive(): void
    {
        $this->createTemplate('root.tpl', 'Root');
        mkdir($this->templateDir . '/partials', 0o755);
        file_put_contents($this->templateDir . '/partials/header.tpl', 'Header');

        $results = $this->warmer->warmRecursive();

        $this->assertArrayHasKey('root.tpl', $results);
        $this->assertArrayHasKey('partials/header.tpl', $results);
        $this->assertTrue($results['root.tpl']);
        $this->assertTrue($results['partials/header.tpl']);
    }

    public function testWarmRecursiveSkipsNonTpl(): void
    {
        $this->createTemplate('valid.tpl', 'Valid');
        file_put_contents($this->templateDir . '/invalid.txt', 'Invalid');

        $results = $this->warmer->warmRecursive();

        $this->assertArrayHasKey('valid.tpl', $results);
        $this->assertArrayNotHasKey('invalid.txt', $results);
    }

    public function testWarmTemplateWithInheritance(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]Default[% endblock %]</html>');
        $this->createTemplate('page.tpl', "[% extends 'base.tpl' %][% block content %]Page[% endblock %]");

        $result = $this->warmer->warmTemplate('page.tpl');

        $this->assertTrue($result);
    }

    public function testWarmTemplateWithVariables(): void
    {
        $this->createTemplate('vars.tpl', '[[ user.name ]] - [[ user.email ]]');

        $result = $this->warmer->warmTemplate('vars.tpl');

        $this->assertTrue($result);
    }
}
