<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\JsonMacro;
use PHPUnit\Framework\TestCase;

class JsonMacroTest extends TestCase
{
    private JsonMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new JsonMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('json', $this->macro->getName());
    }

    public function testExecuteWithArray(): void
    {
        $result = $this->macro->execute([['a' => 1, 'b' => 2]]);
        $this->assertSame('{"a":1,"b":2}', $result);
    }

    public function testExecuteWithString(): void
    {
        $result = $this->macro->execute(['hello']);
        $this->assertSame('"hello"', $result);
    }

    public function testExecuteWithInteger(): void
    {
        $result = $this->macro->execute([42]);
        $this->assertSame('42', $result);
    }

    public function testExecuteWithBoolean(): void
    {
        $result = $this->macro->execute([true]);
        $this->assertSame('true', $result);
    }

    public function testExecuteWithNull(): void
    {
        $result = $this->macro->execute([null]);
        $this->assertSame('null', $result);
    }

    public function testExecutePrettyPrint(): void
    {
        $result = $this->macro->execute([['a' => 1], true]);
        $this->assertStringContainsString("\n", $result);
        $this->assertStringContainsString('    ', $result);
    }

    public function testExecuteWithNestedArray(): void
    {
        $result = $this->macro->execute([['users' => [['name' => 'John'], ['name' => 'Jane']]]]);
        $decoded = json_decode($result, true);
        $this->assertIsArray($decoded);
        $this->assertArrayHasKey('users', $decoded);
        $this->assertCount(2, $decoded['users']);
    }

    public function testExecuteWithEmptyArray(): void
    {
        $result = $this->macro->execute([[]]);
        $this->assertSame('[]', $result);
    }

    public function testExecuteWithEmptyArgs(): void
    {
        $result = $this->macro->execute([]);
        $this->assertSame('null', $result);
    }

    public function testExecuteUnescapedSlashes(): void
    {
        $result = $this->macro->execute([['url' => 'https://example.com/path']]);
        $this->assertStringContainsString('https://example.com/path', $result);
    }

    public function testExecuteUnescapedUnicode(): void
    {
        $result = $this->macro->execute([['text' => 'héllo wörld']]);
        $this->assertStringContainsString('héllo wörld', $result);
    }
}
