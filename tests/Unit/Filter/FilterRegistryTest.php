<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Filter;

use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Filter\FilterInterface;
use Lunar\Template\Filter\FilterRegistry;
use PHPUnit\Framework\TestCase;

class FilterRegistryTest extends TestCase
{
    private FilterRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new FilterRegistry();
    }

    public function testRegisterFilter(): void
    {
        $this->registry->register('upper', fn (string $value) => strtoupper($value));

        $this->assertTrue($this->registry->has('upper'));
    }

    public function testRegisterInstance(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getName')->willReturn('test');

        $this->registry->registerInstance($filter);

        $this->assertTrue($this->registry->has('test'));
    }

    public function testHasReturnsFalseForUnregistered(): void
    {
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testGetFilter(): void
    {
        $callback = fn (string $value) => strtoupper($value);
        $this->registry->register('upper', $callback);

        $this->assertSame($callback, $this->registry->get('upper'));
    }

    public function testGetThrowsForUnregistered(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage("Filter 'nonexistent' is not registered");

        $this->registry->get('nonexistent');
    }

    public function testApplyCallable(): void
    {
        $this->registry->register('upper', fn (string $value) => strtoupper($value));

        $result = $this->registry->apply('upper', 'hello');

        $this->assertSame('HELLO', $result);
    }

    public function testApplyCallableWithArgs(): void
    {
        $this->registry->register('truncate', fn (string $value, int $length) => substr($value, 0, $length));

        $result = $this->registry->apply('truncate', 'hello world', [5]);

        $this->assertSame('hello', $result);
    }

    public function testApplyFilterInstance(): void
    {
        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getName')->willReturn('test');
        $filter->method('apply')->with('input', ['arg1'])->willReturn('output');

        $this->registry->registerInstance($filter);

        $result = $this->registry->apply('test', 'input', ['arg1']);

        $this->assertSame('output', $result);
    }

    public function testApplyThrowsForUnregistered(): void
    {
        $this->expectException(TemplateException::class);

        $this->registry->apply('nonexistent', 'value');
    }

    public function testGetNames(): void
    {
        $this->registry->register('one', fn ($v) => $v);
        $this->registry->register('two', fn ($v) => $v);

        $names = $this->registry->getNames();

        $this->assertContains('one', $names);
        $this->assertContains('two', $names);
    }

    public function testGetNamesEmpty(): void
    {
        $this->assertSame([], $this->registry->getNames());
    }

    public function testRemoveFilter(): void
    {
        $this->registry->register('test', fn ($v) => $v);
        $this->assertTrue($this->registry->has('test'));

        $this->registry->remove('test');

        $this->assertFalse($this->registry->has('test'));
    }

    public function testRemoveNonexistent(): void
    {
        $this->registry->remove('nonexistent');

        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testClear(): void
    {
        $this->registry->register('one', fn ($v) => $v);
        $this->registry->register('two', fn ($v) => $v);

        $this->registry->clear();

        $this->assertSame(0, $this->registry->count());
    }

    public function testCount(): void
    {
        $this->assertSame(0, $this->registry->count());

        $this->registry->register('one', fn ($v) => $v);
        $this->assertSame(1, $this->registry->count());

        $this->registry->register('two', fn ($v) => $v);
        $this->assertSame(2, $this->registry->count());
    }

    public function testFluentInterface(): void
    {
        $result = $this->registry
            ->register('one', fn ($v) => $v)
            ->register('two', fn ($v) => $v)
            ->remove('one')
            ->clear();

        $this->assertSame($this->registry, $result);
    }
}
