<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\GravatarMacro;
use PHPUnit\Framework\TestCase;

class GravatarMacroTest extends TestCase
{
    private GravatarMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new GravatarMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('gravatar', $this->macro->getName());
    }

    public function testExecuteWithEmail(): void
    {
        $result = $this->macro->execute(['test@example.com']);
        $this->assertStringContainsString('https://www.gravatar.com/avatar/', $result);
        $this->assertStringContainsString(md5('test@example.com'), $result);
    }

    public function testExecuteWithSize(): void
    {
        $result = $this->macro->execute(['test@example.com', 200]);
        $this->assertStringContainsString('s=200', $result);
    }

    public function testExecuteDefaultSize(): void
    {
        $result = $this->macro->execute(['test@example.com']);
        $this->assertStringContainsString('s=80', $result);
    }

    public function testExecuteWithDefault(): void
    {
        $result = $this->macro->execute(['test@example.com', 80, 'identicon']);
        $this->assertStringContainsString('d=identicon', $result);
    }

    public function testExecuteDefaultAvatar(): void
    {
        $result = $this->macro->execute(['test@example.com']);
        $this->assertStringContainsString('d=mp', $result);
    }

    public function testExecuteTrimsEmail(): void
    {
        $result1 = $this->macro->execute(['test@example.com']);
        $result2 = $this->macro->execute(['  test@example.com  ']);
        $this->assertSame($result1, $result2);
    }

    public function testExecuteLowercasesEmail(): void
    {
        $result1 = $this->macro->execute(['test@example.com']);
        $result2 = $this->macro->execute(['TEST@EXAMPLE.COM']);
        $this->assertSame($result1, $result2);
    }

    public function testExecuteEmptyEmailStillReturnsUrl(): void
    {
        $result = $this->macro->execute(['']);
        $this->assertStringContainsString('gravatar.com', $result);
        $this->assertStringContainsString(md5(''), $result);
    }

    public function testExecuteEmptyArgsStillReturnsUrl(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('gravatar.com', $result);
    }
}
