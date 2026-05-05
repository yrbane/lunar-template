# Audit Lunar Template — état au 2026-05-05

> Ré-évaluation de l'audit initial (v1.5.0) confronté à l'état réel du code après la livraison de la v1.5.0 et le correctif de sécurité du contexte de rendu.
>
> **Légende** : ✅ livré · 🟡 partiellement traité · ❌ encore ouvert.

---

## 1. Vue d'ensemble

Lunar Template est un moteur de templates PHP autonome (PHP 8.3+, namespace `Lunar\Template`). Pipeline en trois passes : **Parser** → **Compiler** → **Renderer**, avec cache filesystem et adaptateur PSR-16 optionnel. La compilation reste régex-based mais la modularité, les hooks (registries, plugin discovery, source maps, linter) et la couverture de tests (2002 cas) sont en place.

État de la base au moment de l'audit :

- 2002 tests PHPUnit, **0 erreur, 0 failure**
- PHPStan niveau 7 : **OK**
- php-cs-fixer : clean
- Tag livré : `v1.5.0` · CI matrice PHP 8.3 / 8.4 / 8.5

---

## 2. Audit initial — confrontation à la réalité

### 2.1 Architecture & code

| Constat de l'audit | Statut | Commentaire |
|---|---|---|
| Modularité émergente (Compiler / Renderer / Registries) | ✅ | Livré : `TemplateCompiler`, `TemplateRenderer`, `FilterRegistry`, `MacroRegistry`, `InheritanceResolver`. |
| Typage fort + PHPStan niveau 7 | ✅ | Conservé. `final readonly class` appliqué là où c'est pertinent (MOD-03). |
| Source maps DX | ✅ | DX-02 : marqueurs `L:file:N` à la compilation, résolution dans les exceptions, propagation à travers `extends`. |
| Duplication `AdvancedTemplateEngine` ↔ `TemplateRenderer` / `TemplateCompiler` | 🟡 | Les deux moteurs coexistent toujours. La compilation est centralisée dans `TemplateCompiler`, mais `AdvancedTemplateEngine` garde sa propre pile de directives historiques. À déprécier (cf. §4 — RFC-01). |
| Parsing par regex | 🟡 | Toujours régex. Acceptable tant que les cas couverts par la grammaire sont stables ; à reconsidérer avec un lexer si l'on ajoute des composants `[< Tag />]`. |
| CLI vs Library : CLI utilise encore `AdvancedTemplateEngine` | ❌ | `bin/lunar-template` (RenderCommand, CompileCommand, WarmCommand, CheckCommand) instancie `AdvancedTemplateEngine`. Migration vers `TemplateRenderer` + `CacheWarmer` à planifier. |
| Filtres incohérents entre les deux moteurs | 🟡 | `TemplateCompiler` traite les filtres pipe ; `AdvancedTemplateEngine` n'a qu'un support rudimentaire `|raw`. Sera résolu de fait par RFC-01. |

### 2.2 Sécurité

| Constat | Statut | Commentaire |
|---|---|---|
| Échappement HTML par défaut | ✅ | `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')` systématique, `HtmlEscaper` configurable. |
| `PathValidator` empêche les traversées de répertoire | ✅ | Couvert par tests dédiés. |
| `Access::get` / `Access::callMethod` null-safe | ✅ | Issue #14 close. |
| `extract($variables, EXTR_OVERWRITE)` permet à un appelant d'écraser `$compiledFile` → **RCE** | ✅ | **Corrigé** : `RESERVED_VARIABLE_NAMES` filtré avant extract, variables internes renommées en `$__lunar_*`, `EXTR_SKIP` au lieu de `EXTR_OVERWRITE`. Tests dédiés dans `tests/Unit/Security/RenderContextIsolationTest.php`. |
| `[[! ... !]]` désactive l'échappement (DX dangereuse) | 🟡 | Reste documenté comme intentionnel. Piste : marquer ces zones via le linter avec un severity `notice`. |

> **Remarque (correctif RCE)**. La séquence `extract()` puis `include $compiledFile;` autorisait à un appelant qui passait `'compiledFile' => '/path/evil.php'` de détourner l'inclusion. Le correctif (1) retire les noms réservés du tableau, (2) renomme la variable locale en `$__lunar_compiled_file` (qui ne peut plus être ciblée naïvement), (3) passe en `EXTR_SKIP` pour qu'aucune variable interne survivante ne soit écrasée, (4) `unset()` les variables auxiliaires juste avant l'`include`. Les deux moteurs (`AdvancedTemplateEngine` et `TemplateRenderer`) sont alignés.

### 2.3 Performance

| Constat | Statut | Commentaire |
|---|---|---|
| Cache de compilation effectif | ✅ | Filesystem + PSR-16 adapter (Redis/Memcached/APCu). |
| Suivi des dépendances minimal (commentaire en tête) | 🟡 | Toujours en place. Optimisation possible : sidecar JSON `<file>.deps.json` lu sans parser le PHP. |
| Regex overhead sur très gros templates | 🟡 | Mesuré dans `benchmarks/render_pipeline.php`. À surveiller, pas critique. |
| `OPcache` exploité | ✅ | Code généré = `.php` natif. |
| Pré-compilation (warm-up) | ✅ | `CacheWarmer` + commande CLI `template:warm`. |

