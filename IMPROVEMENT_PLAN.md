# Plan d'amélioration — Lunar Template Engine

Roadmap active du moteur de templates. Le plan d'audit initial (IMP-01..04) est livré ; ce document trace les chantiers restants et les nouvelles priorités.

**Dernière mise à jour** : 2026-05-05

---

## État actuel (après nettoyage)

- **PHP** : 8.3+ avec typage strict
- **Tests** : 1722 tests, ~98 % passants (33 erreurs + 3 échecs résiduels, tous documentés ci-dessous)
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

## Milestone 1 — Stabilisation (Priorité Haute)

### [STAB-01] Architecture Cache : unifier CacheInterface

**Type** : Bugfix / Refactoring — **Complexité** : 4/5 — **Statut** : 🔴 À faire

**Problème** :
`FilesystemCache implements CacheStorageInterface`, mais :
- `CacheWarmer::__construct` typé `CacheInterface` (qui n'existe pas)
- `FilesystemCacheTest::testImplementsCacheInterface` attend `CacheInterface`
- Méthodes `has()`, `getPath()`, `getDirectory()` testées mais non exposées par `CacheStorageInterface`

**Conséquence** : 33 erreurs sur la suite (CacheWarmer, FilesystemCache, ConstructorCreatesDirectories).

**Critères d'acceptation** :
- [ ] Décider entre PSR-16 (`Psr\SimpleCache\CacheInterface`) et l'interface maison.
- [ ] Renommer/aligner `CacheStorageInterface` ↔ `CacheInterface` cohéremment.
- [ ] Exposer `has()`, `getPath()`/`getDirectory()` via l'interface ou retirer ces tests.
- [ ] Les 33 erreurs disparaissent.

### [STAB-02] Fix `testPathNormalization` (leaks de dossiers `\tmp\` sous Linux)

**Type** : Bugfix tests — **Complexité** : 1/5 — **Statut** : 🔴 À faire

**Problème** :
Le test `tests/AdvancedTemplateEngineTest.php::testPathNormalization` fait :
```php
str_replace('/', '\\', $this->cacheDir . '_normalized')
```
Sous Linux, ceci crée un dossier littéral `\tmp\lunar-cache-tests-...\_normalized` à la racine du projet (les `\` ne sont pas séparateurs sous Linux). Le cleanup utilise le chemin original avec `/`, donc ne nettoie jamais.

**Critères d'acceptation** :
- [ ] Skip le test si `PHP_OS_FAMILY !== 'Windows'`, OU :
- [ ] Mock le path sans toucher au filesystem, OU :
- [ ] Cleanup correct du chemin backslash.
- [ ] Plus aucun `\tmp\*` créé après une exécution complète des tests.

### [STAB-03] Fix `testFullBlogTemplateRendering`

**Type** : Bugfix — **Complexité** : 2/5 — **Statut** : 🔴 À faire

**Problème** :
Le test échoue sur l'assertion `src="/assets/js/blog.js"`. Le block `[% block scripts %]` du template enfant n'écrase pas correctement celui du parent dans certains cas d'héritage chaîné. Reproduit sur main avant nos commits récents — n'est pas une régression.

**Critères d'acceptation** :
- [ ] Identifier le défaut dans `processExtends` ou `extractBlocks`.
- [ ] Ajouter un test unitaire ciblé (au-delà du test d'intégration).
- [ ] Le test d'intégration passe sans modification.

### [STAB-04] `testTemplateThrowsExceptionDuringExecution`

**Type** : Bugfix tests — **Complexité** : 1/5 — **Statut** : 🔴 À faire

**Problème** :
Le test attend `RuntimeException` mais reçoit `TemplateException`. Soit le test est obsolète (refactoring de la hiérarchie d'exceptions non répercuté), soit l'implémentation devrait propager différemment.

**Critères d'acceptation** :
- [ ] Décider quel comportement est correct.
- [ ] Aligner test ↔ implémentation.

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
