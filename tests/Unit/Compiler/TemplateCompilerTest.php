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

        // Accès hybride via Access::get pour supporter array ET objet (issue #14).
        $this->assertStringContainsString('\\Lunar\\Template\\Runtime\\Access::get', $result);
        $this->assertStringContainsString("'profile'", $result);
        $this->assertStringContainsString("'name'", $result);
        $this->assertStringContainsString('$user', $result);
    }

    public function testCompileDotNotationWithNumericIndex(): void
    {
        // Les indices numériques restent en accès tableau direct.
        $result = $this->compiler->compile('[[ items.0.name ]]');

        $this->assertStringContainsString('$items[0]', $result);
        $this->assertStringContainsString("'name'", $result);
    }

    public function testCompileDotNotationOnObject(): void
    {
        // Issue #14 : accès propriété objet doit produire un appel à Access::get.
        $result = $this->compiler->compile('[[ lang.code ]]');

        $this->assertStringContainsString('\\Lunar\\Template\\Runtime\\Access::get($lang, \'code\')', $result);
    }

    public function testCompileMethodCallRoutesViaCallMethod(): void
    {
        // DX-01 : les appels de méthode passent par Access::callMethod (null-safe).
        $result = $this->compiler->compile('[[ user.getName() ]]');

        $this->assertStringContainsString('\\Lunar\\Template\\Runtime\\Access::callMethod($user, \'getName\')', $result);
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

    public function testCompileStripsSimpleComment(): void
    {
        $result = $this->compiler->compile('Avant [# commentaire #] Après');

        $this->assertSame('Avant  Après', $result);
    }

    public function testCompileStripsCommentLeavingNoTrace(): void
    {
        $result = $this->compiler->compile('[# commentaire à ignorer #]');

        $this->assertStringNotContainsString('[#', $result);
        $this->assertStringNotContainsString('#]', $result);
        $this->assertStringNotContainsString('commentaire', $result);
    }

    public function testCompileStripsMultilineComment(): void
    {
        $source = "Début\n[# ligne 1\nligne 2\nligne 3 #]\nFin";
        $result = $this->compiler->compile($source);

        $this->assertStringNotContainsString('ligne 1', $result);
        $this->assertStringNotContainsString('ligne 2', $result);
        $this->assertStringNotContainsString('ligne 3', $result);
        $this->assertStringContainsString('Début', $result);
        $this->assertStringContainsString('Fin', $result);
    }

    public function testCompileStripsMultipleComments(): void
    {
        $result = $this->compiler->compile('A[# c1 #]B[# c2 #]C');

        $this->assertSame('ABC', $result);
    }

    public function testCompileStripsCommentNonGreedy(): void
    {
        $result = $this->compiler->compile('[# a #]X[# b #]');

        $this->assertSame('X', $result);
    }

    public function testCompileStripsCommentBeforeVariable(): void
    {
        $result = $this->compiler->compile('[# masque le titre #][[ title ]]');

        $this->assertStringNotContainsString('masque', $result);
        $this->assertStringContainsString('$title', $result);
    }

    public function testCompileStripsCommentInsideBlock(): void
    {
        $result = $this->compiler->compile('[% if user %][# debug #]Hello[% endif %]');

        $this->assertStringNotContainsString('debug', $result);
        $this->assertStringContainsString('<?php if (!empty($user)): ?>', $result);
        $this->assertStringContainsString('Hello', $result);
    }

    public function testCompileEmptyComment(): void
    {
        $result = $this->compiler->compile('A[##]B');

        $this->assertSame('AB', $result);
    }

    public function testCompileCommentDoesNotTriggerVariableSubstitution(): void
    {
        $result = $this->compiler->compile('[# [[ should_not_render ]] #]');

        $this->assertStringNotContainsString('should_not_render', $result);
        $this->assertStringNotContainsString('<?=', $result);
    }

    /**
     * Issue #15 : la directive `[% include %]` doit être compilée et non pas
     * laissée telle quelle dans la sortie.
     */
    public function testCompileIncludeDirectiveIsExpanded(): void
    {
        $result = $this->compiler->compile("[% include 'partials/header.tpl' %]");

        $this->assertStringNotContainsString('[% include', $result);
        $this->assertStringContainsString('renderInclude', $result);
        $this->assertStringContainsString("'partials/header.tpl'", $result);
    }

    public function testCompileIncludeDirectiveWithVariables(): void
    {
        $result = $this->compiler->compile("[% include 'card.tpl' with {title: 'Hello'} %]");

        $this->assertStringNotContainsString('[% include', $result);
        $this->assertStringContainsString('renderInclude', $result);
        $this->assertStringContainsString("'title'", $result);
    }

    /**
     * Issue #15 : même symptôme attendu pour `[% set %]`.
     */
    public function testCompileSetDirectiveIsExpanded(): void
    {
        $result = $this->compiler->compile("[% set greeting = 'Hello' %]");

        $this->assertStringNotContainsString('[% set', $result);
        $this->assertStringContainsString('$greeting', $result);
        $this->assertStringContainsString("'Hello'", $result);
    }

    public function testCompileLeavesNoUnknownDirectiveBehind(): void
    {
        // Concaténation de plusieurs directives connues. Aucune ne doit subsister
        // sous forme `[% ... %]` après compilation (à l'exception des tags `if`,
        // `for`, etc. déjà gérés par leurs propres routines).
        $source = "[% include 'a.tpl' %][% set x = 1 %]Hello [[ x ]]";
        $result = $this->compiler->compile($source);

        $this->assertStringNotContainsString('[% include', $result);
        $this->assertStringNotContainsString('[% set', $result);
    }

    /**
     * OCP : un consumer doit pouvoir enregistrer une directive custom.
     */
    public function testCustomDirectiveCanBeRegistered(): void
    {
        $custom = new class () implements \Lunar\Template\Compiler\Directive\DirectiveInterface {
            public function getName(): string
            {
                return 'shout';
            }

            public function compile(string $expression): string
            {
                return '<?= strtoupper(' . trim($expression) . ') ?>';
            }
        };

        $compiler = new TemplateCompiler([$custom]);
        $result = $compiler->compile("[% shout 'hi' %]");

        $this->assertStringNotContainsString('[% shout', $result);
        $this->assertStringContainsString('strtoupper', $result);
    }
}
