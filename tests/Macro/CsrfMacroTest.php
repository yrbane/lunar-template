<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\CsrfMacro;
use PHPUnit\Framework\TestCase;

class CsrfMacroTest extends TestCase
{
    private CsrfMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new CsrfMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('csrf', $this->macro->getName());
    }

    public function testExecuteDefaultField(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('<input', $result);
        $this->assertStringContainsString('type="hidden"', $result);
        $this->assertStringContainsString('name="_token"', $result);
        $this->assertStringContainsString('value="', $result);
    }

    public function testExecuteTokenOnly(): void
    {
        $result = $this->macro->execute(['token']);
        $this->assertStringNotContainsString('<input', $result);
        $this->assertNotEmpty($result);
    }

    public function testExecuteMeta(): void
    {
        $result = $this->macro->execute(['meta']);
        $this->assertStringContainsString('<meta', $result);
        $this->assertStringContainsString('name="csrf-token"', $result);
        $this->assertStringContainsString('content="', $result);
    }

    public function testExecuteTokensAreUnique(): void
    {
        $macro1 = new CsrfMacro();
        $macro2 = new CsrfMacro();
        $token1 = $macro1->execute(['token']);
        $token2 = $macro2->execute(['token']);
        $this->assertNotSame($token1, $token2);
    }

    public function testExecuteTokenLength(): void
    {
        $result = $this->macro->execute(['token']);
        $this->assertGreaterThanOrEqual(32, \strlen($result));
    }
}