### 2.4 Fonctionnel

| Constat | Statut |
|---|---|
| Richesse des macros (~40) | ✅ |
| Linter statique pour CI/CD | ✅ DX-03 |
| Couverture macros legacy | ✅ `tests/Macro/LegacyMacrosCoverageTest.php` |

---

## 3. Risques résiduels

1. **Duplication des deux moteurs** : tant que `AdvancedTemplateEngine` et `TemplateRenderer` coexistent, chaque fonctionnalité a deux portes d'entrée et deux comportements possibles. Source future de drift (le correctif RCE a déjà dû être appliqué deux fois).
2. **CLI désaligné** : les commandes utilisent l'ancienne façade. Toute amélioration n'arrive donc pas aux utilisateurs CLI.
3. **Suivi de dépendances fragile** : un fichier compilé corrompu en tête (header `DEPENDENCIES`) invalide silencieusement la chaîne d'invalidation cache.
4. **`[[! !]]`** non couvert par `AdvancedTemplateEngine` (cf. §2.1) : surface d'incohérence comportementale entre moteurs.
5. **Aucune protection sandbox** : les templates peuvent appeler n'importe quelle macro/filtre enregistrée. Pertinent uniquement si le projet accepte un jour des templates fournis par utilisateur.

---

## 4. Roadmap proposée (post-v1.5.0)

Trois milestones, ordonnées par ratio impact / effort.

### Milestone 6 — Unification du moteur (RFC-01 → RFC-03)

| ID | Titre | Effort | Statut |
|---|---|---|---|
| RFC-01 | Migrer le CLI sur `TemplateRenderer` (RenderCommand, CompileCommand, WarmCommand, CheckCommand) | M | ❌ |
| RFC-02 | Ajouter `@deprecated` sur `AdvancedTemplateEngine`, exposer un `EngineFactory` qui retourne un `TemplateRenderer` configuré | S | ❌ |
| RFC-03 | Garantir parité fonctionnelle (`[[! !]]`, filtres pipe, plugin macros) entre les deux moteurs jusqu'à la suppression | M | ❌ |

### Milestone 7 — Robustesse parsing & dépendances

| ID | Titre | Effort | Statut |
|---|---|---|---|
| ROB-01 | Sidecar JSON `<compiled>.deps.json` (mtime + liste de dépendances) au lieu du header PHP | S | ❌ |
| ROB-02 | Suite de fuzz-tests sur le compilateur (entrées exotiques, balises mal formées) | M | ❌ |
| ROB-03 | Élargir le linter : détecter les variables potentiellement indéfinies vs. schéma optionnel | M | ❌ |

### Milestone 8 — Nouvelles capacités (sur demande utilisateur)

| ID | Titre | Effort | Statut |
|---|---|---|---|
| FEAT-01 | i18n — filtre `\|trans` + macro `##t()##` + chargeur JSON/PHP | M | ❌ |
| FEAT-02 | Mode sandbox (whitelist macros/filtres, désactivation `include`) | L | ❌ |
| FEAT-03 | `DirectiveRegistry` pour des `[% myTag %] / [% endmyTag %]` custom | M | ❌ |
| FEAT-04 | Composants `[< Icon name="user" />]` (lexer requis) | L | ❌ |

### Travaux transverses (DX & écosystème)

- **Adaptateurs framework** : bridges officiels Symfony / Laravel / Slim (S → M chacun).
- **Profilage de rendu** : timer compile vs render, exposition via un `Profiler` injectable (S).
- **Extension VS Code** : coloration + autocomplétion macros/filtres (XL — sortie repo).

---

## 5. Ce qui a été appliqué dans cet audit

1. **Correctif RCE — extract() context isolation** : `AdvancedTemplateEngine::runCompiledTemplate()` + `TemplateRenderer::executeTemplate()` filtrent `RESERVED_VARIABLE_NAMES`, renomment les locaux en `$__lunar_*`, passent en `EXTR_SKIP`. Tests dédiés ajoutés.
2. **PHPStan stabilisé** : `MarkdownFilter::applyWithCommonMark()` annoté pour ignorer la classe optionnelle de manière propre.
3. **Documentation roadmap** : ce fichier remplace l'audit générique pré-livraison par une feuille de route actionnable.

---

## 6. Notes méthodologiques

- L'audit initial a été écrit avant la v1.5.0 et lui a essentiellement servi de cahier des charges. La majorité de ses recommandations sont aujourd'hui livrées (cf. tableaux §2).
- Cet audit se concentre désormais sur ce qui reste — duplication moteur, CLI, robustesse parsing, nouvelles capacités — et trace le **risque de sécurité critique** restant comme **résolu**.
- Les commits associés au correctif sécurité utilisent le préfixe `fix(security)` pour faciliter la traçabilité.
