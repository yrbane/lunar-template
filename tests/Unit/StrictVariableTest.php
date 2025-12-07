<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit;

use Lunar\Template\AdvancedTemplateEngine;
use Lunar\Template\Exception\TemplateException;
use PHPUnit\Framework\TestCase;

class StrictVariableTest extends TestCase
{
    private string $templateDir;
    private string $cacheDir;

    protected function setUp(): void
    {
        $this->templateDir = sys_get_temp_dir() . '/lunar_test_tpl_strict_' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/lunar_test_cache_strict_' . uniqid();

        mkdir($this->templateDir);
        mkdir($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->templateDir);
        $this->removeDirectory($this->cacheDir);
    }

    public function testUndefinedVariableThrowsExceptionInStrictMode(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Hello [[ name ]].');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true); // Assuming such a method will exist

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Undefined variable "name"'); // Or similar message
        
        $engine->render('test', ['other_var' => 'world']);
    }

    public function testNullVariableThrowsExceptionInStrictMode(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Hello [[ name ]].');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Undefined variable "name"'); // Modified to expect "Undefined" for null values
        
        $engine->render('test', ['name' => null]);
    }

    public function testUndefinedVariableDoesNotThrowExceptionByDefault(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Hello [[ name ]].');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        // Default mode (not strict)

        $output = $engine->render('test', ['other_var' => 'world']);
        $this->assertEquals('Hello .', $output);
    }

    public function testNullVariableDoesNotThrowExceptionByDefault(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Hello [[ name ]].');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        // Default mode (not strict)

        $output = $engine->render('test', ['name' => null]);
        $this->assertEquals('Hello .', $output);
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
