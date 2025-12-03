<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\SelectMacro;
use PHPUnit\Framework\TestCase;

class SelectMacroTest extends TestCase
{
    private SelectMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new SelectMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('select', $this->macro->getName());
    }

    public function testExecuteWithOptions(): void
    {
        $options = ['fr' => 'France', 'us' => 'USA', 'uk' => 'UK'];
        $result = $this->macro->execute(['country', $options]);
        $this->assertStringContainsString('<select', $result);
        $this->assertStringContainsString('name="country"', $result);
        $this->assertStringContainsString('<option', $result);
        $this->assertStringContainsString('value="fr"', $result);
        $this->assertStringContainsString('>France</option>', $result);
        $this->assertStringContainsString('</select>', $result);
    }

    public function testExecuteWithSelectedValue(): void
    {
        $options = ['a' => 'A', 'b' => 'B', 'c' => 'C'];
        $result = $this->macro->execute(['choice', $options, 'b']);
        $this->assertStringContainsString('value="b" selected', $result);
    }

    public function testExecuteWithPlaceholder(): void
    {
        $options = ['1' => 'One', '2' => 'Two'];
        $result = $this->macro->execute(['num', $options, '', 'Select...']);
        $this->assertStringContainsString('>Select...</option>', $result);
    }

    public function testExecuteWithClass(): void
    {
        $options = ['a' => 'A'];
        $result = $this->macro->execute(['choice', $options, '', '', 'form-select']);
        $this->assertStringContainsString('class="form-select"', $result);
    }

    public function testExecuteEmptyOptions(): void
    {
        $result = $this->macro->execute(['empty', []]);
        $this->assertStringContainsString('<select', $result);
        $this->assertStringContainsString('</select>', $result);
    }

    public function testExecuteEmptyName(): void
    {
        $result = $this->macro->execute(['', ['a' => 'A']]);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteEscapesValues(): void
    {
        $options = ['<script>' => '<script>alert(1)</script>'];
        $result = $this->macro->execute(['xss', $options]);
        $this->assertStringNotContainsString('<script>alert', $result);
    }
}
