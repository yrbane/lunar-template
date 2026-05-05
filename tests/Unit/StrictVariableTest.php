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

    public function testStrictMethodCallReturningNullThrows(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Result: [[ obj.maybeNull() ]]');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        $obj = new class () {
            public function maybeNull(): ?string
            {
                return null;
            }
        };

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('obj.maybeNull()');

        $engine->render('test', ['obj' => $obj]);
    }

    public function testStrictMethodCallSucceedsWithValidReturn(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Result: [[ obj.greet() ]]');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        $obj = new class () {
            public function greet(): string
            {
                return 'Hello';
            }
        };

        $output = $engine->render('test', ['obj' => $obj]);
        $this->assertSame('Result: Hello', $output);
    }

    public function testNonStrictMethodCallOnNullObjectDoesNotThrow(): void
    {
        // Comportement par défaut : null sur un objet absent → chaîne vide.
        file_put_contents($this->templateDir . '/test.tpl', 'Result: [[ obj.greet() ]]');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        // Pas de mode strict.

        // obj absent : Access::get(undefined, 'greet') retourne null,
        // l'appel ->greet() ne se produit pas (l'expression entière vaut null via ?? '').
        // En réalité on a $obj->greet() qui lève sur $obj null en non-strict :
        // l'engine doit le tolérer en non-strict.
        $output = $engine->render('test', ['obj' => null]);
        $this->assertSame('Result: ', $output);
    }

    public function testStrictMissingObjectPropertyThrows(): void
    {
        file_put_contents($this->templateDir . '/test.tpl', 'Code: [[ lang.missing ]]');

        $engine = new AdvancedTemplateEngine($this->templateDir, $this->cacheDir);
        $engine->setStrictVariables(true);

        $lang = new readonly class('fr') {
            public function __construct(public string $code)
            {
            }
        };

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('lang.missing');

        $engine->render('test', ['lang' => $lang]);
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
