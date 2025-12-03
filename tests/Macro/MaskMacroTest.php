<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\MaskMacro;
use PHPUnit\Framework\TestCase;

class MaskMacroTest extends TestCase
{
    private MaskMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new MaskMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('mask', $this->macro->getName());
    }

    public function testExecuteEmail(): void
    {
        $result = $this->macro->execute(['john.doe@example.com', 'email']);
        $this->assertStringContainsString('j', $result);
        $this->assertStringContainsString('@', $result);
        $this->assertStringContainsString('*', $result);
    }

    public function testExecutePhone(): void
    {
        $result = $this->macro->execute(['1234567890', 'phone']);
        $this->assertStringContainsString('*', $result);
        $this->assertSame(10, \strlen(str_replace(['*', '-', ' ', '(', ')'], '', $result)) + substr_count($result, '*'));
    }

    public function testExecuteCard(): void
    {
        $result = $this->macro->execute(['4111111111111111', 'card']);
        $this->assertStringContainsString('*', $result);
        $this->assertStringContainsString('1111', $result);
    }

    public function testExecuteDefault(): void
    {
        $result = $this->macro->execute(['sensitive data']);
        $this->assertStringContainsString('*', $result);
    }

    public function testExecuteCustomMask(): void
    {
        $result = $this->macro->execute(['password123', 'custom', '#', 4]);
        $this->assertStringContainsString('#', $result);
    }

    public function testExecuteEmptyString(): void
    {
        $result = $this->macro->execute(['']);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteShortEmail(): void
    {
        $result = $this->macro->execute(['a@b.com', 'email']);
        $this->assertStringContainsString('@', $result);
    }
}
