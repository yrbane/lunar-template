# API Reference

Index des classes publiques de Lunar Template (v1.5.0). Pour les détails de signature et exemples d'usage, se référer à la PHPDoc dans le code source.

> Convention : `Lunar\Template` est le namespace racine, omis dans cet index pour la lisibilité.

## Façade

| Classe | Rôle |
|---|---|
| [`AdvancedTemplateEngine`](../src/AdvancedTemplateEngine.php) | Tout-en-un. Charge `templates/` + `cache/`, expose `render()`, gestion des macros, mode strict, source maps. |

## Renderer modulaire

| Classe | Rôle |
|---|---|
| [`Renderer\TemplateRenderer`](../src/Renderer/TemplateRenderer.php) | Renderer minimal avec injection de `FilterRegistry` et `MacroRegistry`. |
| [`Renderer\InheritanceResolver`](../src/Renderer/InheritanceResolver.php) | Résout `[% extends %]` chaîné, détecte les boucles. |

## Compilation

| Classe | Rôle |
|---|---|
| [`Compiler\TemplateCompiler`](../src/Compiler/TemplateCompiler.php) | Compile la syntaxe `[[ ]]` / `[% %]` / `##macro##` vers PHP. |
| [`Compiler\CompilerInterface`](../src/Compiler/CompilerInterface.php) | Contrat des compilateurs (1 méthode : `compile(string)`). |
| [`Compiler\Directive\IncludeDirective`](../src/Compiler/Directive/IncludeDirective.php) | `[% include %]`. |
| [`Compiler\Directive\SetDirective`](../src/Compiler/Directive/SetDirective.php) | `[% set %]`. |

## Parser

| Classe | Rôle |
|---|---|
| [`Parser\TemplateParser`](../src/Parser/TemplateParser.php) | Analyse statique en `ParsedTemplate` (extends, blocks, macros). |
| [`Parser\ParsedTemplate`](../src/Parser/ParsedTemplate.php) | DTO `final readonly` du résultat de parsing. |
| [`Parser\ParserInterface`](../src/Parser/ParserInterface.php) | Contrat (1 méthode : `parse(string)`). |

## Cache

| Classe | Rôle |
|---|---|
| [`Cache\CacheInterface`](../src/Cache/CacheInterface.php) | Contrat unique : `get`, `set`, `has(key, sourceTime)`, `delete`, `clear`, `getPath`, `getDirectory`. |
| [`Cache\FilesystemCache`](../src/Cache/FilesystemCache.php) | Implémentation filesystem par défaut. |
| [`Cache\Psr16Adapter`](../src/Cache/Psr16Adapter.php) | Adaptateur PSR-16 (Redis/Memcached/APCu) avec matérialisation locale. |
| [`Cache\CacheWarmer`](../src/Cache/CacheWarmer.php) | Précompile les templates en lot. |

## Filtres

| Classe | Rôle |
|---|---|
| [`Filter\FilterInterface`](../src/Filter/FilterInterface.php) | Contrat (`getName`, `apply(value, args)`). |
| [`Filter\FilterRegistry`](../src/Filter/FilterRegistry.php) | Registre. Lève `TemplateException` avec suggestion Levenshtein si filtre inconnu. |
| [`Filter\DefaultFilters`](../src/Filter/DefaultFilters.php) | Enregistre les ~50 filtres standards en une fois. |
| [`Filter\AbstractFilter`](../src/Filter/AbstractFilter.php) | Base utilitaire (helpers `toString()`, …). |

Catégories : `Filter\String\*` (chaînes), `Filter\Number\*` (nombres), `Filter\Array\*` (tableaux), `Filter\Date\*` (dates), `Filter\Encoding\*` (b64, json, hash), `Filter\Html\*` (markdown, linkify, …), `Filter\HtmlElement\*` (`div`, `img`, `audio`, …).

## Macros

