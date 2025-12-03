<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\RandomMacro;
use PHPUnit\Framework\TestCase;

class RandomMacroTest extends TestCase
{
    private RandomMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new RandomMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('random', $this->macro->getName());
    }

    public function testExecuteDefaultRange(): void
    {
        $result = $this->macro->execute([]);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
        $this->assertLessThanOrEqual(100, $result);
    }

    public function testExecuteCustomRange(): void
    {
        $result = $this->macro->execute([1, 10]);
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(1, $result);
        $this->assertLessThanOrEqual(10, $result);
    }

    public function testExecuteRandomString(): void
    {
        $result = $this->macro->execute(['string', 16]);
        $this->assertIsString($result);
        $this->assertSame(16, \strlen($result));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $result);
    }

    public function testExecuteRandomHex(): void
    {
        $result = $this->macro->execute(['hex', 32]);
        $this->assertIsString($result);
        $this->assertGreaterThanOrEqual(32, \strlen($result));
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $result);
    }

    public function testExecuteRandomAlpha(): void
    {
        $result = $this->macro->execute(['alpha', 8]);
        $this->assertIsString($result);
        $this->assertSame(8, \strlen($result));
        $this->assertMatchesRegularExpression('/^[a-zA-Z]+$/', $result);
    }

    public function testExecuteRandomAlnum(): void
    {
        $result = $this->macro->execute(['alnum', 12]);
        $this->assertIsString($result);
        $this->assertSame(12, \strlen($result));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $result);
    }

    public function testExecuteRandomToken(): void
    {
        $result = $this->macro->execute(['token', 24]);
        $this->assertIsString($result);
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9_-]+$/', $result);
    }

    public function testExecuteDefaultStringLength(): void
    {
        $result = $this->macro->execute(['string']);
        $this->assertIsString($result);
        $this->assertSame(16, \strlen($result));
    }

    public function testExecuteMinimumLength(): void
    {
        $result = $this->macro->execute(['string', 0]);
        $this->assertIsString($result);
        $this->assertSame(1, \strlen($result));
    }

    public function testExecuteNegativeLength(): void
    {
        $result = $this->macro->execute(['string', -5]);
        $this->assertIsString($result);
        $this->assertSame(1, \strlen($result));
    }

    public function testExecuteUnknownTypeDefaultsToString(): void
    {
        $result = $this->macro->execute(['unknown', 10]);
        $this->assertIsString($result);
        $this->assertSame(10, \strlen($result));
    }
}
