<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Renderer;

use Lunar\Template\Compiler\TemplateCompiler;
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Macro\MacroInterface;
use Lunar\Template\Renderer\RendererInterface;
use Lunar\Template\Renderer\TemplateRenderer;
use PHPUnit\Framework\TestCase;

class TemplateRendererTest extends TestCase
{
    private string $tempDir;

    private string $templatePath;

    private string $cachePath;

    private TemplateRenderer $renderer;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/template-renderer-test-' . uniqid();
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

    public function testImplementsRendererInterface(): void
    {
        $this->assertInstanceOf(RendererInterface::class, $this->renderer);
    }

    public function testRenderSimpleTemplate(): void
    {
        $this->createTemplate('hello.tpl', 'Hello World');

        $result = $this->renderer->render('hello');

        $this->assertSame('Hello World', $result);
    }

    public function testRenderWithExtension(): void
    {
        $this->createTemplate('hello.tpl', 'Hello World');

        $result = $this->renderer->render('hello.tpl');

        $this->assertSame('Hello World', $result);
    }

    public function testRenderWithVariables(): void
    {
        $this->createTemplate('greeting.tpl', 'Hello [[ name ]]!');

        $result = $this->renderer->render('greeting', ['name' => 'John']);

        $this->assertSame('Hello John!', $result);
    }

