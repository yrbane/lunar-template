# Lunar Template Engine

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg)]()

[English](README.md)

**Lunar Template Engine** est un moteur de templates autonome et avancé pour PHP 8.3+, offrant l'héritage de templates, les blocs, les macros et un cache intelligent.

## Fonctionnalités

- **Héritage de templates** — Étendez vos templates avec `[% extends 'parent.tpl' %]`
- **Héritage multi-niveaux** — Enchaînez plusieurs niveaux de templates avec support de `[% parent %]`
- **Système de blocs** — Surchargez les blocs parents avec `[% block content %]`
- **Macros** — Composants réutilisables avec `##macroName(args)##`
- **Directives** — `[% set %]` et `[% include %]` pour un contrôle avancé
- **Commentaires** — Supprimés à la compilation avec `[# ... #]` (multi-lignes, jamais émis)
- **Cache intelligent** — Compilation et mise en cache automatiques avec préchauffage
- **Sécurisé** — Protection XSS avec échappement HTML automatique et validation des chemins
- **Syntaxe claire** — Syntaxe de template intuitive
- **Architecture modulaire** — Composants Parser, Compiler, Renderer et Cache
- **Autonome** — Aucune dépendance, compatible avec tous les frameworks
- **100 % de couverture de tests** — Entièrement testé avec PHPStan niveau 7

## Installation

```bash
composer require yrbane/lunar-template
```

## Démarrage rapide

### Utilisation de AdvancedTemplateEngine (tout-en-un)

```php
<?php
use Lunar\Template\AdvancedTemplateEngine;

$engine = new AdvancedTemplateEngine(
    templatePath: '/chemin/vers/templates',
    cachePath: '/chemin/vers/cache'
);

$html = $engine->render('blog/article', [
    'title' => 'Mon article',
    'content' => 'Contenu de l\'article…',
    'author' => 'Jean Dupont',
]);

echo $html;
```

### Utilisation des composants modulaires

```php
<?php
use Lunar\Template\Renderer\TemplateRenderer;
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Cache\CacheWarmer;

// Créer le renderer avec cache
$renderer = new TemplateRenderer(
    templatePath: '/chemin/vers/templates',
    cachePath: '/chemin/vers/cache'
);

// Définir les variables par défaut
$renderer->setDefaultVariables([
    'siteName' => 'Mon site web',
    'currentYear' => date('Y'),
]);

// Enregistrer des macros
$renderer->registerMacro('uppercase', fn (string $text) => strtoupper($text));

// Rendre un template
$html = $renderer->render('page', ['title' => 'Accueil']);

// Préchauffer le cache
$cache = new FilesystemCache('/chemin/vers/cache');
$warmer = new CacheWarmer('/chemin/vers/templates', $cache);
$warmer->warmRecursive(); // Précompiler tous les templates
```

## Syntaxe des templates

### Variables

```html
<!-- Variable simple (échappée automatiquement) -->
<h1>[[ title ]]</h1>

<!-- Propriété d'objet / notation pointée -->
<p>Par [[ author.name ]]</p>

<!-- Accès tableau -->
<span>[[ tags.0 ]]</span>

<!-- Accès imbriqué -->
<p>[[ user.profile.email ]]</p>

<!-- Appel de méthode sur un objet -->
<p>[[ user.getFullName() ]]</p>

<!-- Propriété d'objet (DTO, readonly class) -->
<p>[[ lang.code ]]</p>     <!-- fonctionne aussi bien avec un tableau qu'avec un objet -->

<!-- Filtre raw inline (pas d'échappement) -->
<div>[[ trustedHtml|raw ]]</div>
```

> **Accès hybride** : la notation pointée fonctionne indifféremment sur les tableaux et les objets. `[[ user.name ]]` se résout en `$user['name']` si `$user` est un tableau, ou en `$user->name` si `$user` est un objet (par exemple un DTO `final readonly class`). Aucune conversion côté contrôleur n'est nécessaire.

### Commentaires

Utilisez `[# ... #]` pour écrire des commentaires de template. Ils sont supprimés à la compilation et n'apparaissent jamais dans la sortie rendue (contrairement à `<!-- ... -->`) :

```html
[# Ce bloc est invisible côté navigateur #]
<h1>[[ title ]]</h1>

[#
  Les commentaires multi-lignes sont supportés.
  Pratiques pour documenter des blocs complexes
  sans fuiter l'explication dans le HTML généré.
#]
```

