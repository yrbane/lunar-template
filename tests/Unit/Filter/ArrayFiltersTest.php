<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\Array\ChunkFilter;
use Lunar\Template\Filter\Array\FilterArrayFilter;
use Lunar\Template\Filter\Array\FirstFilter;
use Lunar\Template\Filter\Array\GroupByFilter;
use Lunar\Template\Filter\Array\JoinFilter;
use Lunar\Template\Filter\Array\KeysFilter;
use Lunar\Template\Filter\Array\LastFilter;
use Lunar\Template\Filter\Array\LengthFilter;
use Lunar\Template\Filter\Array\MapFilter;
use Lunar\Template\Filter\Array\MergeFilter;
use Lunar\Template\Filter\Array\PluckFilter;
use Lunar\Template\Filter\Array\RandomFilter;
use Lunar\Template\Filter\Array\ShuffleFilter;
use Lunar\Template\Filter\Array\SliceFilter;
use Lunar\Template\Filter\Array\SortFilter;
use Lunar\Template\Filter\Array\UniqueFilter;
use Lunar\Template\Filter\Array\ValuesFilter;
use Lunar\Template\Filter\DefaultFilters;
use PHPUnit\Framework\TestCase;

class ArrayFiltersTest extends TestCase
{
    public function testFirstFilter(): void
    {
        $filter = new FirstFilter();

        $this->assertSame('first', $filter->getName());
        $this->assertSame(1, $filter->apply([1, 2, 3]));
        $this->assertSame('a', $filter->apply('abc'));
        $this->assertNull($filter->apply([]));
        $this->assertNull($filter->apply(123));
    }

    public function testLastFilter(): void
    {
        $filter = new LastFilter();

        $this->assertSame('last', $filter->getName());
        $this->assertSame(3, $filter->apply([1, 2, 3]));
        $this->assertSame('c', $filter->apply('abc'));
        $this->assertNull($filter->apply([]));
    }

    public function testLengthFilter(): void
    {
        $filter = new LengthFilter();

        $this->assertSame('length', $filter->getName());
        $this->assertSame(3, $filter->apply([1, 2, 3]));
        $this->assertSame(5, $filter->apply('hello'));
        $this->assertSame(0, $filter->apply(123));
    }

    public function testKeysFilter(): void
    {
        $filter = new KeysFilter();

        $this->assertSame('keys', $filter->getName());
        $this->assertSame(['a', 'b'], $filter->apply(['a' => 1, 'b' => 2]));
        $this->assertSame([], $filter->apply('not array'));
    }

    public function testValuesFilter(): void
    {
        $filter = new ValuesFilter();

        $this->assertSame('values', $filter->getName());
        $this->assertSame([1, 2], $filter->apply(['a' => 1, 'b' => 2]));
    }

    public function testSortFilter(): void
    {
        $filter = new SortFilter();

        $this->assertSame('sort', $filter->getName());
        $this->assertSame([1, 2, 3], $filter->apply([3, 1, 2]));
        $this->assertSame([3, 2, 1], $filter->apply([1, 2, 3], [null, 'desc']));

        $items = [
            ['name' => 'Bob'],
            ['name' => 'Alice'],
        ];
        $sorted = $filter->apply($items, ['name']);
        $this->assertSame('Alice', $sorted[0]['name']);
    }

    public function testSliceFilter(): void
    {
        $filter = new SliceFilter();

        $this->assertSame('slice', $filter->getName());
        $this->assertSame([2, 3], $filter->apply([1, 2, 3, 4], [1, 2]));
        $this->assertSame('llo', $filter->apply('hello', [2, 3]));
        $this->assertSame([3, 4], $filter->apply([1, 2, 3, 4], [2]));
    }

    public function testMergeFilter(): void
    {
        $filter = new MergeFilter();

        $this->assertSame('merge', $filter->getName());
        $this->assertSame([1, 2, 3, 4], $filter->apply([1, 2], [[3, 4]]));
    }

