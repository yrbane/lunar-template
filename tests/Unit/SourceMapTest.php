<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit;

use Lunar\Template\AdvancedTemplateEngine;
use Lunar\Template\Exception\TemplateException;
use PHPUnit\Framework\TestCase;

class SourceMapTest extends TestCase
{
    private string $templateDir;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->templateDir = sys_get_temp_dir() . '/lunar_test_tpl_sourcemap_' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/lunar_test_cache_sourcemap_' . uniqid();

        mkdir($this->templateDir);
        mkdir($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->templateDir);
        $this->removeDirectory($this->cacheDir);
    }

    public function testExceptionMapsToOriginalTemplateLine(): void
    {
        $templateContent = <<<'TPL'
Line 1
Line 2
Line 3 [[ undefined.method() ]] <-- Error on this line
Line 4
TPL;
        file_put_contents($this->templateDir . '/error.tpl', $templateContent);

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true); // Ensure undefined access is caught

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Error in template "error.tpl" at line 3: Undefined variable "undefined.method()" in strict mode.');

        $engine->render('error');
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