> **Note** : les tokens à l'intérieur d'un commentaire (`[[ var ]]`, `[% if %]`, etc.) ne sont pas interprétés — toute la région est supprimée avant compilation.

### Sortie brute (sans échappement)

Utilisez la syntaxe `[[! ... !]]` pour afficher du contenu sans échappement HTML :

```html
<!-- Sortie brute — SANS échappement (uniquement pour contenu de confiance !) -->
[[! htmlContent !]]

<!-- Avec filtres -->
[[! trustedHtml | trim !]]

<!-- Pour le contenu utilisateur, préférez le filtre raw -->
[[ content | raw ]]
```

> **Attention** : n'utilisez `[[! !]]` qu'avec du contenu de confiance. Pour les entrées utilisateur, utilisez toujours la syntaxe `[[ ]]` avec échappement automatique.

### Filtres

Les filtres transforment les valeurs des variables avec la syntaxe pipe :

```html
<!-- Filtre simple -->
<h1>[[ title | upper ]]</h1>

<!-- Filtres enchaînés -->
<p>[[ text | trim | lower | slug ]]</p>

<!-- Filtre avec arguments -->
<p>[[ price | number_format(2) ]]</p>
<p>[[ description | truncate(100, "…") ]]</p>

<!-- Sortie brute (sans échappement) -->
<div>[[ htmlContent | raw ]]</div>
```

#### Filtres intégrés

**Filtres de chaînes :**
- `upper` — Majuscules
- `lower` — Minuscules
- `capitalize` — Première lettre en majuscule
- `title` — Casse de titre
- `trim`, `ltrim`, `rtrim` — Supprimer les espaces
- `slug` — Slug URL
- `truncate(length, suffix)` — Tronquer le texte
- `excerpt(length)` — Extrait intelligent
- `wordwrap(width)` — Retour à la ligne
- `reverse` — Inverser la chaîne
- `repeat(times)` — Répéter
- `pad_left(length, char)`, `pad_right(length, char)` — Remplir
- `replace(search, replace)` — Remplacer
- `split(delimiter)` — Diviser en tableau

**Filtres numériques :**
- `number_format(decimals, dec_point, thousands_sep)` — Formater un nombre
- `round(precision)` — Arrondir
- `floor`, `ceil` — Arrondir vers le bas/haut
- `abs` — Valeur absolue
- `currency(symbol)` — Formater en devise
- `percent(decimals)` — Formater en pourcentage
- `ordinal` — Suffixe ordinal (1st, 2nd, 3rd)
- `filesize(decimals)` — Taille de fichier lisible

**Filtres de tableaux :**
- `first`, `last` — Premier/dernier élément
- `length` — Longueur du tableau/chaîne
- `keys`, `values` — Clés/valeurs du tableau
- `sort(key, direction)` — Trier
- `slice(start, length)` — Extraire une portion
- `merge(array)` — Fusionner
- `unique` — Supprimer les doublons
- `join(glue, lastGlue)` — Joindre en chaîne
- `chunk(size)` — Diviser en morceaux
- `pluck(key)` — Extraire les valeurs par clé
- `filter(key, value)` — Filtrer
- `map(filter)` — Appliquer un filtre à chaque élément
- `group_by(key)` — Grouper par clé
- `random` — Élément aléatoire
- `shuffle` — Mélanger

**Filtres de dates :**
- `date(format)` — Formater une date
- `ago` — Temps relatif (ex. : « il y a 5 minutes »)
- `relative` — Date relative (ex. : « hier »)

**Filtres d'encodage :**
- `base64_encode`, `base64_decode` — Base64
- `url_encode`, `url_decode` — Encodage URL
- `json_encode(pretty)`, `json_decode` — JSON
- `md5`, `sha1`, `sha256` — Hachage

**Filtres HTML :**
- `raw` — Sans échappement (attention !)
- `escape(strategy)` — Échapper (html, js, css, url)
- `striptags(allowed)` — Supprimer les balises HTML
- `nl2br` — Nouvelles lignes en `<br>`
- `spaceless` — Supprimer les espaces entre balises

