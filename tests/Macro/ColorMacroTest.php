<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\ColorMacro;
use PHPUnit\Framework\TestCase;

class ColorMacroTest extends TestCase
{
    private ColorMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new ColorMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('color', $this->macro->getName());
    }

    public function testExecuteRandomColor(): void
    {
        $result = $this->macro->execute(['random']);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $result);
    }

    public function testExecuteNamedColor(): void
    {
        $result = $this->macro->execute(['primary']);
        $this->assertSame('#4F46E5', $result);
    }

    public function testExecuteCssVariable(): void
    {
        $result = $this->macro->execute(['custom-color']);
        $this->assertSame('var(--custom-color)', $result);
    }

    public function testExecuteHexPassthrough(): void
    {
        $result = $this->macro->execute(['#FF0000']);
        $this->assertSame('#FF0000', $result);
    }

    public function testExecuteLighten(): void
    {
        $result = $this->macro->execute(['#800000', 'lighten', 50]);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $result);
    }

    public function testExecuteDarken(): void
    {
        $result = $this->macro->execute(['#FFFFFF', 'darken', 50]);
        $this->assertMatchesRegularExpression('/^#[0-9A-F]{6}$/i', $result);
    }

    public function testExecuteAlpha(): void
    {
        $result = $this->macro->execute(['#FF0000', 'alpha', 0.5]);
        $this->assertMatchesRegularExpression('/^rgba\(\d+, \d+, \d+, [\d.]+\)$/', $result);
    }

    public function testExecuteToRgb(): void
    {
        $result = $this->macro->execute(['#FF0000', 'rgb']);
        $this->assertSame('rgb(255, 0, 0)', $result);
    }

    public function testExecuteToHsl(): void
    {
        $result = $this->macro->execute(['#FF0000', 'hsl']);
        $this->assertMatchesRegularExpression('/^hsl\(\d+, \d+%, \d+%\)$/', $result);
    }

    public function testExecuteShortHex(): void
    {
        $result = $this->macro->execute(['#F00', 'rgb']);
        $this->assertSame('rgb(255, 0, 0)', $result);
    }

    public function testExecuteInvalidHexLength(): void
    {
        // Invalid length hex returns original
        $result = $this->macro->execute(['#12345', 'rgb']);
        $this->assertSame('#12345', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('var(--)', $result);
    }
}
