# Lunar Template Engine

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen.svg)]()
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg)]()

[English](README.md)

**Lunar Template Engine** est un moteur de templates autonome et avance pour PHP 8.3+, offrant l'heritage de templates, les blocs, les macros et un cache intelligent.

## Fonctionnalites

- **Heritage de templates** - Etendez vos templates avec `[% extends 'parent.tpl' %]`
- **Heritage multi-niveaux** - Enchainez plusieurs niveaux de templates avec support de `[% parent %]`
- **Systeme de blocs** - Surchargez les blocs parents avec `[% block content %]`
- **Macros** - Composants reutilisables avec `##macroName(args)##`
- **Directives** - `[% set %]` et `[% include %]` pour un controle avance
- **Cache intelligent** - Compilation et mise en cache automatiques avec prechauffage
- **Securise** - Protection XSS avec echappement HTML automatique et validation des chemins
- **Syntaxe claire** - Syntaxe de template intuitive
- **Architecture modulaire** - Composants Parser, Compiler, Renderer et Cache
- **Autonome** - Aucune dependance, compatible avec tous les frameworks
- **100% de couverture de tests** - Entierement teste avec PHPStan niveau 7

## Installation

```bash
composer require lunar/template
```

## Demarrage rapide

### Utilisation de AdvancedTemplateEngine (Tout-en-un)

```php
<?php
use Lunar\Template\AdvancedTemplateEngine;

$engine = new AdvancedTemplateEngine(
    templatePath: '/chemin/vers/templates',
    cachePath: '/chemin/vers/cache'
);

$html = $engine->render('blog/article', [
    'title' => 'Mon Article',
    'content' => 'Contenu de l\'article...',
    'author' => 'Jean Dupont'
]);

echo $html;
```

### Utilisation des composants modulaires

```php
<?php
use Lunar\Template\Renderer\TemplateRenderer;
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Cache\CacheWarmer;

// Creer le renderer avec cache
$renderer = new TemplateRenderer(
    templatePath: '/chemin/vers/templates',
    cachePath: '/chemin/vers/cache'
);

// Definir les variables par defaut
$renderer->setDefaultVariables([
    'siteName' => 'Mon Site Web',
    'currentYear' => date('Y')
]);

// Enregistrer des macros
$renderer->registerMacro('uppercase', fn(string $text) => strtoupper($text));

// Rendre un template
$html = $renderer->render('page', ['title' => 'Accueil']);

// Prechauffer le cache
$cache = new FilesystemCache('/chemin/vers/cache');
$warmer = new CacheWarmer('/chemin/vers/templates', $cache);
$warmer->warmRecursive(); // Precompiler tous les templates
```

## Syntaxe des templates

### Variables

```html
<!-- Variable simple (echappee automatiquement) -->
<h1>[[ title ]]</h1>

<!-- Propriete d'objet / notation pointee -->
<p>Par [[ author.name ]]</p>

<!-- Acces tableau -->
<span>[[ tags.0 ]]</span>

<!-- Acces imbrique -->
<p>[[ user.profile.email ]]</p>
```

### Conditions

```html
[% if user.isLoggedIn %]
    <p>Bienvenue, [[ user.name ]] !</p>
[% elseif user.isGuest %]
    <p>Bonjour, invite !</p>
[% else %]
    <p>Veuillez vous connecter.</p>
[% endif %]

<!-- Avec operateurs de comparaison -->
[% if count > 0 %]
    <p>Vous avez [[ count ]] elements.</p>
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
[% set pageTitle = "Ma Page" %]
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

### Heritage de templates

**Template de base (`base.html.tpl`) :**
```html
<!DOCTYPE html>
<html>
<head>
    <title>[% block title %]Titre par defaut[% endblock %]</title>
</head>
<body>
    <header>
        [% block header %]
            <h1>Mon Site Web</h1>
        [% endblock %]
    </header>

    <main>
        [% block content %]
            Contenu par defaut
        [% endblock %]
    </main>

    <footer>
        [% block footer %]
            <p>&copy; 2025 Mon Site Web</p>
        [% endblock %]
    </footer>
</body>
</html>
```

**Template enfant (`article.html.tpl`) :**
```html
[% extends 'base.html.tpl' %]

[% block title %][[ article.title ]] - Mon Blog[% endblock %]

[% block content %]
    <article>
        <h1>[[ article.title ]]</h1>
        <time>[[ article.publishedAt ]]</time>
        <div>[[ article.content ]]</div>
    </article>
[% endblock %]
```

### Heritage multi-niveaux avec bloc parent

```html
<!-- grandparent.tpl -->
<div class="container">
    [% block content %]Contenu de base[% endblock %]
</div>

<!-- parent.tpl -->
[% extends 'grandparent.tpl' %]
[% block content %]
    <div class="wrapper">
        [% parent %]  <!-- Inclut "Contenu de base" -->
    </div>
[% endblock %]