**Filtres de mise en forme HTML :**
- `markdown` — Convertir Markdown en HTML (gras, italique, liens, titres, listes, code)
- `linkify(target)` — Convertir automatiquement URLs et e-mails en liens cliquables
- `list(type, class)` — Convertir un tableau en liste HTML (`<ul>` ou `<ol>`)
- `table(hasHeader, class)` — Convertir un tableau 2D en table HTML
- `attributes` — Convertir un tableau en attributs HTML
- `wrap(tag, class, id)` — Envelopper le contenu dans une balise HTML
- `highlight(term, class)` — Surligner les termes recherchés avec `<mark>`
- `paragraph`, `p` — Convertir les blocs de texte en paragraphes `<p>`
- `heading`, `h` — Créer un titre HTML (h1-h6) : `[[ titre | h(2) ]]`
- `anchor`, `a` — Créer un lien : `[[ url | a("Cliquez ici") ]]`
- `excerpt_html(length, suffix)` — Extraire le texte et créer un extrait
- `class_list` — Construire une chaîne de classes CSS à partir d'un tableau

**Filtres d'éléments HTML :**
- `div(class, id)` — Envelopper dans `<div>` : `[[ contenu | div("container") ]]`
- `span(class, id)` — Envelopper dans `<span>` : `[[ texte | span("highlight") ]]`
- `strong(class)` — Envelopper dans `<strong>` : `[[ texte | strong ]]`
- `em(class)` — Envelopper dans `<em>` (italique) : `[[ texte | em ]]`
- `small(class)` — Envelopper dans `<small>` : `[[ texte | small ]]`
- `code(language)` — Envelopper dans `<code>` : `[[ code | code("php") ]]`
- `pre(class)` — Envelopper dans `<pre>` : `[[ texte | pre ]]`
- `blockquote(cite, class)` — Créer une citation avec attribution optionnelle
- `abbr(title)` — Créer une abréviation : `[[ "HTML" | abbr("HyperText Markup Language") ]]`
- `time(format, class)` — Créer un élément `<time>` avec attribut datetime
- `img(alt, class, lazy)` — Créer `<img>` : `[[ url | img("Texte alt", "photo", true) ]]`
- `video(autoplay, loop, muted, class)` — Créer `<video>` avec contrôles
- `audio(autoplay, loop, class)` — Créer `<audio>` avec contrôles
- `iframe(title, class, lazy)` — Créer `<iframe>` avec allowfullscreen
- `progress(max, class)` — Créer barre `<progress>` : `[[ 75 | progress ]]`
- `meter(min, max, low, high)` — Créer jauge `<meter>`
- `badge(variant)` — Créer badge : `[[ "Nouveau" | badge("success") ]]`
- `button(type, class, disabled)` — Créer `<button>` : `[[ "Envoyer" | button("submit", "btn") ]]`

### Conditions

```html
[% if user.isLoggedIn %]
    <p>Bienvenue, [[ user.name ]] !</p>
[% elseif user.isGuest %]
    <p>Bonjour, invité !</p>
[% else %]
    <p>Veuillez vous connecter.</p>
[% endif %]

<!-- Avec opérateurs de comparaison -->
[% if count > 0 %]
    <p>Vous avez [[ count ]] éléments.</p>
[% endif %]

[% if status == "active" %]
    <span class="active">Actif</span>
[% endif %]
```

### Boucles

```html
[% for article in articles %]
    <article>
        <h2>[[ article.title ]]</h2>
        <p>[[ article.excerpt ]]</p>
    </article>
[% endfor %]
```

### Assignation de variables

```html
[% set pageTitle = "Ma page" %]
[% set count = 42 %]
[% set isActive = true %]
[% set userName = user.name %]

<h1>[[ pageTitle ]]</h1>
```

### Inclusion de templates

```html
<!-- Inclusion simple -->
[% include 'partials/header.tpl' %]

<!-- Inclusion avec variables -->
[% include 'components/card.tpl' with {title: "Titre de la carte", count: 5} %]

<!-- Inclusion avec template dynamique -->
[% include templateName %]
```

### Héritage de templates

**Template de base (`base.html.tpl`) :**
```html
<!DOCTYPE html>
<html>
<head>
    <title>[% block title %]Titre par défaut[% endblock %]</title>
</head>
<body>
    <header>
        [% block header %]
            <h1>Mon site web</h1>
        [% endblock %]
    </header>

    <main>
        [% block content %]
            Contenu par défaut
        [% endblock %]
    </main>

    <footer>
        [% block footer %]
            <p>&copy; 2025 Mon site web</p>
        [% endblock %]
    </footer>
</body>
</html>
```