    public function testRenderEscapesVariables(): void
    {
        $this->createTemplate('xss.tpl', 'Value: [[ value ]]');

        $result = $this->renderer->render('xss', ['value' => '<script>alert("xss")</script>']);

        $this->assertStringContainsString('&lt;script&gt;', $result);
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testRenderWithCondition(): void
    {
        $this->createTemplate('conditional.tpl', '[% if show %]Visible[% endif %]');

        $result = $this->renderer->render('conditional', ['show' => true]);
        $this->assertSame('Visible', $result);

        $result = $this->renderer->render('conditional', ['show' => false]);
        $this->assertSame('', $result);
    }

    public function testRenderWithLoop(): void
    {
        $this->createTemplate('loop.tpl', '[% for item in items %][[ item ]][% endfor %]');

        $result = $this->renderer->render('loop', ['items' => ['a', 'b', 'c']]);

        $this->assertSame('abc', $result);
    }

    public function testRenderNotFoundThrowsException(): void
    {
        $this->expectException(TemplateNotFoundException::class);

        $this->renderer->render('nonexistent');
    }

    public function testExistsReturnsTrue(): void
    {
        $this->createTemplate('exists.tpl', 'content');

        $this->assertTrue($this->renderer->exists('exists'));
    }

    public function testExistsReturnsFalse(): void
    {
        $this->assertFalse($this->renderer->exists('nonexistent'));
    }

    public function testExistsWithTraversalReturnsFalse(): void
    {
        $this->assertFalse($this->renderer->exists('../../../etc/passwd'));
    }

    public function testRegisterAndCallMacro(): void
    {
        $this->renderer->registerMacro('greet', fn (string $name) => "Hello, $name!");
        $this->createTemplate('macro.tpl', '##greet("World")##');

        $result = $this->renderer->render('macro');

        $this->assertSame('Hello, World!', $result);
    }

    public function testRegisterMacroInstance(): void
    {
        $macro = $this->createMock(MacroInterface::class);
        $macro->method('getName')->willReturn('test');
        $macro->method('execute')->with(['arg1'])->willReturn('result');

        $this->renderer->registerMacroInstance($macro);
        $this->createTemplate('macro-instance.tpl', '##test("arg1")##');

        $result = $this->renderer->render('macro-instance');

        $this->assertSame('result', $result);
    }

    public function testCallUnregisteredMacroThrowsException(): void
    {
        $this->createTemplate('bad-macro.tpl', '##unknown()##');

        $this->expectException(TemplateException::class);

        $this->renderer->render('bad-macro');
    }

    public function testSetDefaultVariables(): void
    {
        $this->renderer->setDefaultVariables(['siteName' => 'My Site']);
        $this->createTemplate('default.tpl', 'Site: [[ siteName ]]');

        $result = $this->renderer->render('default');

        $this->assertSame('Site: My Site', $result);
    }

    public function testAddDefaultVariables(): void
    {
        $this->renderer->setDefaultVariables(['a' => '1']);
        $this->renderer->addDefaultVariables(['b' => '2']);
        $this->createTemplate('multi-default.tpl', '[[ a ]][[ b ]]');

        $result = $this->renderer->render('multi-default');

        $this->assertSame('12', $result);
    }

    public function testVariablesOverrideDefaults(): void
    {
        $this->renderer->setDefaultVariables(['name' => 'Default']);
        $this->createTemplate('override.tpl', 'Hello [[ name ]]');

        $result = $this->renderer->render('override', ['name' => 'Override']);

        $this->assertSame('Hello Override', $result);
    }

    public function testClearCacheAll(): void
    {
        $this->createTemplate('cached.tpl', 'content');
        $this->renderer->render('cached');

        $cacheFiles = glob($this->cachePath . '/*.php');
        $this->assertNotEmpty($cacheFiles);

        $this->renderer->clearCache();

        $cacheFiles = glob($this->cachePath . '/*.php');
        $this->assertEmpty($cacheFiles);
    }

    public function testClearCacheSpecificTemplate(): void
    {
        $this->createTemplate('one.tpl', 'one');
        $this->createTemplate('two.tpl', 'two');
        $this->renderer->render('one');
        $this->renderer->render('two');

        $cacheFiles = glob($this->cachePath . '/*.php') ?: [];
        $this->assertCount(2, $cacheFiles);

        $this->renderer->clearCache('one');

        $cacheFiles = glob($this->cachePath . '/*.php') ?: [];
        $this->assertCount(1, $cacheFiles);
    }

    public function testTemplateInheritance(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');
        $this->createTemplate('child.tpl', "[% extends 'base.tpl' %][% block content %]custom[% endblock %]");

        $result = $this->renderer->render('child');

        $this->assertSame('<html>custom</html>', $result);
    }

    public function testTemplateInheritanceKeepsDefault(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');
        $this->createTemplate('child.tpl', "[% extends 'base.tpl' %]");

        $result = $this->renderer->render('child');

        $this->assertSame('<html>default</html>', $result);
    }

    public function testTemplateInheritanceParentNotFound(): void
    {
        $this->createTemplate('orphan.tpl', "[% extends 'nonexistent.tpl' %]");

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Parent template not found');

        $this->renderer->render('orphan');
    }

    public function testCachingWorks(): void
    {
        $this->createTemplate('cache-test.tpl', 'Original');

        $result1 = $this->renderer->render('cache-test');
        $this->assertSame('Original', $result1);

        // Modify template but cache should still have old content
        file_put_contents($this->templatePath . '/cache-test.tpl', 'Modified');
        touch($this->templatePath . '/cache-test.tpl', time() - 100); // Make it older

        $result2 = $this->renderer->render('cache-test');
        $this->assertSame('Original', $result2);
    }

    public function testWithCustomCompiler(): void
    {
        $compiler = new TemplateCompiler();
        $renderer = new TemplateRenderer($this->templatePath, $this->cachePath, $compiler);

        $this->createTemplate('custom.tpl', 'Hello [[ name ]]');

        $result = $renderer->render('custom', ['name' => 'World']);

        $this->assertSame('Hello World', $result);
    }

    public function testCacheDirectoryCreatedAutomatically(): void
    {
        $newCachePath = $this->tempDir . '/new-cache';

        $this->assertDirectoryDoesNotExist($newCachePath);

        new TemplateRenderer($this->templatePath, $newCachePath);

        $this->assertDirectoryExists($newCachePath);
    }

    public function testRenderDotNotation(): void
    {
        $this->createTemplate('dot.tpl', '[[ user.profile.name ]]');

        $result = $this->renderer->render('dot', [
            'user' => ['profile' => ['name' => 'John']],
        ]);

        $this->assertSame('John', $result);
    }
}
