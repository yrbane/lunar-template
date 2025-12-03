<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\NowMacro;
use PHPUnit\Framework\TestCase;

class NowMacroTest extends TestCase
{
    private NowMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new NowMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('now', $this->macro->getName());
    }

    public function testExecuteDefaultTimestamp(): void
    {
        $result = $this->macro->execute([]);
        $this->assertIsInt($result);
        $this->assertEqualsWithDelta(time(), $result, 2);
    }

    public function testExecuteCustomFormat(): void
    {
        $result = $this->macro->execute(['Y-m-d']);
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testExecuteIsoFormat(): void
    {
        $result = $this->macro->execute(['iso']);
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result);
    }

    public function testExecuteRfcFormat(): void
    {
        $result = $this->macro->execute(['rfc']);
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[A-Z][a-z]{2}, \d{2} [A-Z][a-z]{2} \d{4}/', $result);
    }

    public function testExecuteAtomFormat(): void
    {
        $result = $this->macro->execute(['atom']);
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $result);
    }

    public function testExecuteTimestampFormat(): void
    {
        $result = $this->macro->execute(['timestamp']);
        $this->assertIsString($result);
        $this->assertEqualsWithDelta(time(), (int) $result, 2);
    }

    public function testExecuteWithTimezone(): void
    {
        $macro = new NowMacro('America/New_York');
        $result = $macro->execute(['e']);
        $this->assertSame('America/New_York', $result);
    }
}
