<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\DefaultMacros;
use Lunar\Template\Macro\MacroRegistry;
use PHPUnit\Framework\TestCase;

class DefaultMacrosTest extends TestCase
{
    public function testRegister(): void
    {
        $registry = new MacroRegistry();
        $result = DefaultMacros::register($registry);

        $this->assertSame($registry, $result);
    }

    public function testCreate(): void
    {
        $registry = DefaultMacros::create();

        $this->assertInstanceOf(MacroRegistry::class, $registry);
    }

    public function testRegistersUtilityMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('uuid'));
        $this->assertTrue($registry->has('random'));
        $this->assertTrue($registry->has('lorem'));
        $this->assertTrue($registry->has('now'));
        $this->assertTrue($registry->has('dump'));
        $this->assertTrue($registry->has('json'));
        $this->assertTrue($registry->has('pluralize'));
        $this->assertTrue($registry->has('money'));
        $this->assertTrue($registry->has('mask'));
        $this->assertTrue($registry->has('initials'));
        $this->assertTrue($registry->has('color'));
        $this->assertTrue($registry->has('timeago'));
        $this->assertTrue($registry->has('countdown'));
    }

    public function testRegistersSecurityMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('csrf'));
        $this->assertTrue($registry->has('nonce'));
        $this->assertTrue($registry->has('honeypot'));
    }

    public function testRegistersFormMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('input'));
        $this->assertTrue($registry->has('textarea'));
        $this->assertTrue($registry->has('select'));
        $this->assertTrue($registry->has('checkbox'));
        $this->assertTrue($registry->has('radio'));
        $this->assertTrue($registry->has('label'));
        $this->assertTrue($registry->has('hidden'));
        $this->assertTrue($registry->has('method'));
    }

    public function testRegistersHtmlMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('script'));
        $this->assertTrue($registry->has('style'));
        $this->assertTrue($registry->has('meta'));
        $this->assertTrue($registry->has('og'));
        $this->assertTrue($registry->has('twitter'));
        $this->assertTrue($registry->has('canonical'));
        $this->assertTrue($registry->has('favicon'));
        $this->assertTrue($registry->has('schema'));
        $this->assertTrue($registry->has('breadcrumbs'));
        $this->assertTrue($registry->has('icon'));
    }

    public function testRegistersMediaMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('gravatar'));
        $this->assertTrue($registry->has('avatar'));
        $this->assertTrue($registry->has('placeholder'));
        $this->assertTrue($registry->has('qrcode'));
    }

    public function testRegistersEmbedMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('youtube'));
        $this->assertTrue($registry->has('vimeo'));
    }

    public function testRegistersSocialMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('share'));
    }

    public function testRegistersCoreMacros(): void
    {
        $registry = DefaultMacros::create();

        $this->assertTrue($registry->has('date'));
        $this->assertTrue($registry->has('asset'));
    }
}
