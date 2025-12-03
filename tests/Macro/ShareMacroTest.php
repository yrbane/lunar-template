<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\ShareMacro;
use PHPUnit\Framework\TestCase;

class ShareMacroTest extends TestCase
{
    private ShareMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new ShareMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('share', $this->macro->getName());
    }

    public function testExecuteTwitter(): void
    {
        $result = $this->macro->execute(['twitter', 'https://example.com', 'Check this out!']);
        $this->assertStringContainsString('twitter.com', $result);
        $this->assertStringContainsString('example.com', $result);
    }

    public function testExecuteFacebook(): void
    {
        $result = $this->macro->execute(['facebook', 'https://example.com']);
        $this->assertStringContainsString('facebook.com', $result);
        $this->assertStringContainsString('example.com', $result);
    }

    public function testExecuteLinkedin(): void
    {
        $result = $this->macro->execute(['linkedin', 'https://example.com']);
        $this->assertStringContainsString('linkedin.com', $result);
    }

    public function testExecuteEmail(): void
    {
        $result = $this->macro->execute(['email', 'https://example.com', 'Check this!', 'Cool link']);
        $this->assertStringContainsString('mailto:', $result);
        $this->assertStringContainsString('subject=', $result);
    }

    public function testExecuteWhatsapp(): void
    {
        $result = $this->macro->execute(['whatsapp', 'https://example.com', 'Check this!']);
        $this->assertStringContainsString('wa.me', $result);
    }

    public function testExecuteTelegram(): void
    {
        $result = $this->macro->execute(['telegram', 'https://example.com']);
        $this->assertStringContainsString('t.me', $result);
    }

    public function testExecuteReddit(): void
    {
        $result = $this->macro->execute(['reddit', 'https://example.com', 'Cool post']);
        $this->assertStringContainsString('reddit.com', $result);
    }

    public function testExecuteUnknownPlatform(): void
    {
        $result = $this->macro->execute(['unknown', 'https://example.com']);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteEncodesUrl(): void
    {
        $result = $this->macro->execute(['twitter', 'https://example.com?foo=bar&baz=qux']);
        $this->assertStringContainsString('example.com', $result);
    }

    public function testExecutePinterest(): void
    {
        $result = $this->macro->execute(['pinterest', 'https://example.com', 'Description', 'https://example.com/img.jpg']);
        $this->assertStringContainsString('pinterest.com', $result);
    }

    public function testExecuteCopy(): void
    {
        $result = $this->macro->execute(['copy', 'https://example.com']);
        $this->assertSame('https://example.com', $result);
    }
}
