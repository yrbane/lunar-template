<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Exception;

use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TemplateNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new TemplateNotFoundException('/path/to/template.tpl');

        $this->assertStringContainsString('Template not found', $exception->getMessage());
        $this->assertStringContainsString('/path/to/template.tpl', $exception->getMessage());
    }

    public function testExtendsTemplateException(): void
    {
        $exception = new TemplateNotFoundException('/path/to/template.tpl');

        $this->assertInstanceOf(TemplateException::class, $exception);
    }

    public function testPreviousException(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = new TemplateNotFoundException('/path/to/template.tpl', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
