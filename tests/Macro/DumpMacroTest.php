<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\DumpMacro;
use PHPUnit\Framework\TestCase;

class DumpMacroTest extends TestCase
{
    private DumpMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new DumpMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('dump', $this->macro->getName());
    }

    public function testExecuteWithString(): void
    {
        $result = $this->macro->execute(['Hello World']);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('Hello World', $result);
        $this->assertStringContainsString('</pre>', $result);
    }

    public function testExecuteWithArray(): void
    {
        $result = $this->macro->execute([['a' => 1, 'b' => 2]]);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('[', $result);
        $this->assertStringContainsString('a', $result);
        $this->assertStringContainsString('b', $result);
        $this->assertStringContainsString('</pre>', $result);
    }

    public function testExecuteWithInteger(): void
    {
        $result = $this->macro->execute([42]);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('42', $result);
    }

    public function testExecuteWithNull(): void
    {
        $result = $this->macro->execute([null]);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('null', $result);
    }

    public function testExecuteWithBoolean(): void
    {
        $result = $this->macro->execute([true]);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('true', $result);
    }

    public function testExecuteWithEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('<pre', $result);
        $this->assertStringContainsString('</pre>', $result);
    }

    public function testExecuteContainsDebugStyles(): void
    {
        $result = $this->macro->execute(['test']);
        $this->assertStringContainsString('background', $result);
        $this->assertStringContainsString('font-family', $result);
    }

    public function testExecuteMultipleValues(): void
    {
        $result = $this->macro->execute(['first', 'second', 42]);
        $this->assertStringContainsString('[$0]', $result);
        $this->assertStringContainsString('[$1]', $result);
        $this->assertStringContainsString('[$2]', $result);
    }
}
