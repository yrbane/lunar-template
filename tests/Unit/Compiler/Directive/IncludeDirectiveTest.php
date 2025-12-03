<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Compiler\Directive;

use Lunar\Template\Compiler\Directive\DirectiveInterface;
use Lunar\Template\Compiler\Directive\IncludeDirective;
use PHPUnit\Framework\TestCase;

class IncludeDirectiveTest extends TestCase
{
    private IncludeDirective $directive;

    protected function setUp(): void
    {
        $this->directive = new IncludeDirective();
    }

    public function testImplementsDirectiveInterface(): void
    {
        $this->assertInstanceOf(DirectiveInterface::class, $this->directive);
    }

    public function testGetName(): void
    {
        $this->assertSame('include', $this->directive->getName());
    }

    public function testCompileSimpleInclude(): void
    {
        $result = $this->directive->compile("'header.tpl'");

        $this->assertStringContainsString("renderInclude('header.tpl'", $result);
        $this->assertStringContainsString('get_defined_vars()', $result);
    }

    public function testCompileDoubleQuotedTemplate(): void
    {
        $result = $this->directive->compile('"footer.tpl"');

        $this->assertStringContainsString("renderInclude('footer.tpl'", $result);
    }

    public function testCompileWithVariables(): void
    {
        $result = $this->directive->compile("'partial.tpl' with {title: 'Hello'}");

        $this->assertStringContainsString("'partial.tpl'", $result);
        $this->assertStringContainsString("'title' => 'Hello'", $result);
    }

    public function testCompileWithMultipleVariables(): void
    {
        $result = $this->directive->compile("'card.tpl' with {title: 'Test', count: 5}");

        $this->assertStringContainsString("'title' => 'Test'", $result);
        $this->assertStringContainsString("'count' => 5", $result);
    }

    public function testCompileWithVariableValue(): void
    {
        $result = $this->directive->compile("'user-card.tpl' with {user: currentUser}");

        $this->assertStringContainsString("'user' => \$currentUser", $result);
    }

    public function testCompileWithDotNotationValue(): void
    {
        $result = $this->directive->compile("'name-tag.tpl' with {name: user.profile.name}");

        $this->assertStringContainsString("'name' => \$user['profile']['name']", $result);
    }

    public function testCompileWithBooleanValue(): void
    {
        $result = $this->directive->compile("'toggle.tpl' with {enabled: true}");

        $this->assertStringContainsString("'enabled' => true", $result);
    }

    public function testCompileWithNullValue(): void
    {
        $result = $this->directive->compile("'optional.tpl' with {value: null}");

        $this->assertStringContainsString("'value' => null", $result);
    }

    public function testCompileTemplateAsVariable(): void
    {
        $result = $this->directive->compile('templateName');

        $this->assertStringContainsString('$templateName', $result);
    }

    public function testCompileTemplateAsVariableWithDollar(): void
    {
        $result = $this->directive->compile('$templateName');

        $this->assertStringContainsString('$templateName', $result);
    }

    public function testCompileTemplateAsDotNotation(): void
    {
        $result = $this->directive->compile('config.template');

        $this->assertStringContainsString("\$config['template']", $result);
    }

    public function testCompileInvalidExpression(): void
    {
        $result = $this->directive->compile('');

        $this->assertSame('', $result);
    }

    public function testCompileTemplateVariableWithVariables(): void
    {
        $result = $this->directive->compile('templateName with {key: "value"}');

        $this->assertStringContainsString('$templateName', $result);
        $this->assertStringContainsString("'key' => \"value\"", $result);
    }

    public function testCompileWithNestedArrayValue(): void
    {
        $result = $this->directive->compile("'nested.tpl' with {items: [1, 2, 3]}");

        $this->assertStringContainsString("'items' => [1, 2, 3]", $result);
    }

    public function testCompileWithNestedObjectValue(): void
    {
        $result = $this->directive->compile("'nested.tpl' with {config: {enabled: true}}");

        $this->assertStringContainsString("'config' => {enabled: true}", $result);
    }

    public function testCompileWithNonJsonVariableExpression(): void
    {
        $result = $this->directive->compile("'test.tpl' with someVariable");

        $this->assertStringContainsString('someVariable', $result);
    }

    public function testCompileWithNumericIndex(): void
    {
        $result = $this->directive->compile("'item.tpl' with {first: items.0}");

        $this->assertStringContainsString('$items[0]', $result);
    }

    public function testCompileWithNumericValue(): void
    {
        $result = $this->directive->compile("'counter.tpl' with {count: 42}");

        $this->assertStringContainsString("'count' => 42", $result);
    }

    public function testCompileWithInvalidPairFormat(): void
    {
        // Test with pair that doesn't have colon separator
        $result = $this->directive->compile("'test.tpl' with {invalidPair}");

        $this->assertStringContainsString('invalidPair', $result);
    }
}
