# Plan d'amélioration — Lunar Template Engine

Roadmap active du moteur de templates. Le plan d'audit initial (IMP-01..04) est livré ; ce document trace les chantiers restants et les nouvelles priorités.

**Dernière mise à jour** : 2026-05-05 (Milestones 1, 2 & 3 livrés)

---

## État actuel (après nettoyage)

- **PHP** : 8.3+ avec typage strict
- **Tests** : 1722 tests, **100 % passants**, 0 erreur, 0 échec
- **Qualité** : PHPStan niveau 7, php-cs-fixer
- **Documentation** : `README.md` (EN), `README.fr.md` (FR), `CLAUDE.md` (consignes projet)

### Récemment livré (décembre 2025 → mai 2026)

- **Issue #13** : Commentaires `[# ... #]` (multi-ligne, jamais émis dans la sortie HTML)
- **Issue #14** : Accès hybride array/objet `[[ obj.prop ]]` via `Runtime\Access::get` (DTO readonly supportés)
- **Appels de méthode** : `[[ obj.method() ]]` compile vers `$obj->method()`
- **Filtre `|raw` inline** : `[[ html|raw ]]` court-circuite l'échappement HTML
- **Auto-extension `.tpl`** : `[% extends 'base' %]` résout `base.tpl` si nécessaire
- **Fix regex** : `convertMacroArgument` détectait incorrectement les chaînes
- **Préservation des tokens** : `[[ ]]`, `[% %]` et `##macro##` autorisés dans `<script>`/`<style>` (y compris dans les attributs)
- **Milestone 1 — Stabilisation** : 4 chantiers livrés (cache, leaks, blocs hérités, exception type)
- **Audit du dépôt** : suppression des artefacts SpecKit, des spécifications livrées et des leaks de cache de tests

---

## Milestone 1 — Stabilisation ✅ Livré (2026-05-05)

### [STAB-01] Architecture Cache : unifier CacheInterface ✅

Le code/tests utilisaient deux noms d'interface contradictoires : `CacheStorageInterface` côté src/, `CacheInterface` (inexistante) côté tests et `CacheWarmer`.

Refactor :
- Renommage `CacheStorageInterface` → `CacheInterface`
- Ajout `has(string $key, int $sourceTime): bool` et `getPath(string $key): ?string`
- Suppression de `getCompiledFilePath()`
- `AdvancedTemplateEngine::$cacheStorage` (public `CacheInterface`) initialisé sur le `cachePath`

Effet : -33 erreurs.

### [STAB-02] Anti-régression `testPathNormalization` ✅

Le bug du leak `\tmp\…` était résolu implicitement par STAB-01 (FilesystemCache normalise désormais le chemin avant `mkdir`). Ajout d'une assertion `assertDirectoryDoesNotExist` pour empêcher la régression.

### [STAB-03] Tokens de template dans les attributs `<script>/<style>` ✅

Deux corrections sur `protectScriptAndStyleContent` :
1. La détection des tokens testait `$match[1]` (contenu) au lieu de `$match[0]` (balise complète) — les macros `##asset()##` apparaissent typiquement dans les attributs `src=`/`href=`.
2. Le pattern `\[\[|\[%` étendu à `\[\[|\[%|##\w+\(.*?\)##` pour couvrir les macros.

Effet : -1 échec (`testFullBlogTemplateRendering`).

### [STAB-04] Wrapping `TemplateException` aligné ✅

`testTemplateThrowsExceptionDuringExecution` attendait `RuntimeException` brute ; l'engine enveloppe systématiquement dans `TemplateException` (source map IMP-04). Test mis à jour pour vérifier la `TemplateException` + son `getPrevious()`.

Effet : -1 échec.

---

## Milestone 2 — Mode strict & DX ✅ Livré (2026-05-05)

### [DX-01] Mode strict + null-safety pour les appels de méthode ✅

`[[ obj.method() ]]` compilait en `$obj->method()` direct, ce qui :
- levait une `TypeError` PHP non-interceptable proprement quand `$obj === null` en mode non-strict,
- bypassait totalement le mode strict.

Implémentation : helper `Runtime\Access::callMethod()` null-safe (variadic args). `convertDotNotation` route désormais les appels via `Access::callMethod($obj, 'method', args...)`. Le mode strict détecte les retours null via le check `$__lunarTmp === null` final.

### [DX-02] Source maps complètes ✅

IMP-04 ne reportait que la ligne du fichier compilé — décalée en cas d'extends ou de header de dépendances. Implémentation : `Runtime\SourceMap::inject()` insère des marqueurs `<?php /* L:file:N */ ?>` au début de chaque ligne du source (consommés silencieusement par PHP), et `SourceMap::resolve()` les lit en sens inverse depuis le compiled file pour retrouver `(file, line)` d'origine. Le catch de `render()` traduit désormais correctement les erreurs vers le `.tpl` source, y compris à travers extends.

### [DX-03] Linter statique de templates en CLI ✅

Nouvelle commande `bin/lunar-template template:lint` qui détecte sans exécution :
- blocs non fermés (`[% if %]` sans `[% endif %]`, idem `for`/`block`)
- fermetures orphelines
- tokens non fermés (`[[`, `[#`, `[%`)
- paires mal appariées (`[% if %]` suivi de `[% endfor %]`)

Composants : `Linter\LintIssue` (DTO), `Linter\Linter` (analyse via pile), `Command\LintCommand` (rapport par fichier).

---

## Milestone 3 — Modernisation ✅ Livré (2026-05-05)

### [MOD-01] Adaptateur PSR-16 ✅

`src/Cache/Psr16Adapter.php` wrappe un `Psr\SimpleCache\CacheInterface` (Redis, Memcached, APCu, …) à l'interface Lunar. Compromis pragmatique : matérialise les contenus dans un `writeDir` local pour permettre `include`, et compare un timestamp companion stocké côté PSR-16 (clé `<key>.lunar_mtime`) pour `has(key, sourceTime)`.

### [MOD-02] Auto-discovery de macros/filtres via composer extra ✅

`src/Plugin/PluginDiscovery.php` lit `vendor/composer/installed.json` et trouve les classes déclarées dans `extra.lunar-template.macros` / `.filters` de chaque package. Validation de l'interface (`MacroInterface` / `FilterInterface`) avant instanciation. Helper `AdvancedTemplateEngine::loadPluginMacros()` pour intégration en une ligne.

### [MOD-03] Classes immuables en `final readonly class` ✅

Conversion ciblée des classes sans état mutable ni besoin d'extension : `Psr16Adapter`, `PluginDiscovery`, `ParsedTemplate`, `HtmlEscaper`, `InheritanceResolver`. Formalise l'immutabilité au niveau du langage. Les macros/filtres restent classiques (chacun encapsule via `private readonly`), une conversion massive serait du churn pour peu de bénéfice.

---

## Standards de qualité

Pour chaque chantier :

1. **TDD** : test (rouge) avant code (vert).
2. **Typage strict** : `declare(strict_types=1)`, types nominatifs partout.
3. **PSR-12** : via php-cs-fixer.
4. **PHPStan niveau 7** : zéro nouvelle erreur.
5. **Commits français** : message clair, mention de l'ID de chantier (`STAB-01`, `DX-02`...).
6. **Doc** : PHPDoc complet sur les nouvelles classes ; mise à jour des README si la syntaxe utilisateur change.

---

## Contributions

Voir [CONTRIBUTING.md](CONTRIBUTING.md) (à créer si absent — l'établir comme STAB-05 si l'équipe s'étend).
