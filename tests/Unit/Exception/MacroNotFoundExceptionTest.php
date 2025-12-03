<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Exception;

use Lunar\Template\Exception\MacroNotFoundException;
use Lunar\Template\Exception\TemplateException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MacroNotFoundExceptionTest extends TestCase
{
    public function testExceptionMessage(): void
    {
        $exception = new MacroNotFoundException('myMacro');

        $this->assertStringContainsString('Macro "myMacro" is not defined', $exception->getMessage());
    }

    public function testGetMacroName(): void
    {
        $exception = new MacroNotFoundException('testMacro');

        $this->assertSame('testMacro', $exception->getMacroName());
    }

    public function testExtendsTemplateException(): void
    {
        $exception = new MacroNotFoundException('macro');

        $this->assertInstanceOf(TemplateException::class, $exception);
    }

    public function testPreviousException(): void
    {
        $previous = new RuntimeException('Previous error');
        $exception = new MacroNotFoundException('macro', $previous);

        $this->assertSame($previous, $exception->getPrevious());
    }
}
