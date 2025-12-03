<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Macro;

use DateTimeImmutable;
use Lunar\Template\Macro\DateMacro;
use Lunar\Template\Macro\MacroInterface;
use PHPUnit\Framework\TestCase;

class DateMacroTest extends TestCase
{
    private DateMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new DateMacro();
    }

    public function testImplementsMacroInterface(): void
    {
        $this->assertInstanceOf(MacroInterface::class, $this->macro);
    }

    public function testGetName(): void
    {
        $this->assertSame('date', $this->macro->getName());
    }

    public function testExecuteWithDefaultFormat(): void
    {
        $result = $this->macro->execute([]);

        // Should return current date in Y-m-d format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testExecuteWithCustomFormat(): void
    {
        $result = $this->macro->execute(['Y']);

        $this->assertSame(date('Y'), $result);
    }

    public function testExecuteWithTimestamp(): void
    {
        $timestamp = mktime(12, 0, 0, 6, 15, 2024);
        $result = $this->macro->execute(['Y-m-d', $timestamp]);

        $this->assertSame('2024-06-15', $result);
    }

    public function testExecuteWithDateTimeObject(): void
    {
        $dateTime = new DateTimeImmutable('2023-12-25 10:30:00');
        $result = $this->macro->execute(['Y-m-d H:i', $dateTime]);

        $this->assertSame('2023-12-25 10:30', $result);
    }

    public function testExecuteWithStringDate(): void
    {
        $result = $this->macro->execute(['Y-m-d', '2024-01-15']);

        $this->assertSame('2024-01-15', $result);
    }

    public function testExecuteWithNonStringFormat(): void
    {
        $result = $this->macro->execute([123]); // Invalid format

        // Should use default format
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $result);
    }

    public function testExecuteWithInvalidValueUsesNow(): void
    {
        $result = $this->macro->execute(['Y', ['invalid']]);

        $this->assertSame(date('Y'), $result);
    }

    public function testCustomDefaultFormat(): void
    {
        $macro = new DateMacro('F j, Y');
        $result = $macro->execute([]);

        // Should match "Month Day, Year" format
        $this->assertMatchesRegularExpression('/^\w+ \d{1,2}, \d{4}$/', $result);
    }

    public function testCustomTimezone(): void
    {
        $macro = new DateMacro('Y-m-d H:i', 'America/New_York');

        // Just test that it doesn't throw
        $result = $macro->execute([]);

        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $result);
    }

    public function testLongDateFormat(): void
    {
        $result = $this->macro->execute(['l, F j, Y', '2024-12-25']);

        $this->assertSame('Wednesday, December 25, 2024', $result);
    }

    public function testTimeFormat(): void
    {
        $result = $this->macro->execute(['H:i:s', '2024-01-01 14:30:45']);

        $this->assertSame('14:30:45', $result);
    }
}
