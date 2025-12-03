<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use DateTimeImmutable;
use Lunar\Template\Filter\Date\AgoFilter;
use Lunar\Template\Filter\Date\DateFilter;
use Lunar\Template\Filter\Date\RelativeDateFilter;
use PHPUnit\Framework\TestCase;

class DateFiltersTest extends TestCase
{
    public function testDateFilter(): void
    {
        $filter = new DateFilter();

        $this->assertSame('date', $filter->getName());
        $this->assertSame('2024-06-15', $filter->apply('2024-06-15', ['Y-m-d']));
        $this->assertSame('June 15, 2024', $filter->apply('2024-06-15', ['F j, Y']));
    }

    public function testDateFilterWithTimestamp(): void
    {
        $filter = new DateFilter();
        $timestamp = mktime(12, 0, 0, 6, 15, 2024);

        $this->assertSame('2024-06-15', $filter->apply($timestamp, ['Y-m-d']));
    }

    public function testDateFilterWithDateTime(): void
    {
        $filter = new DateFilter();
        $date = new DateTimeImmutable('2024-06-15 10:30:00');

        $this->assertSame('2024-06-15 10:30:00', $filter->apply($date, ['Y-m-d H:i:s']));
    }

    public function testDateFilterWithTimezone(): void
    {
        $filter = new DateFilter('Y-m-d', 'UTC');
        $result = $filter->apply('2024-06-15 12:00:00');

        $this->assertNotEmpty($result);
    }

    public function testDateFilterInvalidValue(): void
    {
        $filter = new DateFilter();

        $this->assertSame('', $filter->apply(null));
        $this->assertSame('', $filter->apply(''));
        $this->assertSame('', $filter->apply('invalid-date'));
    }

    public function testAgoFilter(): void
    {
        $filter = new AgoFilter();

        $this->assertSame('ago', $filter->getName());
        $this->assertSame('just now', $filter->apply(time()));
        $this->assertSame('1 minute ago', $filter->apply(time() - 60));
        $this->assertSame('5 minutes ago', $filter->apply(time() - 300));
        $this->assertSame('1 hour ago', $filter->apply(time() - 3600));
        $this->assertSame('2 hours ago', $filter->apply(time() - 7200));
        $this->assertSame('1 day ago', $filter->apply(time() - 86400));
        $this->assertSame('1 week ago', $filter->apply(time() - 604800));
    }

    public function testAgoFilterFuture(): void
    {
        $filter = new AgoFilter();

        $this->assertSame('in 1 minute', $filter->apply(time() + 60));
        $this->assertSame('in 1 hour', $filter->apply(time() + 3600));
    }

    public function testAgoFilterInvalidValue(): void
    {
        $filter = new AgoFilter();

        $this->assertSame('', $filter->apply(null));
        $this->assertSame('', $filter->apply(''));
    }

    public function testRelativeDateFilter(): void
    {
        $filter = new RelativeDateFilter();

        $this->assertSame('relative', $filter->getName());
        $this->assertSame('today', $filter->apply(date('Y-m-d')));
        $this->assertSame('yesterday', $filter->apply(date('Y-m-d', strtotime('-1 day'))));
        $this->assertSame('tomorrow', $filter->apply(date('Y-m-d', strtotime('+1 day'))));
        $this->assertSame('in 3 days', $filter->apply(date('Y-m-d', strtotime('+3 days'))));
        $this->assertSame('3 days ago', $filter->apply(date('Y-m-d', strtotime('-3 days'))));
    }

    public function testRelativeDateFilterInvalidValue(): void
    {
        $filter = new RelativeDateFilter();

        $this->assertSame('', $filter->apply(null));
    }

    public function testRelativeDateFilterFarDates(): void
    {
        $filter = new RelativeDateFilter();

        $farPast = date('Y-m-d', strtotime('-30 days'));
        $result = $filter->apply($farPast);

        $this->assertMatchesRegularExpression('/\w+ \d+, \d{4}/', $result);
    }
}