**Template enfant (`article.html.tpl`) :**
```html
[% extends 'base.html.tpl' %]

[% block title %][[ article.title ]] — Mon blog[% endblock %]

[% block content %]
    <article>
        <h1>[[ article.title ]]</h1>
        <time>[[ article.publishedAt ]]</time>
        <div>[[ article.content ]]</div>
    </article>
[% endblock %]
```

### Héritage multi-niveaux avec bloc parent

```html
<!-- grandparent.tpl -->
<div class="container">
    [% block content %]Contenu de base[% endblock %]
</div>

<!-- parent.tpl -->
[% extends 'grandparent.tpl' %]
[% block content %]
    <div class="wrapper">
        [% parent %]  <!-- Inclut « Contenu de base » -->
    </div>
[% endblock %]

<!-- child.tpl -->
[% extends 'parent.tpl' %]
[% block content %]
    <h1>Titre</h1>
    [% parent %]  <!-- Inclut le contenu parent enveloppé -->
[% endblock %]
```

### Macros

**Enregistrer une macro :**
```php
$engine->registerMacro('url', function (string $routeName, array $params = []) {
    return "/route/{$routeName}?" . http_build_query($params);
});
```

**Utilisation dans un template :**
```html
<a href="##url('blog.show', ['id' => article.id])##">Lire la suite</a>
```

**Macros intégrées :**

#### Macros de base
```html
<!-- URLs des assets -->
<link rel="stylesheet" href="##asset('/css/app.css')##">

<!-- Formatage de date -->
<time>##date('j F Y')##</time>
<time>##date('Y-m-d', article.publishedAt)##</time>
```

#### Macros utilitaires
```html
<!-- Génération UUID -->
##uuid()##                              <!-- ex. : 550e8400-e29b-41d4-a716-446655440000 -->

<!-- Valeurs aléatoires -->
##random()##                            <!-- Aléatoire 0-100 -->
##random(1, 10)##                       <!-- Aléatoire 1-10 -->
##random("string", 16)##                <!-- Chaîne hexadécimale -->
##random("alpha", 8)##                  <!-- Lettres aléatoires -->

<!-- Lorem ipsum -->
##lorem()##                             <!-- 1 paragraphe -->
##lorem(3)##                            <!-- 3 paragraphes -->
##lorem("words", 10)##                  <!-- 10 mots -->

<!-- Heure actuelle -->
##now()##                               <!-- Timestamp Unix -->
##now("Y-m-d")##                        <!-- Date formatée -->
##now("iso")##                          <!-- ISO 8601 -->

<!-- Pluralisation -->
##pluralize(5, "article", "articles")## <!-- « 5 articles » -->

<!-- Formatage monétaire -->
##money(99.99, "EUR")##                 <!-- 99,99 € -->
##money(50, "USD")##                    <!-- $50.00 -->

<!-- Masquage de données -->
##mask("jean@exemple.fr", "email")##    <!-- j***@exemple.fr -->

<!-- Manipulation de couleurs -->
##color("primary")##                    <!-- #4F46E5 -->
##color("#FF0000", "lighten", 20)##     <!-- Éclaircir de 20 % -->

<!-- Temps relatif -->
##timeago(timestamp)##                  <!-- « il y a 5 minutes » -->

<!-- Compte à rebours -->
##countdown("2025-12-31")##             <!-- « 28 jours, 5 heures » -->
```

#### Macros de sécurité
```html
<!-- Protection CSRF -->
##csrf()##                              <!-- Champ input caché -->
##csrf("token")##                       <!-- Valeur du token uniquement -->
##csrf("meta")##                        <!-- Balise meta -->

<!-- Nonce CSP -->
##nonce()##                             <!-- Valeur du nonce -->
##nonce("script")##                     <!-- Attribut nonce="…" -->

<!-- Protection anti-spam Honeypot -->
##honeypot()##                          <!-- Champ honeypot caché -->
```

#### Macros de formulaire
```html
<!-- Éléments input -->
##input("email", "email")##
##input("nom", "text", "Jean")##

<!-- Zone de texte -->
##textarea("message")##

<!-- Liste déroulante -->
##select("pays", pays, "FR", "Choisir…")##

<!-- Cases à cocher et boutons radio -->
##checkbox("accepter", "1", false, "J'accepte")##
##radio("genre", "homme", false, "Homme")##

<!-- Autres éléments -->
##label("email", "Adresse e-mail")##
##hidden("user_id", "123")##
##method("PUT")##                       <!-- Spoofing de méthode -->
```

