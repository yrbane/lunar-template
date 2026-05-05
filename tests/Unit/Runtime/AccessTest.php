<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Runtime;

use Lunar\Template\Runtime\Access;
use PHPUnit\Framework\TestCase;
use stdClass;

class AccessTest extends TestCase
{
    public function testGetReadsArrayKey(): void
    {
        $this->assertSame('fr', Access::get(['code' => 'fr'], 'code'));
    }

    public function testGetReturnsNullForMissingArrayKey(): void
    {
        $this->assertNull(Access::get(['code' => 'fr'], 'missing'));
    }

    public function testGetReadsObjectProperty(): void
    {
        $obj = new stdClass();
        $obj->code = 'fr';

        $this->assertSame('fr', Access::get($obj, 'code'));
    }

    public function testGetReturnsNullForMissingObjectProperty(): void
    {
        $obj = new stdClass();

        $this->assertNull(Access::get($obj, 'missing'));
    }

    public function testGetReadsReadonlyDtoProperty(): void
    {
        $dto = new readonly class('fr', 'ltr') {
            public function __construct(public string $code, public string $direction)
            {
            }
        };

        $this->assertSame('fr', Access::get($dto, 'code'));
        $this->assertSame('ltr', Access::get($dto, 'direction'));
    }

    public function testGetReturnsNullForScalar(): void
    {
        $this->assertNull(Access::get('not-an-array-or-object', 'anything'));
        $this->assertNull(Access::get(42, 'anything'));
        $this->assertNull(Access::get(null, 'anything'));
    }

    public function testGetWalksMixedNesting(): void
    {
        $obj = new stdClass();
        $obj->profile = ['name' => 'Jean'];

        $name = Access::get(Access::get($obj, 'profile'), 'name');
        $this->assertSame('Jean', $name);
    }

    public function testHasReturnsTrueForExistingArrayKey(): void
    {
        $this->assertTrue(Access::has(['code' => null], 'code'));
        $this->assertTrue(Access::has(['code' => 'fr'], 'code'));
    }

    public function testHasReturnsFalseForMissingArrayKey(): void
    {
        $this->assertFalse(Access::has(['code' => 'fr'], 'missing'));
    }

    public function testHasReturnsTrueForExistingObjectProperty(): void
    {
        $obj = new stdClass();
        $obj->code = 'fr';

        $this->assertTrue(Access::has($obj, 'code'));
    }

    public function testHasReturnsFalseForMissingObjectProperty(): void
    {
        $obj = new stdClass();

        $this->assertFalse(Access::has($obj, 'missing'));
    }

    public function testHasReturnsFalseForScalar(): void
    {
        $this->assertFalse(Access::has('scalar', 'anything'));
        $this->assertFalse(Access::has(null, 'anything'));
    }

    public function testCallMethodInvokesExistingMethod(): void
    {
        $obj = new class () {
            public function greet(string $who = 'World'): string
            {
                return "Hello, $who!";
            }
        };

        $this->assertSame('Hello, World!', Access::callMethod($obj, 'greet'));
        $this->assertSame('Hello, Jean!', Access::callMethod($obj, 'greet', 'Jean'));
    }

    public function testCallMethodReturnsNullForMissingMethod(): void
    {
        $obj = new \stdClass();

        $this->assertNull(Access::callMethod($obj, 'nope'));
    }

    public function testCallMethodReturnsNullForNullValue(): void
    {
        $this->assertNull(Access::callMethod(null, 'anyMethod'));
    }

    public function testCallMethodReturnsNullForArrayValue(): void
    {
        // Un tableau n'a pas de méthode — null plutôt qu'un fatal PHP.
        $this->assertNull(Access::callMethod(['key' => 'value'], 'anyMethod'));
    }
}