    public function testUniqueFilter(): void
    {
        $filter = new UniqueFilter();

        $this->assertSame('unique', $filter->getName());
        $this->assertSame([1, 2, 3], $filter->apply([1, 2, 2, 3, 3, 3]));
    }

    public function testJoinFilter(): void
    {
        $filter = new JoinFilter();

        $this->assertSame('join', $filter->getName());
        $this->assertSame('a, b, c', $filter->apply(['a', 'b', 'c']));
        $this->assertSame('a-b-c', $filter->apply(['a', 'b', 'c'], ['-']));
        $this->assertSame('a, b and c', $filter->apply(['a', 'b', 'c'], [', ', ' and ']));
        $this->assertSame('', $filter->apply('not array'));
    }

    public function testChunkFilter(): void
    {
        $filter = new ChunkFilter();

        $this->assertSame('chunk', $filter->getName());
        $this->assertSame([[1, 2], [3, 4]], $filter->apply([1, 2, 3, 4], [2]));
        $this->assertSame([[1], [2], [3]], $filter->apply([1, 2, 3], [0]));
    }

    public function testPluckFilter(): void
    {
        $filter = new PluckFilter();

        $this->assertSame('pluck', $filter->getName());

        $items = [
            ['name' => 'Alice'],
            ['name' => 'Bob'],
        ];
        $this->assertSame(['Alice', 'Bob'], $filter->apply($items, ['name']));
        $this->assertSame([], $filter->apply($items, ['']));
    }

    public function testFilterArrayFilter(): void
    {
        $filter = new FilterArrayFilter();

        $this->assertSame('filter', $filter->getName());
        $this->assertSame([1, 2, 3], $filter->apply([0, 1, 2, '', 3, null]));

        $items = [
            ['active' => true, 'name' => 'A'],
            ['active' => false, 'name' => 'B'],
        ];
        $filtered = $filter->apply($items, ['active', true]);
        $this->assertCount(1, $filtered);
        $this->assertSame('A', $filtered[0]['name']);
    }

    public function testMapFilter(): void
    {
        $registry = DefaultFilters::create();
        $filter = new MapFilter($registry);

        $this->assertSame('map', $filter->getName());
        $this->assertSame(['HELLO', 'WORLD'], $filter->apply(['hello', 'world'], ['upper']));
        $this->assertSame(['a', 'b'], $filter->apply(['a', 'b'], ['nonexistent']));
        $this->assertSame(['a', 'b'], $filter->apply(['a', 'b'], ['']));
    }

    public function testGroupByFilter(): void
    {
        $filter = new GroupByFilter();

        $this->assertSame('group_by', $filter->getName());

        $items = [
            ['type' => 'fruit', 'name' => 'apple'],
            ['type' => 'fruit', 'name' => 'banana'],
            ['type' => 'veggie', 'name' => 'carrot'],
        ];
        $grouped = $filter->apply($items, ['type']);

        $this->assertCount(2, $grouped['fruit']);
        $this->assertCount(1, $grouped['veggie']);
    }

    public function testRandomFilter(): void
    {
        $filter = new RandomFilter();

        $this->assertSame('random', $filter->getName());
        $this->assertContains($filter->apply([1, 2, 3]), [1, 2, 3]);
        $this->assertContains($filter->apply('abc'), ['a', 'b', 'c']);
        $this->assertNull($filter->apply([]));

        $random = $filter->apply(10, [1]);
        $this->assertGreaterThanOrEqual(1, $random);
        $this->assertLessThanOrEqual(10, $random);
    }

    public function testShuffleFilter(): void
    {
        $filter = new ShuffleFilter();

        $this->assertSame('shuffle', $filter->getName());

        $result = $filter->apply([1, 2, 3]);
        $this->assertCount(3, $result);
        $this->assertContains(1, $result);
        $this->assertContains(2, $result);
        $this->assertContains(3, $result);
    }
}
