<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Macro;

use Lunar\Template\Macro\AvatarMacro;
use Lunar\Template\Macro\BreadcrumbsMacro;
use Lunar\Template\Macro\CanonicalMacro;
use Lunar\Template\Macro\CheckboxMacro;
use Lunar\Template\Macro\EmbedVimeoMacro;
use Lunar\Template\Macro\EmbedYoutubeMacro;
use Lunar\Template\Macro\FaviconMacro;
use Lunar\Template\Macro\HiddenMacro;
use Lunar\Template\Macro\LabelMacro;
use Lunar\Template\Macro\MacroInterface;
use Lunar\Template\Macro\MetaMacro;
use Lunar\Template\Macro\MethodMacro;
use Lunar\Template\Macro\OgMacro;
use Lunar\Template\Macro\PlaceholderMacro;
use Lunar\Template\Macro\QrCodeMacro;
use Lunar\Template\Macro\RadioMacro;
use Lunar\Template\Macro\SchemaOrgMacro;
use Lunar\Template\Macro\ScriptMacro;
use Lunar\Template\Macro\StyleMacro;
use Lunar\Template\Macro\TextareaMacro;
use Lunar\Template\Macro\TwitterCardMacro;
use PHPUnit\Framework\TestCase;

/**
 * Couverture minimale (smoke + cas critiques) pour les macros qui
 * n'avaient pas de test dédié avant la session de finition v1.5.0.
 *
 * Pour chaque macro :
 *  - le nom est non-vide,
 *  - execute([]) ne plante pas et retourne une string,
 *  - les arguments typiques produisent une sortie avec les bonnes balises
 *    et sans XSS sur les arguments user-provided.
 */
class LegacyMacrosCoverageTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<MacroInterface>, string}>
     */
    public static function macroProvider(): iterable
    {
        yield 'avatar'       => [AvatarMacro::class, 'avatar'];
        yield 'breadcrumbs'  => [BreadcrumbsMacro::class, 'breadcrumbs'];
        yield 'canonical'    => [CanonicalMacro::class, 'canonical'];
        yield 'checkbox'     => [CheckboxMacro::class, 'checkbox'];
        yield 'embed_vimeo'  => [EmbedVimeoMacro::class, 'vimeo'];
        yield 'embed_youtube' => [EmbedYoutubeMacro::class, 'youtube'];
        yield 'favicon'      => [FaviconMacro::class, 'favicon'];
        yield 'hidden'       => [HiddenMacro::class, 'hidden'];
        yield 'label'        => [LabelMacro::class, 'label'];
        yield 'meta'         => [MetaMacro::class, 'meta'];
        yield 'method'       => [MethodMacro::class, 'method'];
        yield 'og'           => [OgMacro::class, 'og'];
        yield 'placeholder'  => [PlaceholderMacro::class, 'placeholder'];
        yield 'qrcode'       => [QrCodeMacro::class, 'qrcode'];
        yield 'radio'        => [RadioMacro::class, 'radio'];
        yield 'schema'       => [SchemaOrgMacro::class, 'schema'];
        yield 'script'       => [ScriptMacro::class, 'script'];
        yield 'style'        => [StyleMacro::class, 'style'];
        yield 'textarea'     => [TextareaMacro::class, 'textarea'];
        yield 'twitter'      => [TwitterCardMacro::class, 'twitter'];
    }

    /**
     * @param class-string<MacroInterface> $class
     *
     * @dataProvider macroProvider
     */
    public function testImplementsMacroInterface(string $class, string $expectedName): void
    {
        $macro = new $class();
        $this->assertInstanceOf(MacroInterface::class, $macro);
    }

    /**
     * @param class-string<MacroInterface> $class
     *
     * @dataProvider macroProvider
     */
    public function testExposesExpectedName(string $class, string $expectedName): void
    {
        $macro = new $class();
        $this->assertSame($expectedName, $macro->getName());
    }

    /**
     * @param class-string<MacroInterface> $class
     *
     * @dataProvider macroProvider
     */
    public function testExecuteWithEmptyArgsDoesNotThrow(string $class, string $expectedName): void
    {
        $macro = new $class();
        $result = $macro->execute([]);
        $this->assertIsString($result);
    }

    public function testCheckboxRendersInputAndLabel(): void
    {
        $macro = new CheckboxMacro();
        $html = $macro->execute(['agree', true, '1', 'I agree']);

        $this->assertStringContainsString('<input type="checkbox"', $html);
        $this->assertStringContainsString('name="agree"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('I agree', $html);
        $this->assertStringContainsString('<label>', $html);
    }

    public function testCheckboxEscapesUserInput(): void
    {
        $macro = new CheckboxMacro();
        $html = $macro->execute(['name', false, '"><script>alert(1)</script>', '']);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&quot;&gt;&lt;script&gt;', $html);
    }

    public function testRadioRendersInput(): void
    {
        // Signature : (name, value, label, checked, class)
        $macro = new RadioMacro();
        $html = $macro->execute(['gender', 'male', 'Male', true]);

        $this->assertStringContainsString('<input type="radio"', $html);
        $this->assertStringContainsString('name="gender"', $html);
        $this->assertStringContainsString('value="male"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('Male', $html);
    }

    public function testEmbedYoutubeRendersIframe(): void
    {
        $macro = new EmbedYoutubeMacro();
        $html = $macro->execute(['dQw4w9WgXcQ']);

        $this->assertStringContainsString('<iframe', $html);
        // youtube-nocookie est utilisé pour la confidentialité par défaut
        $this->assertStringContainsString('embed/dQw4w9WgXcQ', $html);
    }

    public function testEmbedVimeoRendersIframe(): void
    {
        $macro = new EmbedVimeoMacro();
        $html = $macro->execute(['123456789']);

        $this->assertStringContainsString('<iframe', $html);
        $this->assertStringContainsString('123456789', $html);
    }

    public function testBreadcrumbsRendersNav(): void
    {
        // Signature : items avec clés 'name' et 'url'
        $macro = new BreadcrumbsMacro();
        $html = $macro->execute([[
            ['name' => 'Accueil', 'url' => '/'],
            ['name' => 'Blog', 'url' => '/blog'],
            ['name' => 'Article'],
        ]]);

        $this->assertStringContainsString('Accueil', $html);
        $this->assertStringContainsString('Blog', $html);
        $this->assertStringContainsString('Article', $html);
        $this->assertStringContainsString('href="/"', $html);
        $this->assertStringContainsString('schema.org/BreadcrumbList', $html);
    }

    public function testTextareaRendersWithValue(): void
    {
        $macro = new TextareaMacro();
        $html = $macro->execute(['comment', 'Hello world']);

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="comment"', $html);
        $this->assertStringContainsString('Hello world', $html);
    }

    public function testHiddenRendersInput(): void
    {
        $macro = new HiddenMacro();
        $html = $macro->execute(['user_id', '42']);

        $this->assertStringContainsString('<input type="hidden"', $html);
        $this->assertStringContainsString('name="user_id"', $html);
        $this->assertStringContainsString('value="42"', $html);
    }

    public function testLabelRendersFor(): void
    {
        $macro = new LabelMacro();
        $html = $macro->execute(['email', 'Adresse e-mail']);

        $this->assertStringContainsString('<label', $html);
        $this->assertStringContainsString('for="email"', $html);
        $this->assertStringContainsString('Adresse e-mail', $html);
    }
}