#### Macros HTML/Meta
```html
<!-- Script et style -->
##script("/js/app.js", "defer")##
##style("/css/app.css")##

<!-- Balises meta -->
##meta("description", "Description de la page")##

<!-- Open Graph -->
##og("title", "Titre de la page")##

<!-- Twitter Cards -->
##twitter("card", "summary_large_image")##

<!-- SEO -->
##canonical("https://exemple.fr/page")##
##favicon("/favicon.ico")##

<!-- Schema.org JSON-LD -->
##schema("Organization", data)##

<!-- Fil d'Ariane -->
##breadcrumbs(items)##

<!-- Icônes -->
##icon("user")##                        <!-- Heroicons outline -->
##icon("fa-home", "fa")##               <!-- Font Awesome -->
```

#### Macros image/média
```html
<!-- Gravatar -->
##gravatar("email@exemple.fr", 200)##

<!-- Avatars UI -->
##avatar("Jean Dupont", 100)##

<!-- Images placeholder -->
##placeholder(800, 600)##

<!-- Codes QR -->
##qrcode("https://exemple.fr")##
```

#### Macros d'intégration
```html
<!-- YouTube -->
##youtube("dQw4w9WgXcQ")##

<!-- Vimeo -->
##vimeo("123456789")##
```

#### Macros de partage social
```html
##share("twitter", "https://exemple.fr", "Regardez ça !")##
##share("facebook", "https://exemple.fr")##
##share("linkedin", "https://exemple.fr", "Titre")##
##share("email", "https://exemple.fr", "Sujet", "Corps")##
##share("whatsapp", "https://exemple.fr", "Message")##
```

## Architecture

### Composants

| Composant | Description |
|-----------|-------------|
| `TemplateParser` | Analyse le source du template en composants structurés |
| `TemplateCompiler` | Compile la syntaxe du template en code PHP |
| `TemplateRenderer` | Rend les templates avec injection de variables |
| `InheritanceResolver` | Gère l'héritage multi-niveaux des templates |
| `FilterRegistry` | Gestion centralisée des filtres avec 50+ filtres intégrés |
| `FilesystemCache` | Cache de templates basé sur le système de fichiers |
| `CacheWarmer` | Précompile les templates pour la production |
| `MacroRegistry` | Gestion centralisée des macros |
| `PathValidator` | Sécurité : empêche la traversée de répertoire |
| `HtmlEscaper` | Sécurité : protection XSS |

### Exceptions

```php
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Exception\SyntaxException;
use Lunar\Template\Exception\CircularInheritanceException;
use Lunar\Template\Exception\MacroNotFoundException;

try {
    $html = $engine->render('blog/article', $data);
} catch (TemplateNotFoundException $e) {
    // Le fichier template n'existe pas
} catch (SyntaxException $e) {
    // Le template contient des erreurs de syntaxe
    echo "Erreur à la ligne " . $e->getLineNumber();
} catch (CircularInheritanceException $e) {
    // Héritage circulaire détecté (ex. : A étend B étend A)
    echo "Chaîne : " . implode(' -> ', $e->getInheritanceChain());
} catch (MacroNotFoundException $e) {
    // Macro non enregistrée
    echo "Macro inconnue : " . $e->getMacroName();
}
```

## Utilisation avancée

### Classes de macros personnalisées

```php
<?php
use Lunar\Template\Macro\MacroInterface;

class UrlMacro implements MacroInterface
{
    public function __construct(private RouterInterface $router) {}

    public function getName(): string
    {
        return 'url';
    }

    public function execute(array $args): string
    {
        $routeName = $args[0] ?? '';
        return $this->router->generateUrl($routeName);
    }
}

$engine->registerMacroInstance(new UrlMacro($router));
```

### Registre de macros

```php
use Lunar\Template\Macro\MacroRegistry;
use Lunar\Template\Macro\DateMacro;

$registry = new MacroRegistry();
$registry
    ->register('greet', fn (string $name) => "Bonjour, $name !")
    ->registerInstance(new DateMacro('Y-m-d', 'Europe/Paris'));

// Vérifier si une macro existe
if ($registry->has('greet')) {
    $result = $registry->call('greet', ['Monde']);
}
```

### Filtres personnalisés

