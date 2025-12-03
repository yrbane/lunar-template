<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Exception;

use Lunar\Template\Exception\CircularInheritanceException;
use Lunar\Template\Exception\TemplateException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CircularInheritanceExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $chain = ['base.tpl', 'child.tpl', 'grandchild.tpl', 'base.tpl'];
        $exception = new CircularInheritanceException($chain);

        $this->assertStringContainsString('Circular template inheritance detected', $exception->getMessage());
        $this->assertStringContainsString('base.tpl -> child.tpl -> grandchild.tpl -> base.tpl', $exception->getMessage());
    }

    public function testGetInheritanceChain(): void
    {
        $chain = ['a.tpl', 'b.tpl', 'a.tpl'];
        $exception = new CircularInheritanceException($chain);

        $this->assertSame($chain, $exception->getInheritanceChain());
    }

    public function testExtendsTemplateException(): void
    {
        $exception = new CircularInheritanceException(['a.tpl', 'a.tpl']);

        $this->assertInstanceOf(TemplateException::class, $exception);
    }

    public function testPreviousException(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = new CircularInheritanceException(['a.tpl'], $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