| Classe | Rôle |
|---|---|
| [`Macro\MacroInterface`](../src/Macro/MacroInterface.php) | Contrat (`getName`, `execute(args)`). |
| [`Macro\MacroRegistry`](../src/Macro/MacroRegistry.php) | Registre. |
| [`Macro\DefaultMacros`](../src/Macro/DefaultMacros.php) | Enregistre les ~40 macros standards en une fois. |

Catégories : utilitaires (`UuidMacro`, `LoremMacro`, `MoneyMacro`, …), formulaires (`InputMacro`, `SelectMacro`, …), HTML/Meta (`ScriptMacro`, `OgMacro`, …), media (`GravatarMacro`, `EmbedYoutubeMacro`, …), social (`ShareMacro`).

## Plugins

| Classe | Rôle |
|---|---|
| [`Plugin\PluginDiscovery`](../src/Plugin/PluginDiscovery.php) | Auto-discovery des macros/filtres tiers via `composer.json` `extra.lunar-template`. Cache statique mémoïsé. |

## Runtime helpers

| Classe | Rôle |
|---|---|
| [`Runtime\Access`](../src/Runtime/Access.php) | Accès hybride array/objet (`get`, `has`, `callMethod`). Utilisé par le code compilé pour `[[ obj.prop ]]` et `[[ obj.method() ]]`. |
| [`Runtime\SourceMap`](../src/Runtime/SourceMap.php) | Inject + resolve : marqueurs ligne→ligne pour traduire les erreurs vers le `.tpl` source. |

## Linter

| Classe | Rôle |
|---|---|
| [`Linter\Linter`](../src/Linter/Linter.php) | Analyse statique : tags non fermés, fermetures orphelines, paires mal appariées. |
| [`Linter\LintIssue`](../src/Linter/LintIssue.php) | DTO `final readonly` (type, line, message, file, severity). |

## Sécurité

| Classe | Rôle |
|---|---|
| [`Security\PathValidator`](../src/Security/PathValidator.php) | Empêche les attaques de path traversal sur les chemins de templates. |
| [`Security\HtmlEscaper`](../src/Security/HtmlEscaper.php) | Échappement HTML configurable (charset, stratégie). |
| [`Security\EscaperInterface`](../src/Security/EscaperInterface.php) | Contrat. |

## HTML helpers

| Classe | Rôle |
|---|---|
| [`Html\AttributeBag`](../src/Html/AttributeBag.php) | Construit des chaînes d'attributs HTML avec échappement automatique (utilisé par les macros de formulaire). |

## Exceptions

Toutes les exceptions héritent de `Lunar\Template\Exception\TemplateException` :

| Exception | Cas |
|---|---|
| `TemplateException` | Cas générique, source map attachée. |
| `TemplateNotFoundException` | Fichier `.tpl` absent. |
| `SyntaxException` | Syntaxe invalide à la compilation. |
| `CircularInheritanceException` | `extends` cyclique détecté. |
| `MacroNotFoundException` | Macro appelée mais non enregistrée. |

## CLI (`bin/lunar-template`)

| Commande | Rôle |
|---|---|
| `template:render` | Rendu d'un template. |
| `template:compile` | Compilation à plat (sans render). |
| `template:warm` | Précompilation en masse. |
| `template:clear` | Vide le cache. |
| `template:check` | Render avec données vides pour valider syntaxe. |
| `template:lint` | **Analyse statique** (sans exécution) : blocs non fermés, tokens malformés. |
| `template:list` | Liste les templates trouvés. |

---

## Conventions transverses

- **Final readonly** quand possible : `Psr16Adapter`, `PluginDiscovery`, `ParsedTemplate`, `HtmlEscaper`, `InheritanceResolver`, `LintIssue`.
- **Strict types** : `declare(strict_types=1)` dans tous les fichiers.
- **Mode strict** : variables undefined ou null lèvent `TemplateException` quand `$engine->setStrictVariables(true)`.
- **Source map** : les exceptions `TemplateException` portent toujours le nom du `.tpl` et la ligne d'origine, même à travers `extends`.
