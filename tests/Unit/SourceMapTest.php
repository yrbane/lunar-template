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
Line 3 [[ undefined_var ]] <-- Error on this line
Line 4
TPL;
        file_put_contents($this->templateDir . '/error.tpl', $templateContent);

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true); // Ensure undefined access is caught

        try {
            $engine->render('error');
            $this->fail('TemplateException was not thrown.');
        } catch (TemplateException $e) {
            $expectedMessagePart = 'Error in template "error.tpl" at line 3: Undefined variable "undefined_var" in strict mode.';
            $this->assertStringContainsString($expectedMessagePart, $e->getMessage());
        }
    }

    public function testSourceMapResolvesChildLineWithExtends(): void
    {
        // base.tpl avec un seul block, page.tpl étend et lève une erreur ligne 3.
        file_put_contents($this->templateDir . '/base.tpl', "<html>\n[% block content %]Default[% endblock %]\n</html>");
        file_put_contents(
            $this->templateDir . '/page.tpl',
            "[% extends 'base.tpl' %]\n[% block content %]\nLine 3 of page: [[ undef ]]\n[% endblock %]"
        );

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        try {
            $engine->render('page');
            $this->fail('TemplateException attendue');
        } catch (TemplateException $e) {
            // L'erreur doit pointer vers page.tpl ligne 3 (et non vers la ligne du compilé).
            $this->assertStringContainsString('page.tpl', $e->getMessage());
            $this->assertStringContainsString('line 3', $e->getMessage());
        }
    }

    public function testSourceMapResolvesParentLineWhenErrorInBaseTemplate(): void
    {
        // L'erreur survient dans la base.tpl, pas dans le child.
        file_put_contents(
            $this->templateDir . '/base.tpl',
            "<html>\nHeader\n[[ undef_in_base ]]\n</html>"
        );
        file_put_contents(
            $this->templateDir . '/page.tpl',
            "[% extends 'base.tpl' %]"
        );

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        try {
            $engine->render('page');
            $this->fail('TemplateException attendue');
        } catch (TemplateException $e) {
            $this->assertStringContainsString('base.tpl', $e->getMessage());
            $this->assertStringContainsString('line 3', $e->getMessage());
        }
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