```php
<?php
use Lunar\Template\Filter\FilterInterface;

class HighlightFilter implements FilterInterface
{
    public function getName(): string
    {
        return 'highlight';
    }

    public function apply(mixed $value, array $args = []): string
    {
        $term = $args[0] ?? '';
        return str_replace($term, "<mark>$term</mark>", (string) $value);
    }
}

// Enregistrer avec le renderer
$renderer->registerFilterInstance(new HighlightFilter());

// Ou enregistrer un simple callable
$renderer->registerFilter('double', fn ($value) => $value * 2);
```

**Utilisation dans un template :**
```html
<p>[[ text | highlight("terme recherché") ]]</p>
<p>Total : [[ count | double ]]</p>
```

### Directives personnalisées

```php
use Lunar\Template\Compiler\Directive\DirectiveInterface;

class UppercaseDirective implements DirectiveInterface
{
    public function getName(): string
    {
        return 'uppercase';
    }

    public function compile(string $expression): string
    {
        return '<?php $' . $expression . ' = strtoupper($' . $expression . '); ?>';
    }
}
```

### Gestion du cache

```php
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Cache\CacheWarmer;

// Créer le cache
$cache = new FilesystemCache('/chemin/vers/cache');

// Vérifier si en cache et à jour
if ($cache->has('template-key', filemtime($templateFile))) {
    $compiled = $cache->get('template-key');
}

// Préchauffer tous les templates
$warmer = new CacheWarmer('/chemin/vers/templates', $cache);
$results = $warmer->warmRecursive();

foreach ($results as $template => $success) {
    echo $success ? "✓ $template" : "✗ $template";
}
```

## Sécurité

- **Protection XSS** : toutes les variables sont automatiquement échappées en HTML par défaut
- **Validation des chemins** : les chemins de templates sont validés pour prévenir les attaques de traversée de répertoire
- **Compilation sécurisée** : les templates compilés sont stockés uniquement dans les répertoires de cache désignés
- **Détection d'héritage circulaire** : empêche les boucles infinies dans l'héritage de templates

## Performance

- **Cache intelligent** : les templates sont recompilés uniquement lorsque la source change
- **Préchauffage du cache** : précompilez tous les templates lors du déploiement
- **Compilation optimisée** : analyse efficace basée sur les expressions régulières
- **Mémoire efficace** : empreinte mémoire minimale
- **Compatible OPcache** : fonctionne parfaitement avec PHP OPcache

## Intégration avec les frameworks

### Laravel

```php
// Dans un Service Provider
$this->app->singleton(TemplateRenderer::class, function ($app) {
    $renderer = new TemplateRenderer(
        resource_path('templates'),
        storage_path('framework/cache/templates')
    );

    $renderer->registerMacro('route', fn ($name) => route($name));

    return $renderer;
});
```

### Symfony

```yaml
# services.yaml
services:
    Lunar\Template\Renderer\TemplateRenderer:
        arguments:
            $templatePath: '%kernel.project_dir%/templates'
            $cachePath: '%kernel.cache_dir%/templates'
```

## Prérequis

- **PHP 8.3+**
- **ext-mbstring** (pour la gestion des chaînes)

## Contribution

Les contributions sont les bienvenues ! Veuillez lire notre [Guide de contribution](CONTRIBUTING.md) pour plus de détails.

## Licence

Ce projet est sous licence MIT — voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Journal des modifications

