# Plan d'Amélioration - Lunar Template Engine (Post-Audit)

Ce document détaille la feuille de route pour implémenter les recommandations issues de l'audit technique du 07/12/2025.

**Objectifs** :
1. Sécuriser les failles potentielles (XSS dans les macros).
2. Corriger les bugs critiques d'architecture (Invalidation du cache d'héritage).
3. Améliorer l'expérience développeur (DX) et la maintenabilité.

---

## Milestone 1: Robustesse & Sécurité (Priorité Haute)
**Objectif** : Rendre le moteur fiable en production et sécurisé par défaut.

### [IMP-01] Correction de l'invalidation du cache d'héritage
**Type** : Bugfix / Architecture
**Complexité** : Élevée (5/5)
**Statut** : ✅ Terminé (07/12/2025)
**Description** :
Actuellement, le système de cache ne vérifie que la date de modification du fichier template appelé (ex: `page.tpl`). Si ce template étend un parent (ex: `layout.tpl`), et que le parent est modifié, `page.tpl` n'est pas recompilé, servant une version obsolète.
Il faut modifier le compilateur pour extraire la liste des dépendances (parents) et les stocker (soit dans le fichier compilé, soit dans un fichier méta séparé). Le Renderer doit vérifier ces dépendances avant de servir le cache.

**Critères d'Acceptation** :
- [x] Le compilateur détecte tous les `[% extends "..." %]` et stocke ces chemins.
- [x] `TemplateRenderer::needsCompilation` vérifie récursivement la date de modification des parents.
- [x] Test d'intégration : Rendre `page.tpl`, modifier `layout.tpl`, rendre `page.tpl` -> le contenu doit changer.
- [x] Pas de régression de performance majeure sur le "Warm Run" (vérification rapide des mtimes).

### [IMP-02] Sécurisation des attributs de Macros (AttributeBag)
**Type** : Sécurité
**Complexité** : Moyenne (3/5)
**Statut** : ✅ Terminé (07/12/2025)
**Description** :
Les macros actuelles (ex: `InputMacro`) concatènent des chaînes HTML manuellement (`class="' . $class . '"`). Un oubli d'échappement crée une faille XSS.
Il faut créer une classe utilitaire `AttributeBag` ou `HtmlHelper` qui accepte un tableau d'attributs et génère la chaîne HTML en forçant l'échappement via `htmlspecialchars`.

**Critères d'Acceptation** :
- [x] Création de la classe `Lunar\Template\Html\AttributeBag`.
- [x] Refactoring de `InputMacro` pour utiliser `AttributeBag`.
- [x] Test unitaire : Vérifier que `AttributeBag` échappe correctement les guillemets et caractères spéciaux.
- [x] Les macros existantes continuent de fonctionner à l'identique.

---

## Milestone 2: Expérience Développeur (DX)
**Objectif** : Faciliter le débogage et l'intégration.

### [IMP-03] Mode Strict pour les variables
**Type** : Feature
**Complexité** : Faible (2/5)
**Statut** : ✅ Terminé (07/12/2025)
**Description** :
Par défaut, une variable inconnue `[[ typo ]]` affiche une chaîne vide. C'est bien pour la prod, mais terrible pour le dev.
Ajouter une option de configuration `strict_variables` (bool). Si true, lancer une exception `TemplateException` si une variable est null ou non définie.

**Critères d'Acceptation** :
- [x] Ajouter la config au constructeur de `AdvancedTemplateEngine` (via `setStrictVariables`).
- [x] Modifier le code généré par le compilateur pour vérifier l'existence si le mode est actif (`!isset($var)`).
- [x] Test : Vérifier que l'exception est levée uniquement en mode strict pour les variables indéfinies.
- [x] Test : Vérifier que l'exception est levée uniquement en mode strict pour les variables nulles.
- [x] Test : Vérifier que le comportement par défaut (non strict) n'est pas modifié.

### [IMP-04] Source Maps pour le débogage (Basic)
**Type** : Feature
**Complexité** : Moyenne (3/5)
**Description** :
Les erreurs PHP (ex: appel de méthode sur null) se produisent dans le fichier compilé (`/cache/hash.php:45`). Le développeur ne sait pas à quelle ligne du `.tpl` cela correspond.
Il faut capturer les erreurs fatales/exceptions lors du `include` du template compilé, et essayer de mapper la ligne PHP vers la ligne du template original (via une table de correspondance générée lors de la compilation ou une approximation).

**Critères d'Acceptation** :
- [ ] Le compilateur génère des commentaires `/* LINE:12 */` ou une map.
- [ ] `TemplateRenderer` capture les `Throwable`.
- [ ] L'exception relancée contient le message "Error in template.tpl at line X".

---

## Milestone 3: Modernisation (Architecture)
**Objectif** : Standardiser le code avec l'écosystème PHP moderne.

### [IMP-05] Adaptateur PSR-16 (Simple Cache)
**Type** : Refactoring
**Complexité** : Moyenne (3/5)
**Description** :
Remplacer l'implémentation propriétaire `FilesystemCache` par une dépendance vers `psr/simple-cache`. Fournir une implémentation par défaut basée sur fichier, mais permettre l'injection de n'importe quel `CacheInterface` PSR-16 (Redis, Memcached).

**Critères d'Acceptation** :
- [ ] `Lunar\Template\Cache\CacheInterface` étend `Psr\SimpleCache\CacheInterface` (ou est remplacé par).
- [ ] Les tests de cache utilisent des mocks PSR-16.

---

## Standards de Qualité requis
Pour chaque issue ci-dessus :
1.  **TDD** : Créer le test (rouge) avant le code.
2.  **Typage** : `declare(strict_types=1)`, typage fort des arguments/retours.
3.  **Doc** : PHPDoc complet sur les nouvelles classes.
4.  **Immutabilité** : Utiliser `readonly` pour les DTOs/Services si possible.
