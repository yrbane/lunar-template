<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\InitialsMacro;
use PHPUnit\Framework\TestCase;

class InitialsMacroTest extends TestCase
{
    private InitialsMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new InitialsMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('initials', $this->macro->getName());
    }

    public function testExecuteTwoWords(): void
    {
        $result = $this->macro->execute(['John Doe']);
        $this->assertSame('JD', $result);
    }

    public function testExecuteThreeWordsDefaultLimit(): void
    {
        $result = $this->macro->execute(['John William Doe']);
        $this->assertSame('JW', $result);
    }

    public function testExecuteThreeWordsWithLimit(): void
    {
        $result = $this->macro->execute(['John William Doe', 3]);
        $this->assertSame('JWD', $result);
    }

    public function testExecuteSingleWord(): void
    {
        $result = $this->macro->execute(['John']);
        $this->assertSame('J', $result);
    }

    public function testExecuteLowercase(): void
    {
        $result = $this->macro->execute(['john doe']);
        $this->assertSame('JD', $result);
    }

    public function testExecuteWithLimit(): void
    {
        $result = $this->macro->execute(['John Doe', 1]);
        $this->assertSame('J', $result);
    }

    public function testExecuteEmptyString(): void
    {
        $result = $this->macro->execute(['']);
        $this->assertSame('', $result);
    }

    public function testExecuteEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('', $result);
    }

    public function testExecuteExtraSpaces(): void
    {
        $result = $this->macro->execute(['  John   Doe  ']);
        $this->assertSame('JD', $result);
    }

    public function testExecuteHyphenatedName(): void
    {
        $result = $this->macro->execute(['Mary-Jane Watson', 3]);
        $this->assertSame('MJW', $result);
    }
}
