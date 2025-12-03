<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\IconMacro;
use PHPUnit\Framework\TestCase;

class IconMacroTest extends TestCase
{
    private IconMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new IconMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('icon', $this->macro->getName());
    }

    public function testExecuteDefaultHeroicon(): void
    {
        $result = $this->macro->execute(['user']);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('icon-user', $result);
    }

    public function testExecuteHeroiconSolid(): void
    {
        $result = $this->macro->execute(['home', 'solid']);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('icon-solid', $result);
    }

    public function testExecuteFontAwesome(): void
    {
        $result = $this->macro->execute(['fa-home', 'fa']);
        $this->assertStringContainsString('<i', $result);
        $this->assertStringContainsString('fa-home', $result);
    }

    public function testExecuteMaterialDesignIcons(): void
    {
        $result = $this->macro->execute(['mdi-account', 'mdi']);
        $this->assertStringContainsString('<i', $result);
        $this->assertStringContainsString('mdi-account', $result);
    }

    public function testExecuteBootstrapIcons(): void
    {
        $result = $this->macro->execute(['bi-person', 'bi']);
        $this->assertStringContainsString('<i', $result);
        $this->assertStringContainsString('bi-person', $result);
    }

    public function testExecuteLucide(): void
    {
        $result = $this->macro->execute(['user', 'lucide']);
        $this->assertStringContainsString('<svg', $result);
        $this->assertStringContainsString('lucide', $result);
    }

    public function testExecuteWithSize(): void
    {
        $result = $this->macro->execute(['user', 'outline', '32px']);
        $this->assertStringContainsString('32px', $result);
    }

    public function testExecuteWithClass(): void
    {
        $result = $this->macro->execute(['user', 'outline', '1em', 'text-primary']);
        $this->assertStringContainsString('text-primary', $result);
    }

    public function testExecuteEmptyName(): void
    {
        $result = $this->macro->execute(['']);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }
}
