<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\UuidMacro;
use PHPUnit\Framework\TestCase;

class UuidMacroTest extends TestCase
{
    private UuidMacro $macro;

    protected function setUp(): void
    {
        $this->macro = new UuidMacro();
    }

    public function testGetName(): void
    {
        $this->assertSame('uuid', $this->macro->getName());
    }

    public function testExecuteGeneratesValidUuid(): void
    {
        $result = $this->macro->execute([]);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result
        );
    }

    public function testExecuteGeneratesUniqueUuids(): void
    {
        $uuids = [];
        for ($i = 0; $i < 100; $i++) {
            $uuids[] = $this->macro->execute([]);
        }
        $this->assertCount(100, array_unique($uuids));
    }

    public function testExecuteIgnoresArguments(): void
    {
        $result = $this->macro->execute(['ignored', 'args']);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i',
            $result
        );
    }
}
