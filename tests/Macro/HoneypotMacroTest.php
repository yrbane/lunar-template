<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\HoneypotMacro;
use PHPUnit\Framework\TestCase;

class HoneypotMacroTest extends TestCase
{
    private HoneypotMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new HoneypotMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('honeypot', $this->macro->getName());
    }

    public function testExecuteDefault(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('<input', $result);
        $this->assertStringContainsString('type="text"', $result);
        $this->assertStringContainsString('name="website_url"', $result);
        $this->assertStringContainsString('position:absolute', $result);
    }

    public function testExecuteCustomName(): void
    {
        $result = $this->macro->execute(['website']);
        $this->assertStringContainsString('name="website"', $result);
    }

    public function testExecuteHiddenOffscreen(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('left:-9999px', $result);
    }

    public function testExecuteEmptyValue(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('value=""', $result);
    }

    public function testExecuteTabindexNegative(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('tabindex="-1"', $result);
    }

    public function testExecuteAutocompleteOff(): void
    {
        $result = $this->macro->execute([]);
        $this->assertStringContainsString('autocomplete="off"', $result);
    }
}
