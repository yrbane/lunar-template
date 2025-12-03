<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\CountdownMacro;
use PHPUnit\Framework\TestCase;

class CountdownMacroTest extends TestCase
{
    private CountdownMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new CountdownMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('countdown', $this->macro->getName());
    }

    public function testExecuteWithFutureDate(): void
    {
        $futureDate = date('Y-m-d', strtotime('+10 days'));
        $result = $this->macro->execute([$futureDate]);
        $this->assertStringContainsString('day', $result);
    }

    public function testExecuteWithPastDate(): void
    {
        $pastDate = date('Y-m-d', strtotime('-1 day'));
        $result = $this->macro->execute([$pastDate]);
        $this->assertSame('Event passed', $result);
    }

    public function testExecuteFullFormat(): void
    {
        $futureDate = date('Y-m-d H:i:s', strtotime('+1 day +2 hours +30 minutes'));
        $result = $this->macro->execute([$futureDate, 'full']);
        $this->assertMatchesRegularExpression('/\d+d \d+h \d+m/', $result);
    }

    public function testExecuteDaysFormat(): void
    {
        $futureDate = date('Y-m-d', strtotime('+5 days'));
        $result = $this->macro->execute([$futureDate, 'days']);
        $this->assertMatchesRegularExpression('/\d+ days/', $result);
    }

    public function testExecuteHoursFormat(): void
    {
        $futureDate = date('Y-m-d H:i:s', strtotime('+48 hours'));
        $result = $this->macro->execute([$futureDate, 'hours']);
        $this->assertMatchesRegularExpression('/\d+ hours/', $result);
    }

    public function testExecuteWithTimestamp(): void
    {
        $result = $this->macro->execute([time() + 86400]);
        $this->assertStringContainsString('hour', $result);
    }

    public function testExecuteWithDateTimeObject(): void
    {
        $date = new \DateTimeImmutable('+3 days');
        $result = $this->macro->execute([$date]);
        $this->assertStringContainsString('day', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteInvalidDate(): void
    {
        $result = $this->macro->execute(['not-a-date']);
        $this->assertSame('', $result);
    }
}