<!-- child.tpl -->
[% extends 'parent.tpl' %]
[% block content %]
    <h1>Titre</h1>
    [% parent %]  <!-- Inclut le contenu parent enveloppe -->
[% endblock %]
```

### Macros

**Enregistrer une macro :**
```php
$engine->registerMacro('url', function(string $routeName, array $params = []) {
    return "/route/{$routeName}?" . http_build_query($params);
});
```

**Utilisation dans un template :**
```html
<a href="##url('blog.show', ['id' => article.id])##">Lire la suite</a>
```

**Macros integrees :**
```html
<!-- URLs des assets -->
<link rel="stylesheet" href="##asset('/css/app.css')##">

<!-- Formatage de date -->
<time>##date('j F Y')##</time>
<time>##date('Y-m-d', article.publishedAt)##</time>
```

## Architecture

### Composants

| Composant | Description |
|-----------|-------------|
| `TemplateParser` | Analyse le source du template en composants structures |
| `TemplateCompiler` | Compile la syntaxe du template en code PHP |
| `TemplateRenderer` | Rend les templates avec injection de variables |
| `InheritanceResolver` | Gere l'heritage multi-niveaux des templates |
| `FilesystemCache` | Cache de templates base sur le systeme de fichiers |
| `CacheWarmer` | Precompile les templates pour la production |
| `MacroRegistry` | Gestion centralisee des macros |
| `PathValidator` | Securite : empeche la traversee de repertoire |
| `HtmlEscaper` | Securite : protection XSS |

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
    echo "Erreur a la ligne " . $e->getLineNumber();
} catch (CircularInheritanceException $e) {
    // Heritage circulaire detecte (ex: A etend B etend A)
    echo "Chaine : " . implode(' -> ', $e->getInheritanceChain());
} catch (MacroNotFoundException $e) {
    // Macro non enregistree
    echo "Macro inconnue : " . $e->getMacroName();
}
```

## Utilisation avancee

### Classes de macros personnalisees

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
    ->register('greet', fn(string $name) => "Bonjour, $name!")
    ->registerInstance(new DateMacro('Y-m-d', 'Europe/Paris'));

// Verifier si une macro existe
if ($registry->has('greet')) {
    $result = $registry->call('greet', ['Monde']);
}
```

### Directives personnalisees

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

// Creer le cache
$cache = new FilesystemCache('/chemin/vers/cache');

// Verifier si en cache et a jour
if ($cache->has('template-key', filemtime($templateFile))) {
    $compiled = $cache->get('template-key');
}

// Prechauffer tous les templates
$warmer = new CacheWarmer('/chemin/vers/templates', $cache);
$results = $warmer->warmRecursive();

foreach ($results as $template => $success) {
    echo $success ? "✓ $template" : "✗ $template";
}
```

## Securite

- **Protection XSS** : Toutes les variables sont automatiquement echappees en HTML par defaut
- **Validation des chemins** : Les chemins de templates sont valides pour prevenir les attaques de traversee de repertoire
- **Compilation securisee** : Les templates compiles sont stockes uniquement dans les repertoires de cache designes
- **Detection d'heritage circulaire** : Empeche les boucles infinies dans l'heritage de templates

## Performance

- **Cache intelligent** : Les templates sont recompiles uniquement lorsque la source change
- **Prechauffage du cache** : Precompilez tous les templates lors du deploiement
- **Compilation optimisee** : Analyse efficace basee sur les expressions regulieres
- **Memoire efficace** : Empreinte memoire minimale
- **Compatible OPcache** : Fonctionne parfaitement avec PHP OPcache

## Integration avec les frameworks

### Laravel

```php
// Dans un Service Provider
$this->app->singleton(TemplateRenderer::class, function ($app) {
    $renderer = new TemplateRenderer(
        resource_path('templates'),
        storage_path('framework/cache/templates')
    );

    $renderer->registerMacro('route', fn($name) => route($name));

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

## Prerequis

- **PHP 8.3+**
- **ext-mbstring** (pour la gestion des chaines)

## Contribution

Les contributions sont les bienvenues ! Veuillez lire notre [Guide de contribution](CONTRIBUTING.md) pour plus de details.

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de details.

## Journal des modifications

### v1.1.0
- Architecture modulaire (Parser, Compiler, Renderer)
- Heritage multi-niveaux avec `[% parent %]`
- Directive `[% set %]` pour l'assignation de variables
- Directive `[% include %]` pour l'inclusion de templates
- `MacroRegistry` pour la gestion centralisee des macros
- `DateMacro` pour le formatage de date
- `CacheInterface` et `FilesystemCache`
- `CacheWarmer` pour la precompilation des templates
- `InheritanceResolver` avec detection circulaire
- 100% de couverture de tests avec PHPStan niveau 7

### v1.0.0
- Version initiale
- Systeme d'heritage de templates
- Systeme de blocs
- Systeme de macros
- Cache intelligent
- Protection XSS
- Conception independante des frameworks

---

**Bon templating !**
