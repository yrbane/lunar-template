<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Compiler;

use Lunar\Template\Compiler\CompilerInterface;
use Lunar\Template\Compiler\TemplateCompiler;
use PHPUnit\Framework\TestCase;

class TemplateCompilerTest extends TestCase
{
    private TemplateCompiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new TemplateCompiler();
    }

    public function testImplementsCompilerInterface(): void
    {
        $this->assertInstanceOf(CompilerInterface::class, $this->compiler);
    }

    public function testCompileSimpleVariable(): void
    {
        $result = $this->compiler->compile('Hello [[ name ]]');

        $this->assertStringContainsString('<?=', $result);
        $this->assertStringContainsString('htmlspecialchars', $result);
        $this->assertStringContainsString('$name', $result);
    }

    public function testCompileEmptyVariable(): void
    {
        $result = $this->compiler->compile('Hello [[  ]]');

        $this->assertSame('Hello ', $result);
    }

    public function testCompileDotNotation(): void
    {
        $result = $this->compiler->compile('[[ user.profile.name ]]');

        $this->assertStringContainsString("\$user['profile']['name']", $result);
    }

    public function testCompileDotNotationWithNumericIndex(): void
    {
        $result = $this->compiler->compile('[[ items.0.name ]]');

        $this->assertStringContainsString("\$items[0]['name']", $result);
    }

    public function testCompileIfCondition(): void
    {
        $result = $this->compiler->compile('[% if user %]Hello[% endif %]');

        $this->assertStringContainsString('<?php if (!empty($user)): ?>', $result);
        $this->assertStringContainsString('<?php endif; ?>', $result);
    }

    public function testCompileIfElseCondition(): void
    {
        $result = $this->compiler->compile('[% if user %]Hello[% else %]Goodbye[% endif %]');

        $this->assertStringContainsString('<?php if (!empty($user)): ?>', $result);
        $this->assertStringContainsString('<?php else: ?>', $result);
        $this->assertStringContainsString('<?php endif; ?>', $result);
    }

    public function testCompileIfElseifCondition(): void
    {
        $result = $this->compiler->compile('[% if admin %]Admin[% elseif user %]User[% endif %]');

        $this->assertStringContainsString('<?php if (!empty($admin)): ?>', $result);
        $this->assertStringContainsString('<?php elseif (!empty($user)): ?>', $result);
    }

    public function testCompileComplexCondition(): void
    {
        $result = $this->compiler->compile('[% if count > 0 %]Has items[% endif %]');

        $this->assertStringContainsString('$count > 0', $result);
    }

    public function testCompileForLoop(): void
    {
        $result = $this->compiler->compile('[% for item in items %][[ item ]][% endfor %]');

        $this->assertStringContainsString('<?php foreach(($items ?? []) as $item): ?>', $result);
        $this->assertStringContainsString('<?php endforeach; ?>', $result);
    }

    public function testCompileForLoopWithDollarPrefix(): void
    {
        $result = $this->compiler->compile('[% for $item in $items %][[ item ]][% endfor %]');

        $this->assertStringContainsString('foreach(($items ?? []) as $item)', $result);
    }

    public function testCompileMacro(): void
    {
        $result = $this->compiler->compile('##url("home")##');

        $this->assertStringContainsString("callMacro('url',", $result);
        $this->assertStringContainsString('"home"', $result);
    }

    public function testCompileMacroWithMultipleArgs(): void
    {
        $result = $this->compiler->compile('##url("user.show", userId)##');

        $this->assertStringContainsString('"user.show"', $result);
        $this->assertStringContainsString('$userId', $result);
    }

    public function testCompileMacroWithNoArgs(): void
    {
        $result = $this->compiler->compile('##currentYear()##');

        $this->assertStringContainsString("callMacro('currentYear', [])", $result);
    }

    public function testCompileMacroWithNumericArg(): void
    {
        $result = $this->compiler->compile('##paginate(10)##');

        $this->assertStringContainsString('[10]', $result);
    }

    public function testCompileMacroWithBooleanArg(): void
    {
        $result = $this->compiler->compile('##toggle(true)##');

        $this->assertStringContainsString('[true]', $result);
    }

    public function testCompileRemovesBlockTags(): void
    {
        $result = $this->compiler->compile('[% block content %]Hello[% endblock %]');

        $this->assertStringNotContainsString('[% block', $result);
        $this->assertStringNotContainsString('[% endblock', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function testCompilePreservesText(): void
    {
        $result = $this->compiler->compile('Plain text without directives');

        $this->assertSame('Plain text without directives', $result);
    }

    public function testCompileMultipleVariables(): void
    {
        $result = $this->compiler->compile('[[ first ]] and [[ second ]]');

        $this->assertStringContainsString('$first', $result);
        $this->assertStringContainsString('$second', $result);
    }

    public function testCompileConditionWithString(): void
    {
        $result = $this->compiler->compile('[% if status == "active" %]Active[% endif %]');

        $this->assertStringContainsString('$status == "active"', $result);
    }

    public function testCompileVariableWithDollarPrefix(): void
    {
        $result = $this->compiler->compile('[[ $name ]]');

        $this->assertStringContainsString('$name', $result);
        // Should not have $$name
        $this->assertStringNotContainsString('$$', $result);
    }

    public function testCompileConditionWithPhpKeywords(): void
    {
        $result = $this->compiler->compile('[% if enabled and visible %]Show[% endif %]');

        // PHP keywords should be preserved, variables should get $
        $this->assertStringContainsString('$enabled', $result);
        $this->assertStringContainsString('and', $result);
        $this->assertStringContainsString('$visible', $result);
        $this->assertStringNotContainsString('$and', $result);
    }

    public function testCompileConditionWithBooleanLiteral(): void
    {
        $result = $this->compiler->compile('[% if active == true %]Active[% endif %]');

        $this->assertStringContainsString('$active == true', $result);
        $this->assertStringNotContainsString('$true', $result);
    }
}
