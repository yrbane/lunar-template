<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Renderer;

use Lunar\Template\Exception\CircularInheritanceException;
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Renderer\InheritanceResolver;
use Lunar\Template\Security\PathValidator;
use PHPUnit\Framework\TestCase;

class InheritanceResolverTest extends TestCase
{
    private string $tempDir;

    private string $templatePath;

    private InheritanceResolver $resolver;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/inheritance-test-' . uniqid();
        $this->templatePath = $this->tempDir . '/templates';

        mkdir($this->templatePath, 0o755, true);

        $pathValidator = new PathValidator($this->templatePath);
        $this->resolver = new InheritanceResolver($pathValidator);
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

    public function testResolveWithoutInheritance(): void
    {
        $source = 'Hello World';

        $result = $this->resolver->resolve($source);

        $this->assertSame('Hello World', $result);
    }

    public function testResolveSimpleInheritance(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');

        $source = "[% extends 'base.tpl' %][% block content %]custom[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<html>custom</html>', $result);
    }

    public function testResolveKeepsParentBlockIfNotOverridden(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');

        $source = "[% extends 'base.tpl' %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<html>default</html>', $result);
    }

    public function testResolveMultipleBlocks(): void
    {
        $this->createTemplate('base.tpl', '[% block header %]Header[% endblock %][% block content %]Content[% endblock %]');

        $source = "[% extends 'base.tpl' %][% block header %]Custom Header[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('Custom HeaderContent', $result);
    }

    public function testResolveMultiLevelInheritance(): void
    {
        $this->createTemplate('grandparent.tpl', '<main>[% block content %]GP[% endblock %]</main>');
        $this->createTemplate('parent.tpl', "[% extends 'grandparent.tpl' %][% block content %]Parent[% endblock %]");

        $source = "[% extends 'parent.tpl' %][% block content %]Child[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<main>Child</main>', $result);
    }

    public function testResolveThreeLevelInheritance(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block body %]Base[% endblock %]</html>');
        $this->createTemplate('layout.tpl', "[% extends 'base.tpl' %][% block body %]Layout[% endblock %]");
        $this->createTemplate('page.tpl', "[% extends 'layout.tpl' %][% block body %]Page[% endblock %]");

        $source = "[% extends 'page.tpl' %][% block body %]Final[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<html>Final</html>', $result);
    }

    public function testResolveCircularInheritanceThrowsException(): void
    {
        $this->createTemplate('a.tpl', "[% extends 'b.tpl' %]");
        $this->createTemplate('b.tpl', "[% extends 'a.tpl' %]");

        $this->expectException(CircularInheritanceException::class);
        $this->expectExceptionMessage('Circular template inheritance detected');

        $source = "[% extends 'a.tpl' %]";
        $this->resolver->resolve($source);
    }

    public function testResolveCircularInheritanceChainInException(): void
    {
        $this->createTemplate('a.tpl', "[% extends 'b.tpl' %]");
        $this->createTemplate('b.tpl', "[% extends 'c.tpl' %]");
        $this->createTemplate('c.tpl', "[% extends 'a.tpl' %]");

        try {
            $source = "[% extends 'a.tpl' %]";
            $this->resolver->resolve($source);
            $this->fail('Expected CircularInheritanceException');
        } catch (CircularInheritanceException $e) {
            $chain = $e->getInheritanceChain();
            $this->assertContains('a.tpl', $chain);
            $this->assertContains('b.tpl', $chain);
            $this->assertContains('c.tpl', $chain);
        }
    }

    public function testResolveSelfInheritanceThrowsException(): void
    {
        $this->createTemplate('self.tpl', "[% extends 'self.tpl' %]");

        $this->expectException(CircularInheritanceException::class);

        $source = "[% extends 'self.tpl' %]";
        $this->resolver->resolve($source);
    }

    public function testResolveParentNotFoundThrowsException(): void
    {
        $source = "[% extends 'nonexistent.tpl' %]";

        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Parent template not found');

        $this->resolver->resolve($source);
    }

    public function testResolveWithParentDirective(): void
    {
        $this->createTemplate('base.tpl', '<div>[% block content %]Base Content[% endblock %]</div>');

        $source = "[% extends 'base.tpl' %][% block content %]Before [% parent %] After[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<div>Before Base Content After</div>', $result);
    }

    public function testResolveWithParentDirectiveMultiLevel(): void
    {
        $this->createTemplate('base.tpl', '<div>[% block content %]Base[% endblock %]</div>');
        $this->createTemplate('middle.tpl', "[% extends 'base.tpl' %][% block content %]Middle([% parent %])[% endblock %]");

        $source = "[% extends 'middle.tpl' %][% block content %]Child([% parent %])[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<div>Child(Middle(Base))</div>', $result);
    }

    public function testResolveWithDoubleQuotes(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');

        $source = '[% extends "base.tpl" %][% block content %]custom[% endblock %]';

        $result = $this->resolver->resolve($source);

        $this->assertSame('<html>custom</html>', $result);
    }

    public function testResolveWithAutoExtension(): void
    {
        $this->createTemplate('base.tpl', '<html>[% block content %]default[% endblock %]</html>');

        $source = "[% extends 'base' %][% block content %]custom[% endblock %]";

        $result = $this->resolver->resolve($source);

        $this->assertSame('<html>custom</html>', $result);
    }
}
