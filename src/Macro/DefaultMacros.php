<?php

declare(strict_types=1);

namespace Lunar\Template\Macro;

/**
 * Registers all default macros into a MacroRegistry.
 */
final class DefaultMacros
{
    /**
     * Register all default macros.
     */
    public static function register(MacroRegistry $registry): MacroRegistry
    {
        // Core macros
        $registry->registerInstance(new DateMacro());
        $registry->registerInstance(new AssetMacro());

        // Utility macros
        $registry->registerInstance(new UuidMacro());
        $registry->registerInstance(new RandomMacro());
        $registry->registerInstance(new LoremMacro());
        $registry->registerInstance(new NowMacro());
        $registry->registerInstance(new DumpMacro());
        $registry->registerInstance(new JsonMacro());
        $registry->registerInstance(new PluralizeMacro());
        $registry->registerInstance(new MoneyMacro());
        $registry->registerInstance(new MaskMacro());
        $registry->registerInstance(new InitialsMacro());
        $registry->registerInstance(new ColorMacro());
        $registry->registerInstance(new TimeAgoMacro());
        $registry->registerInstance(new CountdownMacro());

        // Security macros
        $registry->registerInstance(new CsrfMacro());
        $registry->registerInstance(new NonceMacro());
        $registry->registerInstance(new HoneypotMacro());

        // Form macros
        $registry->registerInstance(new InputMacro());
        $registry->registerInstance(new TextareaMacro());
        $registry->registerInstance(new SelectMacro());
        $registry->registerInstance(new CheckboxMacro());
        $registry->registerInstance(new RadioMacro());
        $registry->registerInstance(new LabelMacro());
        $registry->registerInstance(new HiddenMacro());
        $registry->registerInstance(new MethodMacro());

        // HTML/Meta macros
        $registry->registerInstance(new ScriptMacro());
        $registry->registerInstance(new StyleMacro());
        $registry->registerInstance(new MetaMacro());
        $registry->registerInstance(new OgMacro());
        $registry->registerInstance(new TwitterCardMacro());
        $registry->registerInstance(new CanonicalMacro());
        $registry->registerInstance(new FaviconMacro());
        $registry->registerInstance(new SchemaOrgMacro());
        $registry->registerInstance(new BreadcrumbsMacro());
        $registry->registerInstance(new IconMacro());

        // Image/Media macros
        $registry->registerInstance(new GravatarMacro());
        $registry->registerInstance(new AvatarMacro());
        $registry->registerInstance(new PlaceholderMacro());
        $registry->registerInstance(new QrCodeMacro());

        // Embed macros
        $registry->registerInstance(new EmbedYoutubeMacro());
        $registry->registerInstance(new EmbedVimeoMacro());

        // Social macros
        $registry->registerInstance(new ShareMacro());

        return $registry;
    }

    /**
     * Create a new registry with all default macros.
     */
    public static function create(): MacroRegistry
    {
        return self::register(new MacroRegistry());
    }
}
