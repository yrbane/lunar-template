<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Compiler\Directive;

use Lunar\Template\Compiler\Directive\DirectiveInterface;
use Lunar\Template\Compiler\Directive\SetDirective;
use PHPUnit\Framework\TestCase;

class SetDirectiveTest extends TestCase
{
    private SetDirective $directive;

    protected function setUp(): void
    {
        $this->directive = new SetDirective();
    }

    public function testImplementsDirectiveInterface(): void
    {
        $this->assertInstanceOf(DirectiveInterface::class, $this->directive);
    }

    public function testGetName(): void
    {
        $this->assertSame('set', $this->directive->getName());
    }

    public function testCompileStringValue(): void
    {
        $result = $this->directive->compile('name = "John"');

        $this->assertSame('<?php $name = "John"; ?>', $result);
    }

    public function testCompileSingleQuotedString(): void
    {
        $result = $this->directive->compile("title = 'Hello World'");

        $this->assertSame("<?php \$title = 'Hello World'; ?>", $result);
    }

    public function testCompileNumericValue(): void
    {
        $result = $this->directive->compile('count = 42');

        $this->assertSame('<?php $count = 42; ?>', $result);
    }

    public function testCompileFloatValue(): void
    {
        $result = $this->directive->compile('price = 19.99');

        $this->assertSame('<?php $price = 19.99; ?>', $result);
    }

    public function testCompileBooleanTrue(): void
    {
        $result = $this->directive->compile('enabled = true');

        $this->assertSame('<?php $enabled = true; ?>', $result);
    }

    public function testCompileBooleanFalse(): void
    {
        $result = $this->directive->compile('disabled = false');

        $this->assertSame('<?php $disabled = false; ?>', $result);
    }

    public function testCompileNull(): void
    {
        $result = $this->directive->compile('value = null');

        $this->assertSame('<?php $value = null; ?>', $result);
    }

    public function testCompileVariableValue(): void
    {
        $result = $this->directive->compile('copy = original');

        $this->assertSame('<?php $copy = $original; ?>', $result);
    }

    public function testCompileDotNotationValue(): void
    {
        $result = $this->directive->compile('userName = user.name');

        $this->assertSame("<?php \$userName = \$user['name']; ?>", $result);
    }

    public function testCompileWithDollarPrefix(): void
    {
        $result = $this->directive->compile('copy = $original');

        $this->assertSame('<?php $copy = $original; ?>', $result);
    }

    public function testCompileArrayLiteral(): void
    {
        $result = $this->directive->compile('items = [1, 2, 3]');

        $this->assertSame('<?php $items = [1, 2, 3]; ?>', $result);
    }

    public function testCompileInvalidExpression(): void
    {
        $result = $this->directive->compile('invalid expression');

        $this->assertSame('', $result);
    }

    public function testCompileWithExtraSpaces(): void
    {
        $result = $this->directive->compile('  name   =   "John"  ');

        $this->assertSame('<?php $name = "John"; ?>', $result);
    }

    public function testCompileNestedDotNotation(): void
    {
        $result = $this->directive->compile('fullName = user.profile.fullName');

        $this->assertSame("<?php \$fullName = \$user['profile']['fullName']; ?>", $result);
    }

    public function testCompileNumericIndex(): void
    {
        $result = $this->directive->compile('first = items.0');

        $this->assertSame('<?php $first = $items[0]; ?>', $result);
    }
}
