<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Number\AbsFilter;
use Lunar\Template\Filter\Number\CeilFilter;
use Lunar\Template\Filter\Number\CurrencyFilter;
use Lunar\Template\Filter\Number\FilesizeFilter;
use Lunar\Template\Filter\Number\FloorFilter;
use Lunar\Template\Filter\Number\NumberFormatFilter;
use Lunar\Template\Filter\Number\OrdinalFilter;
use Lunar\Template\Filter\Number\PercentFilter;
use Lunar\Template\Filter\Number\RoundFilter;
use PHPUnit\Framework\TestCase;

class NumberFiltersTest extends TestCase
{
    public function testNumberFormatFilter(): void
    {
        $filter = new NumberFormatFilter();

        $this->assertSame('number_format', $filter->getName());
        $this->assertSame('1,234,568', $filter->apply(1234567.89));
        $this->assertSame('1,234,567.89', $filter->apply(1234567.89, [2]));
        $this->assertSame('1 234 567,89', $filter->apply(1234567.89, [2, ',', ' ']));
    }

    public function testRoundFilter(): void
    {
        $filter = new RoundFilter();

        $this->assertSame('round', $filter->getName());
        $this->assertSame(4.0, $filter->apply(3.7));
        $this->assertSame(3.57, $filter->apply(3.567, [2]));
    }

    public function testFloorFilter(): void
    {
        $filter = new FloorFilter();

        $this->assertSame('floor', $filter->getName());
        $this->assertSame(3.0, $filter->apply(3.7));
        $this->assertSame(-4.0, $filter->apply(-3.2));
    }

    public function testCeilFilter(): void
    {
        $filter = new CeilFilter();

        $this->assertSame('ceil', $filter->getName());
        $this->assertSame(4.0, $filter->apply(3.2));
        $this->assertSame(-3.0, $filter->apply(-3.7));
    }

    public function testAbsFilter(): void
    {
        $filter = new AbsFilter();

        $this->assertSame('abs', $filter->getName());
        $this->assertSame(5, $filter->apply(-5));
        $this->assertSame(5.5, $filter->apply(-5.5));
    }

    public function testCurrencyFilter(): void
    {
        $filter = new CurrencyFilter();

        $this->assertSame('currency', $filter->getName());
        $this->assertSame('$1,234.56', $filter->apply(1234.56));
        $this->assertSame('€1.234,56', $filter->apply(1234.56, ['€', 2, ',', '.']));
    }

    public function testPercentFilter(): void
    {
        $filter = new PercentFilter();

        $this->assertSame('percent', $filter->getName());
        $this->assertSame('50%', $filter->apply(0.5));
        $this->assertSame('50.50%', $filter->apply(0.505, [2]));
        $this->assertSame('50%', $filter->apply(50, [0, false]));
    }

    public function testOrdinalFilter(): void
    {
        $filter = new OrdinalFilter();

        $this->assertSame('ordinal', $filter->getName());
        $this->assertSame('1st', $filter->apply(1));
        $this->assertSame('2nd', $filter->apply(2));
        $this->assertSame('3rd', $filter->apply(3));
        $this->assertSame('4th', $filter->apply(4));
        $this->assertSame('11th', $filter->apply(11));
        $this->assertSame('12th', $filter->apply(12));
        $this->assertSame('13th', $filter->apply(13));
        $this->assertSame('21st', $filter->apply(21));
        $this->assertSame('22nd', $filter->apply(22));
        $this->assertSame('23rd', $filter->apply(23));
    }

    public function testFilesizeFilter(): void
    {
        $filter = new FilesizeFilter();

        $this->assertSame('filesize', $filter->getName());
        $this->assertSame('0 B', $filter->apply(0));
        $this->assertSame('500.00 B', $filter->apply(500));
        $this->assertSame('1.00 KB', $filter->apply(1024));
        $this->assertSame('1.50 MB', $filter->apply(1.5 * 1024 * 1024));
        $this->assertSame('2.5 GB', $filter->apply(2.5 * 1024 * 1024 * 1024, [1]));
    }
}
