<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\LoremMacro;
use PHPUnit\Framework\TestCase;

class LoremMacroTest extends TestCase
{
    private LoremMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new LoremMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('lorem', $this->macro->getName());
    }

    public function testExecuteDefaultParagraph(): void
    {
        $result = $this->macro->execute([]);
        $this->assertNotEmpty($result);
        $this->assertStringContainsString('.', $result);
    }

    public function testExecuteMultipleParagraphs(): void
    {
        $result = $this->macro->execute([3]);
        // 3 paragraphs separated by double newlines
        $this->assertSame(2, substr_count($result, "\n\n"));
    }

    public function testExecuteWords(): void
    {
        $result = $this->macro->execute(['words', 10]);
        $words = explode(' ', $result);
        $this->assertCount(10, $words);
    }

    public function testExecuteSentences(): void
    {
        $result = $this->macro->execute(['sentences', 3]);
        // 3 sentences should have 3 periods
        $this->assertSame(3, substr_count($result, '.'));
    }

    public function testExecuteParagraphsType(): void
    {
        $result = $this->macro->execute(['paragraphs', 2]);
        $this->assertSame(1, substr_count($result, "\n\n"));
    }

    public function testExecuteWordsStartWithUppercase(): void
    {
        $result = $this->macro->execute(['words', 5]);
        $this->assertMatchesRegularExpression('/^[A-Z]/', $result);
    }

    public function testExecuteDefaultWords(): void
    {
        $result = $this->macro->execute(['words']);
        $words = explode(' ', $result);
        $this->assertCount(10, $words);
    }
}
