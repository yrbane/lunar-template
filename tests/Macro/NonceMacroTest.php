<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\NonceMacro;
use PHPUnit\Framework\TestCase;

class NonceMacroTest extends TestCase
{
    private NonceMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new NonceMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('nonce', $this->macro->getName());
    }

    public function testExecuteDefault(): void
    {
        $result = $this->macro->execute([]);
        $this->assertNotEmpty($result);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9+\/=]+$/', $result);
    }

    public function testExecuteScriptAttribute(): void
    {
        $result = $this->macro->execute(['script']);
        $this->assertStringContainsString('nonce="', $result);
    }

    public function testExecuteStyleAttribute(): void
    {
        $result = $this->macro->execute(['style']);
        $this->assertStringContainsString('nonce="', $result);
    }

    public function testExecuteConsistentNonce(): void
    {
        $nonce1 = $this->macro->execute([]);
        $nonce2 = $this->macro->execute([]);
        $this->assertSame($nonce1, $nonce2);
    }

    public function testExecuteUniqueNoncesPerInstance(): void
    {
        $macro1 = new NonceMacro();
        $macro2 = new NonceMacro();
        $nonce1 = $macro1->execute([]);
        $nonce2 = $macro2->execute([]);
        $this->assertNotSame($nonce1, $nonce2);
    }
}
