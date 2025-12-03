<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\PluralizeMacro;
use PHPUnit\Framework\TestCase;

class PluralizeMacroTest extends TestCase
{
    private PluralizeMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new PluralizeMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('pluralize', $this->macro->getName());
    }

    public function testExecuteSingular(): void
    {
        $result = $this->macro->execute([1, 'item', 'items']);
        $this->assertSame('1 item', $result);
    }

    public function testExecutePlural(): void
    {
        $result = $this->macro->execute([5, 'item', 'items']);
        $this->assertSame('5 items', $result);
    }

    public function testExecuteZero(): void
    {
        $result = $this->macro->execute([0, 'item', 'items']);
        $this->assertSame('0 items', $result);
    }

    public function testExecuteNegativeOne(): void
    {
        $result = $this->macro->execute([-1, 'item', 'items']);
        $this->assertSame('-1 item', $result);
    }

    public function testExecuteNegativeMany(): void
    {
        $result = $this->macro->execute([-5, 'item', 'items']);
        $this->assertSame('-5 items', $result);
    }

    public function testExecuteWithDefaultPlural(): void
    {
        $result = $this->macro->execute([5, 'cat']);
        $this->assertSame('5 cats', $result);
    }

    public function testExecuteWithDefaultSingular(): void
    {
        $result = $this->macro->execute([1, 'cat']);
        $this->assertSame('1 cat', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('0 s', $result);
    }

    public function testExecuteOnlyCount(): void
    {
        $result = $this->macro->execute([5]);
        $this->assertSame('5 s', $result);
    }
}
