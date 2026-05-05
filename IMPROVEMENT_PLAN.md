# Plan d'amélioration — Lunar Template Engine

Roadmap active du moteur de templates. Le plan d'audit initial (IMP-01..04) est livré ; ce document trace les chantiers restants et les nouvelles priorités.

**Dernière mise à jour** : 2026-05-05 (Milestone 1 livré)

---

## État actuel (après nettoyage)

- **PHP** : 8.3+ avec typage strict
- **Tests** : 1722 tests, **100 % passants**, 0 erreur, 0 échec
- **Qualité** : PHPStan niveau 7, php-cs-fixer
- **Documentation** : `README.md` (EN), `README.fr.md` (FR), `CLAUDE.md` (consignes projet)

### Récemment livré (décembre 2025 → mai 2026)

- **Issue #13** : Commentaires `[# ... #]` (multi-ligne, jamais émis dans la sortie HTML)
- **Appels de méthode** : `[[ obj.method() ]]` compile vers `$obj->method()`
- **Filtre `|raw` inline** : `[[ html|raw ]]` court-circuite l'échappement HTML
- **Auto-extension `.tpl`** : `[% extends 'base' %]` résout `base.tpl` si nécessaire
- **Fix regex** : `convertMacroArgument` détectait incorrectement les chaînes
- **Préservation des tokens** : `[[ ]]` et `[% %]` autorisés dans `<script>`/`<style>`
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

## Milestone 2 — Mode strict & DX (Priorité Moyenne)

### [DX-01] Mode strict pour les appels de méthode

**Type** : Feature — **Complexité** : 2/5 — **Statut** : 🟡 Idée

**Contexte** :
`[[ obj.method() ]]` compile vers `$obj->method() ?? ''`. En mode strict, la branche `isset()` est court-circuitée car invalide en PHP. Conséquence : on ne sait pas si `$obj` est null avant l'appel.

**Idée** :
En mode strict, générer :
```php
if (!isset($obj)) throw ...
if (!method_exists($obj, 'method')) throw ...
echo htmlspecialchars($obj->method() ?? '', ...);
```

### [DX-02] Source maps complètes

**Type** : Feature — **Complexité** : 3/5 — **Statut** : 🟡 Idée

**Contexte** :
IMP-04 a livré le source mapping de base. Une vraie source map (table `ligne_compilée → ligne_source`) permettrait de pointer la ligne exacte du `.tpl` même après extends/blocks/include.

### [DX-03] Linter de templates en CLI

**Type** : Feature — **Complexité** : 3/5 — **Statut** : 🟡 Idée

**Contexte** :
`bin/lunar-template lint <fichier.tpl>` qui détecte :
- tags non fermés (`[% if %]` sans `[% endif %]`)
- variables jamais définies (avec un `--data-fixture=...`)
- macros inconnues
- syntaxe invalide

---

## Milestone 3 — Modernisation (Priorité Basse)

### [MOD-01] Adaptateur PSR-16 (anciennement IMP-05)

**Type** : Refactoring — **Complexité** : 3/5 — **Statut** : 🟡 Idée

Remplacer ou wrapper `FilesystemCache` derrière `Psr\SimpleCache\CacheInterface` (déjà déclaré en dépendance dans `composer.json`). Ouvre la voie à Redis/Memcached/APCu.

**Critères d'acceptation** :
- [ ] `Lunar\Template\Cache\Psr16Adapter` qui wrappe un `Psr\SimpleCache\CacheInterface`.
- [ ] `TemplateRenderer` accepte au choix `CacheStorageInterface` ou `CacheInterface` PSR-16.
- [ ] Tests avec un mock PSR-16 (réutilisable).

### [MOD-02] Plugin de macros

**Type** : Architecture — **Complexité** : 3/5 — **Statut** : 🟡 Idée

Système de plugins permettant à un package tiers (ex. un thème) d'enregistrer ses macros et filtres via auto-discovery (composer extra).

### [MOD-03] Cible PHP 8.4 (readonly properties partout)

**Type** : Refactoring — **Complexité** : 2/5 — **Statut** : 🟡 Idée

Une fois la 8.3 obsolète, passer les DTOs/services en `readonly class`.

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
