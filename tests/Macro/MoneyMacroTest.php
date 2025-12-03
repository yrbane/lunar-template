<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\MoneyMacro;
use PHPUnit\Framework\TestCase;

class MoneyMacroTest extends TestCase
{
    private MoneyMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new MoneyMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('money', $this->macro->getName());
    }

    public function testExecuteDefaultCurrency(): void
    {
        $result = $this->macro->execute([1234.56]);
        $this->assertStringContainsString('1', $result);
        $this->assertStringContainsString('234', $result);
        $this->assertStringContainsString('56', $result);
    }

    public function testExecuteUsd(): void
    {
        $result = $this->macro->execute([99.99, 'USD']);
        $this->assertStringContainsString('$', $result);
        $this->assertStringContainsString('99', $result);
    }

    public function testExecuteEur(): void
    {
        $result = $this->macro->execute([50, 'EUR']);
        $this->assertStringContainsString('€', $result);
        $this->assertStringContainsString('50', $result);
    }

    public function testExecuteGbp(): void
    {
        $result = $this->macro->execute([100, 'GBP']);
        $this->assertStringContainsString('£', $result);
        $this->assertStringContainsString('100', $result);
    }

    public function testExecuteJpy(): void
    {
        $result = $this->macro->execute([1000, 'JPY']);
        $this->assertStringContainsString('¥', $result);
        $this->assertStringContainsString('1', $result);
    }

    public function testExecuteZeroAmount(): void
    {
        $result = $this->macro->execute([0, 'USD']);
        $this->assertStringContainsString('$', $result);
        $this->assertStringContainsString('0', $result);
    }

    public function testExecuteNegativeAmount(): void
    {
        $result = $this->macro->execute([-50, 'USD']);
        $this->assertStringContainsString('-', $result);
        $this->assertStringContainsString('50', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        // Returns formatted 0 with default currency
        $this->assertStringContainsString('0', $result);
    }

    public function testExecuteUnknownCurrency(): void
    {
        $result = $this->macro->execute([100, 'XXX']);
        $this->assertStringContainsString('100', $result);
    }
}
