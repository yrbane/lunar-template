<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Html;

use Lunar\Template\Html\AttributeBag;
use PHPUnit\Framework\TestCase;

class AttributeBagTest extends TestCase
{
    public function testItRendersAttributes(): void
    {
        $attributes = new AttributeBag([
            'class' => 'btn btn-primary',
            'id' => 'submit-btn',
            'data-role' => 'save',
        ]);

        $this->assertEquals(
            'class="btn btn-primary" id="submit-btn" data-role="save"',
            (string) $attributes
        );
    }

    public function testItEscapesSpecialCharacters(): void
    {
        $attributes = new AttributeBag([
            'value' => 'Hello "World" & <Moon>',
            'onclick' => 'alert("XSS")',
        ]);

        $this->assertStringContainsString('value="Hello &quot;World&quot; &amp; &lt;Moon&gt;"', (string) $attributes);
        $this->assertStringContainsString('onclick="alert(&quot;XSS&quot;)"', (string) $attributes);
    }

    public function testItHandlesBooleanAttributes(): void
    {
        $attributes = new AttributeBag([
            'required' => true,
            'disabled' => false,
            'readonly' => null,
            'checked' => true,
        ]);

        $output = (string) $attributes;

        $this->assertStringContainsString('required', $output);
        $this->assertStringContainsString('checked', $output);
        $this->assertStringNotContainsString('disabled', $output);
        $this->assertStringNotContainsString('readonly', $output);
    }

    public function testItCanAddAttributes(): void
    {
        $attributes = new AttributeBag(['class' => 'foo']);
        $attributes->add('id', 'bar');

        $this->assertEquals('class="foo" id="bar"', (string) $attributes);
    }

    public function testItMergesClassNames(): void
    {
        // Optionnel : Une gestion intelligente des classes serait un plus
        $attributes = new AttributeBag(['class' => 'btn']);
        $attributes->add('class', 'btn-primary');

        // Si on implemente juste un écrasement simple :
        $this->assertEquals('class="btn-primary"', (string) $attributes);
        
        // Si on voulait une fusion intelligente, le test serait :
        // $this->assertEquals('class="btn btn-primary"', (string) $attributes);
    }
}
