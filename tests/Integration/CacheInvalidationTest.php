<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Integration;

use Lunar\Template\AdvancedTemplateEngine;
use PHPUnit\Framework\TestCase;

class CacheInvalidationTest extends TestCase
{
    private string $templateDir;
    private string $cacheDir;
    private AdvancedTemplateEngine $engine;

    protected function setUp(): void
    {
        $this->templateDir = sys_get_temp_dir() . '/lunar_test_tpl_' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/lunar_test_cache_' . uniqid();

        mkdir($this->templateDir);
        mkdir($this->cacheDir);

        $this->engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->templateDir);
        $this->removeDirectory($this->cacheDir);
    }

    public function testParentTemplateModificationInvalidatesChildCache(): void
    {
        // 1. Create Parent Template
        file_put_contents($this->templateDir . '/layout.tpl', 'Parent: [% block content %]Default[% endblock %]');
        
        // 2. Create Child Template
        file_put_contents($this->templateDir . '/child.tpl', '[% extends "layout.tpl" %][% block content %]Child[% endblock %]');

        // 3. First Render (compiles and caches)
        $output1 = $this->engine->render('child');
        $this->assertEquals('Parent: Child', $output1);

        // Wait to ensure file modification time is different (at least 1 second for some filesystems)
        sleep(1);

        // 4. Modify Parent Template
        file_put_contents($this->templateDir . '/layout.tpl', 'Modified Parent: [% block content %]Default[% endblock %]');

        // Clear PHP's internal file status cache to ensure it sees the new mtime
        clearstatcache();

        // 5. Second Render (should detect parent change and recompile)
        $output2 = $this->engine->render('child');

        // This assertion is expected to FAIL currently
        $this->assertEquals('Modified Parent: Child', $output2, 'Child template should be recompiled when parent changes');
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
