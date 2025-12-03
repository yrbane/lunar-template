<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Macro;

use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Macro\MacroInterface;
use Lunar\Template\Macro\MacroRegistry;
use PHPUnit\Framework\TestCase;

class MacroRegistryTest extends TestCase
{
    private MacroRegistry $registry;

    protected function setUp(): void
    {
        $this->registry = new MacroRegistry();
    }

    public function testRegisterMacro(): void
    {
        $this->registry->register('greet', fn (string $name) => "Hello, $name!");

        $this->assertTrue($this->registry->has('greet'));
    }

    public function testRegisterInstance(): void
    {
        $macro = $this->createMock(MacroInterface::class);
        $macro->method('getName')->willReturn('test');

        $this->registry->registerInstance($macro);

        $this->assertTrue($this->registry->has('test'));
    }

    public function testHasReturnsFalseForUnregistered(): void
    {
        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testGetMacro(): void
    {
        $callback = fn () => 'result';
        $this->registry->register('test', $callback);

        $this->assertSame($callback, $this->registry->get('test'));
    }

    public function testGetThrowsForUnregistered(): void
    {
        $this->expectException(TemplateException::class);

        $this->registry->get('nonexistent');
    }

    public function testCallMacro(): void
    {
        $this->registry->register('greet', fn (string $name) => "Hello, $name!");

        $result = $this->registry->call('greet', ['World']);

        $this->assertSame('Hello, World!', $result);
    }

    public function testCallMacroInstance(): void
    {
        $macro = $this->createMock(MacroInterface::class);
        $macro->method('getName')->willReturn('test');
        $macro->method('execute')->with(['arg1'])->willReturn('result');

        $this->registry->registerInstance($macro);

        $result = $this->registry->call('test', ['arg1']);

        $this->assertSame('result', $result);
    }

    public function testCallThrowsForUnregistered(): void
    {
        $this->expectException(TemplateException::class);

        $this->registry->call('nonexistent', []);
    }

    public function testGetNames(): void
    {
        $this->registry->register('one', fn () => '1');
        $this->registry->register('two', fn () => '2');

        $names = $this->registry->getNames();

        $this->assertContains('one', $names);
        $this->assertContains('two', $names);
    }

    public function testGetNamesEmpty(): void
    {
        $this->assertSame([], $this->registry->getNames());
    }

    public function testRemoveMacro(): void
    {
        $this->registry->register('test', fn () => 'result');
        $this->assertTrue($this->registry->has('test'));

        $this->registry->remove('test');

        $this->assertFalse($this->registry->has('test'));
    }

    public function testRemoveNonexistent(): void
    {
        // Should not throw
        $this->registry->remove('nonexistent');

        $this->assertFalse($this->registry->has('nonexistent'));
    }

    public function testClear(): void
    {
        $this->registry->register('one', fn () => '1');
        $this->registry->register('two', fn () => '2');

        $this->registry->clear();

        $this->assertSame(0, $this->registry->count());
    }

    public function testCount(): void
    {
        $this->assertSame(0, $this->registry->count());

        $this->registry->register('one', fn () => '1');
        $this->assertSame(1, $this->registry->count());

        $this->registry->register('two', fn () => '2');
        $this->assertSame(2, $this->registry->count());
    }

    public function testFluentInterface(): void
    {
        $result = $this->registry
            ->register('one', fn () => '1')
            ->register('two', fn () => '2')
            ->remove('one')
            ->clear();

        $this->assertSame($this->registry, $result);
    }
}