### v1.5.0
- **Commentaires de template** : `[# ... #]` (multi-lignes, jamais émis dans la sortie HTML)
- **Appels de méthode** : `[[ obj.method() ]]` passe par un helper null-safe (plus de `TypeError` sur `null`)
- **Accès hybride array/objet** : `[[ obj.prop ]]` fonctionne sur les tableaux ET les objets (DTO `readonly`) via un helper `Runtime\Access` (issue #14)
- **Filtre `|raw` inline** : `[[ html|raw ]]` court-circuite l'échappement HTML
- **Auto-extension `.tpl`** : `[% extends 'base' %]` résout `base.tpl` automatiquement
- **Tokens autorisés dans `<script>`/`<style>`** : permet l'injection de variables, conditions et macros (y compris dans les attributs `src=`, `href=`)
- **Source maps complètes** : les erreurs sont résolues vers la ligne d'origine du `.tpl`, même après héritage `extends`
- **Linter statique** : `lunar-template template:lint` signale blocs non fermés, fermetures orphelines et tokens malformés sans exécuter les templates
- **Adaptateur PSR-16** : `Cache\Psr16Adapter` wrappe n'importe quel `Psr\SimpleCache\CacheInterface` (Redis, Memcached, APCu, …)
- **Plugin discovery** : les packages tiers peuvent enregistrer macros/filtres via `composer.json` `extra.lunar-template`
- **Cache** : unification de l'interface `CacheInterface` (méthodes `has()`, `getPath()`, `getDirectory()` au contrat)
- **`final readonly class`** : `Psr16Adapter`, `PluginDiscovery`, `ParsedTemplate`, `HtmlEscaper`, `InheritanceResolver`
- **Fix regex** : détection des chaînes dans `convertMacroArgument` corrigée

### v1.4.0
- **Syntaxe de sortie brute** : `[[! contenu !]]` pour affichage sans échappement
- **40+ nouvelles macros** :
  - Utilitaires : `uuid`, `random`, `lorem`, `now`, `dump`, `json`, `pluralize`, `money`, `mask`, `initials`, `color`, `timeago`, `countdown`
  - Sécurité : `csrf`, `nonce`, `honeypot`
  - Formulaires : `input`, `textarea`, `select`, `checkbox`, `radio`, `label`, `hidden`, `method`
  - HTML/Meta : `script`, `style`, `meta`, `og`, `twitter`, `canonical`, `favicon`, `schema`, `breadcrumbs`, `icon`
  - Image/média : `gravatar`, `avatar`, `placeholder`, `qrcode`
  - Intégration : `youtube`, `vimeo`
  - Social : `share` (twitter, facebook, linkedin, email, whatsapp, telegram, reddit, pinterest)
- **DefaultMacros** : enregistrement de toutes les macros intégrées en une fois

### v1.3.0
- **Filtres de mise en forme HTML** : 12 filtres pour la génération HTML
- `markdown`, `linkify`, `list`, `table`, `attributes`, `wrap`
- `highlight`, `paragraph`, `heading`, `anchor`, `excerpt_html`, `class_list`
- **Filtres d'éléments HTML** : 18 nouveaux filtres pour les éléments HTML
- Texte : `div`, `span`, `strong`, `em`, `small`, `code`, `pre`, `blockquote`, `abbr`
- Média : `img`, `video`, `audio`, `iframe`
- UI : `time`, `progress`, `meter`, `badge`, `button`
- **Alias** : `p` (paragraph), `h` (heading), `a` (anchor)

### v1.2.0
- **Système de filtres** : 50+ filtres intégrés avec syntaxe pipe `[[ var | filtre ]]`
- Filtres de chaînes : `upper`, `lower`, `capitalize`, `title`, `trim`, `slug`, `truncate`, `excerpt`, `replace`, `split`
- Filtres numériques : `number_format`, `round`, `floor`, `ceil`, `abs`, `currency`, `percent`, `ordinal`, `filesize`
- Filtres de tableaux : `first`, `last`, `length`, `keys`, `values`, `sort`, `slice`, `merge`, `unique`, `join`, `chunk`, `pluck`, `filter`, `map`, `group_by`, `random`, `shuffle`
- Filtres de dates : `date`, `ago`, `relative`
- Filtres d'encodage : `base64_encode`, `base64_decode`, `url_encode`, `url_decode`, `json_encode`, `json_decode`, `md5`, `sha1`, `sha256`
- Filtres HTML : `raw`, `escape`, `striptags`, `nl2br`, `spaceless`
- Support du chaînage de filtres : `[[ texte | trim | upper | slug ]]`
- Enregistrement de filtres personnalisés via `FilterInterface`
- `FilterRegistry` pour la gestion centralisée des filtres

### v1.1.0
- Architecture modulaire (Parser, Compiler, Renderer)
- Héritage multi-niveaux avec `[% parent %]`
- Directive `[% set %]` pour l'assignation de variables
- Directive `[% include %]` pour l'inclusion de templates
- `MacroRegistry` pour la gestion centralisée des macros
- `DateMacro` pour le formatage de date
- `CacheInterface` et `FilesystemCache`
- `CacheWarmer` pour la précompilation des templates
- `InheritanceResolver` avec détection circulaire
- 100 % de couverture de tests avec PHPStan niveau 7

### v1.0.0
- Version initiale
- Système d'héritage de templates
- Système de blocs
- Système de macros
- Cache intelligent
- Protection XSS
- Conception indépendante des frameworks

---

**Bon templating !**
