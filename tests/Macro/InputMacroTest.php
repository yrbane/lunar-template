<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\InputMacro;
use PHPUnit\Framework\TestCase;

class InputMacroTest extends TestCase
{
    private InputMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new InputMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('input', $this->macro->getName());
    }

    public function testExecuteTextInput(): void
    {
        $result = $this->macro->execute(['username', 'text']);
        $this->assertStringContainsString('<input', $result);
        $this->assertStringContainsString('type="text"', $result);
        $this->assertStringContainsString('name="username"', $result);
    }

    public function testExecuteEmailInput(): void
    {
        $result = $this->macro->execute(['email', 'email']);
        $this->assertStringContainsString('type="email"', $result);
    }

    public function testExecutePasswordInput(): void
    {
        $result = $this->macro->execute(['password', 'password']);
        $this->assertStringContainsString('type="password"', $result);
    }

    public function testExecuteWithValue(): void
    {
        $result = $this->macro->execute(['name', 'text', 'John']);
        $this->assertStringContainsString('value="John"', $result);
    }

    public function testExecuteWithAttributes(): void
    {
        $result = $this->macro->execute(['name', 'text', '', 'required placeholder="Enter name"']);
        $this->assertStringContainsString('required', $result);
        $this->assertStringContainsString('placeholder="Enter name"', $result);
    }

    public function testExecuteWithClass(): void
    {
        $result = $this->macro->execute(['name', 'text', '', '', 'form-control']);
        $this->assertStringContainsString('class="form-control"', $result);
    }

    public function testExecuteNumberInput(): void
    {
        $result = $this->macro->execute(['age', 'number']);
        $this->assertStringContainsString('type="number"', $result);
    }

    public function testExecuteDefaultType(): void
    {
        $result = $this->macro->execute(['field']);
        $this->assertStringContainsString('type="text"', $result);
    }

    public function testExecuteEmptyName(): void
    {
        $result = $this->macro->execute(['']);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteEscapesValue(): void
    {
        $result = $this->macro->execute(['name', 'text', '<script>alert(1)</script>']);
        $this->assertStringNotContainsString('<script>', $result);
    }
}
