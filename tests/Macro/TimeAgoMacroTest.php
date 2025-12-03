<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\TimeAgoMacro;
use PHPUnit\Framework\TestCase;

class TimeAgoMacroTest extends TestCase
{
    private TimeAgoMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new TimeAgoMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('timeago', $this->macro->getName());
    }

    public function testExecuteWithTimestamp(): void
    {
        $result = $this->macro->execute([time() - 3600]);
        $this->assertStringContainsString('<time', $result);
        $this->assertStringContainsString('1 hour ago', $result);
        $this->assertStringContainsString('</time>', $result);
    }

    public function testExecuteWithDateString(): void
    {
        $result = $this->macro->execute([date('Y-m-d H:i:s', time() - 86400)]);
        $this->assertStringContainsString('<time', $result);
        $this->assertStringContainsString('1 day ago', $result);
    }

    public function testExecuteWithFutureDate(): void
    {
        $result = $this->macro->execute([time() + 3600]);
        $this->assertStringContainsString('in 1 hour', $result);
    }

    public function testExecuteJustNow(): void
    {
        $result = $this->macro->execute([time() - 2]);
        $this->assertStringContainsString('just now', $result);
    }

    public function testExecuteShortFormat(): void
    {
        $result = $this->macro->execute([time() - 3600, 'short']);
        $this->assertStringContainsString('1h ago', $result);
    }

    public function testExecuteMinutes(): void
    {
        $result = $this->macro->execute([time() - 300]);
        $this->assertStringContainsString('5 minutes ago', $result);
    }

    public function testExecuteDays(): void
    {
        $result = $this->macro->execute([time() - 172800]);
        $this->assertStringContainsString('2 days ago', $result);
    }

    public function testExecuteWithDateTimeObject(): void
    {
        $date = new \DateTimeImmutable('-1 week');
        $result = $this->macro->execute([$date]);
        $this->assertStringContainsString('1 week ago', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteWithNullValue(): void
    {
        $result = $this->macro->execute([null]);
        $this->assertSame('', $result);
    }
}
