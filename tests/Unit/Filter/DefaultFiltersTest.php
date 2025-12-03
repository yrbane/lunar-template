<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Filter\DefaultFilters;
use Lunar\Template\Filter\FilterRegistry;
use PHPUnit\Framework\TestCase;

class DefaultFiltersTest extends TestCase
{
    public function testCreate(): void
    {
        $registry = DefaultFilters::create();

        $this->assertInstanceOf(FilterRegistry::class, $registry);
        $this->assertGreaterThan(40, $registry->count());
    }

    public function testRegister(): void
    {
        $registry = new FilterRegistry();
        DefaultFilters::register($registry);

        $this->assertGreaterThan(40, $registry->count());
    }

    public function testAllFiltersAreRegistered(): void
    {
        $registry = DefaultFilters::create();
        $expectedFilters = [
            // String
            'upper', 'lower', 'capitalize', 'title', 'trim', 'ltrim', 'rtrim',
            'slug', 'truncate', 'wordwrap', 'reverse', 'repeat', 'pad_left',
            'pad_right', 'replace', 'split', 'excerpt',
            // Number
            'number_format', 'round', 'floor', 'ceil', 'abs', 'currency',
            'percent', 'ordinal', 'filesize',
            // Array
            'first', 'last', 'length', 'keys', 'values', 'sort', 'slice',
            'merge', 'unique', 'join', 'chunk', 'pluck', 'filter', 'map',
            'group_by', 'random', 'shuffle',
            // Date
            'date', 'ago', 'relative',
            // Encoding
            'base64_encode', 'base64_decode', 'url_encode', 'url_decode',
            'json_encode', 'json_decode', 'md5', 'sha1', 'sha256',
            // HTML
            'raw', 'escape', 'striptags', 'nl2br', 'spaceless',
        ];

        foreach ($expectedFilters as $filterName) {
            $this->assertTrue($registry->has($filterName), "Filter '$filterName' should be registered");
        }
    }
}
