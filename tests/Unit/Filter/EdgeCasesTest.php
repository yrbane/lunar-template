<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use ArrayObject;
use Lunar\Template\Filter\AbstractFilter;
use Lunar\Template\Filter\Array\FilterArrayFilter;
use Lunar\Template\Filter\Array\GroupByFilter;
use Lunar\Template\Filter\Array\PluckFilter;
use Lunar\Template\Filter\Array\SortFilter;
use PHPUnit\Framework\TestCase;
use stdClass;

class EdgeCasesTest extends TestCase
{
    public function testAbstractFilterToStringWithObject(): void
    {
        $filter = new class () extends AbstractFilter {
            public function getName(): string
            {
                return 'test';
            }

            public function apply(mixed $value, array $args = []): string
            {
                return $this->toString($value);
            }
        };

        // Object with __toString
        $obj = new class () {
            public function __toString(): string
            {
                return 'stringified';
            }
        };
        $this->assertSame('stringified', $filter->apply($obj));

        // Boolean
        $this->assertSame('true', $filter->apply(true));
        $this->assertSame('false', $filter->apply(false));

        // Null
        $this->assertSame('', $filter->apply(null));

        // Array
        $this->assertSame('a, b, c', $filter->apply(['a', 'b', 'c']));

        // Object without __toString
        $plainObj = new stdClass();
        $this->assertSame('', $filter->apply($plainObj));
    }

    public function testSortFilterWithObjects(): void
    {
        $filter = new SortFilter();

        $items = [
            (object) ['name' => 'Bob'],
            (object) ['name' => 'Alice'],
        ];
        $sorted = $filter->apply($items, ['name']);

        $this->assertSame('Alice', $sorted[0]->name);
    }

    public function testSortFilterDescending(): void
    {
        $filter = new SortFilter();

        $items = [
            ['name' => 'Alice'],
            ['name' => 'Bob'],
        ];
        $sorted = $filter->apply($items, ['name', 'desc']);

        $this->assertSame('Bob', $sorted[0]['name']);
    }

    public function testSortFilterNonArray(): void
    {
        $filter = new SortFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testPluckFilterWithObjects(): void
    {
        $filter = new PluckFilter();

        $items = [
            (object) ['name' => 'Alice'],
            (object) ['name' => 'Bob'],
        ];
        $this->assertSame(['Alice', 'Bob'], $filter->apply($items, ['name']));
    }

    public function testPluckFilterNonArray(): void
    {
        $filter = new PluckFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testFilterArrayFilterWithObjects(): void
    {
        $filter = new FilterArrayFilter();

        $items = [
            (object) ['active' => true, 'name' => 'A'],
            (object) ['active' => false, 'name' => 'B'],
        ];
        $filtered = $filter->apply($items, ['active', true]);
        $this->assertCount(1, $filtered);
    }

    public function testFilterArrayFilterWithKeyOnly(): void
    {
        $filter = new FilterArrayFilter();

        $items = [
            ['active' => true, 'name' => 'A'],
            ['active' => false, 'name' => 'B'],
            ['active' => '', 'name' => 'C'],
        ];
        $filtered = $filter->apply($items, ['active']);
        $this->assertCount(1, $filtered);
    }

    public function testGroupByFilterWithObjects(): void
    {
        $filter = new GroupByFilter();

        $items = [
            (object) ['type' => 'fruit', 'name' => 'apple'],
            (object) ['type' => 'veggie', 'name' => 'carrot'],
        ];
        $grouped = $filter->apply($items, ['type']);

        $this->assertCount(1, $grouped['fruit']);
        $this->assertCount(1, $grouped['veggie']);
    }

    public function testGroupByFilterNonArray(): void
    {
        $filter = new GroupByFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testGroupByFilterEmptyKey(): void
    {
        $filter = new GroupByFilter();

        $this->assertSame([], $filter->apply([['name' => 'a']], ['']));
    }

    public function testLengthFilterWithCountable(): void
    {
        $filter = new \Lunar\Template\Filter\Array\LengthFilter();

        $countable = new ArrayObject([1, 2, 3]);
        $this->assertSame(3, $filter->apply($countable));
    }

    public function testRandomFilterNull(): void
    {
        $filter = new \Lunar\Template\Filter\Array\RandomFilter();

        $this->assertNull($filter->apply(null));
    }

    public function testRandomFilterEmptyString(): void
    {
        $filter = new \Lunar\Template\Filter\Array\RandomFilter();

        $this->assertNull($filter->apply(''));
    }

    public function testShuffleFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\ShuffleFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testMergeFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\MergeFilter();

        $this->assertSame([1, 2], $filter->apply('not array', [[1, 2]]));
    }

    public function testMergeFilterWithNonArrayArg(): void
    {
        $filter = new \Lunar\Template\Filter\Array\MergeFilter();

        $this->assertSame([1, 2], $filter->apply([1, 2], ['not array']));
    }

    public function testUniqueFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\UniqueFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testKeysFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\KeysFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testValuesFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\ValuesFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testChunkFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\ChunkFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testFilterArrayFilterNonArray(): void
    {
        $filter = new FilterArrayFilter();

        $this->assertSame([], $filter->apply('not array'));
    }

    public function testMapFilterNonArray(): void
    {
        $filter = new \Lunar\Template\Filter\Array\MapFilter(
            \Lunar\Template\Filter\DefaultFilters::create(),
        );

        $this->assertSame([], $filter->apply('not array'));
    }
}
