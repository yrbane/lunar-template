# lunar-template — Consignes projet

Moteur de templates PHP autonome (PHP 8.3+, namespace `Lunar\Template`).

## Stack

- **Runtime** : PHP 8.3+, `ext-mbstring`
- **Dépendances** : `psr/simple-cache`, `yrbane/lunar-cli`, `yrbane/lunar-config`
- **Dev** : PHPUnit, php-cs-fixer, PHPStan (niveau 7)
- **Typage strict** : `declare(strict_types=1)` partout

## Structure

```text
src/                     Code source (PSR-4 sous Lunar\Template\)
├── AdvancedTemplateEngine.php   Façade tout-en-un
├── Compiler/            Compilation source .tpl → PHP
├── Parser/              Analyse de syntaxe
├── Renderer/            Rendu + injection variables
├── Cache/               FilesystemCache, CacheWarmer
├── Filter/              50+ filtres pipe `[[ var | filtre ]]`
├── Macro/               Macros `##macro(args)##`
├── Compiler/Directive/  Directives `[% set %]`, `[% include %]`
├── Html/                AttributeBag, HtmlEscaper
├── Security/            PathValidator
└── Exception/           Hiérarchie d'exceptions

tests/                   Tests unitaires + intégration
config/template.json     Configuration par défaut
bin/lunar-template       Entrée CLI
```

## Commandes

```bash
composer install
vendor/bin/phpunit                    # suite complète
vendor/bin/phpunit --filter <Test>    # filtre
vendor/bin/phpstan analyse            # analyse statique niveau 7
vendor/bin/php-cs-fixer fix           # formatage
```

## Conventions

- **TDD** : test (rouge) avant code (vert).
- **Code en anglais**, **commentaires et commits en français** (avec accents).
- **SOLID, DRY, KISS** — pas d'abstraction prématurée.
- **Sécurité par défaut** : échappement HTML automatique, pas de raw sans intention explicite (`[[! !]]` ou `|raw`).

## Syntaxe template (référence rapide)

| Token | Usage |
|---|---|
| `[[ var ]]` | Variable échappée HTML |
| `[[! var !]]` | Variable brute (sans échappement) |
| `[[ var \| filtre ]]` | Variable filtrée |
| `[[ var \| raw ]]` | Variable brute via filtre |
| `[[ obj.prop ]]` | Accès hybride array/objet (issue #14, via `Runtime\Access::get`) |
| `[[ obj.method() ]]` | Appel de méthode |
| `[# ... #]` | Commentaire (multi-ligne, jamais émis) |
| `[% if/elseif/else/endif %]` | Conditions |
| `[% for x in xs %] / [% endfor %]` | Boucles |
| `[% extends 'base' %]` | Héritage (auto-extension `.tpl`) |
| `[% block name %] / [% endblock %]` | Blocs |
| `[% parent %]` | Inclusion du contenu parent |
| `[% include 'partial' %]` | Inclusion |
| `[% set var = value %]` | Assignation |
| `##macro(args)##` | Macro |

Documentation complète : `README.md` (EN), `README.fr.md` (FR).

## État du code

- 100% de couverture de tests (PHPStan niveau 7).
- IMPROVEMENT_PLAN.md liste la roadmap active (post-livraison initiale).
