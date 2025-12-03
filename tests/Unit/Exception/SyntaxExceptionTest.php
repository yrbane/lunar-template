<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Exception;

use Lunar\Template\Exception\SyntaxException;
use Lunar\Template\Exception\TemplateException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SyntaxExceptionTest extends TestCase
{
    public function testBasicMessage(): void
    {
        $exception = new SyntaxException('Unexpected token');

        $this->assertSame('Unexpected token', $exception->getMessage());
        $this->assertSame(0, $exception->getLineNumber());
        $this->assertSame('', $exception->getTemplatePath());
    }

    public function testMessageWithTemplatePath(): void
    {
        $exception = new SyntaxException('Unexpected token', '/path/to/template.tpl');

        $this->assertStringContainsString('Unexpected token', $exception->getMessage());
        $this->assertStringContainsString('/path/to/template.tpl', $exception->getMessage());
        $this->assertSame('/path/to/template.tpl', $exception->getTemplatePath());
    }

    public function testMessageWithLineNumber(): void
    {
        $exception = new SyntaxException('Unexpected token', '/path/to/template.tpl', 42);

        $this->assertStringContainsString('Unexpected token', $exception->getMessage());
        $this->assertStringContainsString('line 42', $exception->getMessage());
        $this->assertSame(42, $exception->getLineNumber());
    }

    public function testExtendsTemplateException(): void
    {
        $exception = new SyntaxException('Error');

        $this->assertInstanceOf(TemplateException::class, $exception);
    }

    public function testPreviousException(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = new SyntaxException('Error', '', 0, $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
