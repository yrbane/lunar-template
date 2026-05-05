<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Linter;

use Lunar\Template\Linter\LintIssue;
use Lunar\Template\Linter\Linter;
use PHPUnit\Framework\TestCase;

class LinterTest extends TestCase
{
    private Linter $linter;

    protected function setUp(): void
    {
        $this->linter = new Linter();
    }

    public function testNoIssuesOnValidTemplate(): void
    {
        $source = <<<'TPL'
            [% if user %]
                Hello [[ user.name ]]
            [% endif %]

            [% for item in items %]
                <li>[[ item ]]</li>
            [% endfor %]

            [% block content %]Default[% endblock %]
            TPL;

        $issues = $this->linter->lint($source);

        $this->assertSame([], $issues);
    }

    public function testDetectsUnclosedIf(): void
    {
        $source = "[% if user %]\nHello\n";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('unclosed_block', $issues[0]->type);
        $this->assertSame(1, $issues[0]->line);
        $this->assertStringContainsString('if', $issues[0]->message);
    }

    public function testDetectsUnclosedFor(): void
    {
        $source = "[% for x in xs %]\n  [[ x ]]\n";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('unclosed_block', $issues[0]->type);
        $this->assertStringContainsString('for', $issues[0]->message);
    }

    public function testDetectsUnclosedBlock(): void
    {
        $source = "[% block content %]\nContent\n";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('unclosed_block', $issues[0]->type);
    }

    public function testDetectsOrphanEndif(): void
    {
        $source = "Hello\n[% endif %]\n";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('orphan_close', $issues[0]->type);
        $this->assertSame(2, $issues[0]->line);
    }

    public function testDetectsMismatchedBlocks(): void
    {
        // [% if %] suivi de [% endfor %] — mismatch
        $source = "[% if x %]\n[% endfor %]\n";

        $issues = $this->linter->lint($source);

        $this->assertGreaterThanOrEqual(1, count($issues));
    }

    public function testDetectsUnclosedVariable(): void
    {
        $source = "Hello [[ name";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('unclosed_token', $issues[0]->type);
    }

    public function testDetectsUnclosedComment(): void
    {
        $source = "[# foo bar baz";

        $issues = $this->linter->lint($source);

        $this->assertCount(1, $issues);
        $this->assertSame('unclosed_token', $issues[0]->type);
    }

    public function testReportsMultipleIssues(): void
    {
        $source = "[% if x %]\n[% for y in ys %]\nDone";

        $issues = $this->linter->lint($source);

        $this->assertCount(2, $issues);
    }

    public function testIssueExposesFile(): void
    {
        $source = "[% if x %]";
        $issues = $this->linter->lint($source, 'page.tpl');

        $this->assertSame('page.tpl', $issues[0]->file);
    }
}
