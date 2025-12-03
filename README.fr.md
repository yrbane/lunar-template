# Lunar Template Engine

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

[English](README.md)

**Lunar Template Engine** est un moteur de templates autonome et avance pour PHP 8.3+, offrant l'heritage de templates, les blocs, les macros et un cache intelligent.

## Fonctionnalites

- **Heritage de templates** - Etendez vos templates avec `[% extends 'parent.tpl' %]`
- **Systeme de blocs** - Surchargez les blocs parents avec `[% block content %]`
- **Macros** - Composants reutilisables avec `##macroName(args)##`
- **Cache intelligent** - Compilation et mise en cache automatiques
- **Securise** - Protection XSS avec echappement HTML automatique
- **Syntaxe claire** - Syntaxe de template intuitive
- **Autonome** - Aucune dependance, compatible avec tous les frameworks

## Installation

```bash
composer require lunar/template
```

## Demarrage rapide

```php
<?php
use Lunar\Template\AdvancedTemplateEngine;

// Initialiser le moteur
$engine = new AdvancedTemplateEngine(
    templatePath: '/chemin/vers/templates',
    cachePath: '/chemin/vers/cache'
);

// Rendre un template
$html = $engine->render('blog/article', [
    'title' => 'Mon Article',
    'content' => 'Contenu de l\'article...',
    'author' => 'Jean Dupont'
]);

echo $html;
```

## Syntaxe des templates

### Variables

```html
<!-- Variable simple (echappee automatiquement) -->
<h1>[[ title ]]</h1>

<!-- Propriete d'objet -->
<p>Par [[ author.name ]]</p>

<!-- Acces tableau -->
<span>[[ tags.0 ]]</span>

<!-- Sortie brute (NON echappee - a utiliser avec precaution) -->
<div>[[! htmlDeConfiance !]]</div>
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
        <h1>Mon Site Web</h1>
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

### Macros

**Enregistrer une macro :**
```php
$engine->registerMacro('url', function($routeName, $params = []) {
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
<script src="##asset('/js/app.js')##"></script>
```

## Utilisation avancee

### Classes de macros personnalisees

```php
<?php
use Lunar\Template\Macro\MacroInterface;

class UrlMacro implements MacroInterface
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

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

// Enregistrer la macro
$engine->registerMacroInstance(new UrlMacro($router));
```

### Charger des macros depuis un repertoire

```php
$engine->loadMacrosFromDirectory(
    'App\\Template\\Macro',
    '/chemin/vers/macros'
);
```

### Gestion des templates

```php
// Verifier si un template existe
if ($engine->templateExists('blog/article')) {
    $html = $engine->render('blog/article', $data);
}

// Vider le cache
$engine->clearCache(); // Tout vider
$engine->clearCache('blog/article'); // Vider un template specifique

// Obtenir les macros enregistrees
$macros = $engine->getRegisteredMacros();
```

### Gestion des erreurs

```php
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Exception\TemplateNotFoundException;
use Lunar\Template\Exception\SyntaxException;
use Lunar\Template\Exception\CircularInheritanceException;

try {
    $html = $engine->render('blog/article', $data);
} catch (TemplateNotFoundException $e) {
    // Le fichier template n'existe pas
    echo "Template non trouve : " . $e->getTemplatePath();
} catch (SyntaxException $e) {
    // Le template contient des erreurs de syntaxe
    echo "Erreur de syntaxe a la ligne " . $e->getLine() . " : " . $e->getMessage();
} catch (CircularInheritanceException $e) {
    // Heritage circulaire detecte (ex: A etend B etend A)
    echo "Heritage circulaire : " . implode(' -> ', $e->getChain());
} catch (TemplateException $e) {
    // Toute autre erreur de template
    echo "Erreur de template : " . $e->getMessage();
}
```

## Securite

- **Protection XSS** : Toutes les variables sont automatiquement echappees en HTML par defaut
- **Sortie brute optionnelle** : Utilisez la syntaxe `[[! var !]]` uniquement pour du HTML de confiance
- **Validation des chemins** : Les chemins de templates sont valides pour prevenir les attaques de traversee de repertoire
- **Compilation securisee** : Les templates compiles sont stockes uniquement dans les repertoires de cache designes

## Performance

- **Cache intelligent** : Les templates sont recompiles uniquement lorsque la source change
- **Compilation optimisee** : Analyse efficace basee sur les expressions regulieres
- **Memoire efficace** : Empreinte memoire minimale
- **Compatible OPcache** : Fonctionne parfaitement avec PHP OPcache

## Configuration

### Variables par defaut personnalisees

```php
class CustomTemplateEngine extends AdvancedTemplateEngine
{
    protected function setDefaultVariables(): void
    {
        parent::setDefaultVariables();

        // Ajouter vos valeurs par defaut
        if (!isset($siteName)) $siteName = 'Mon Site Web';
        if (!isset($currentYear)) $currentYear = date('Y');
    }
}
```

## Prerequis

- **PHP 8.3+**
- **ext-mbstring** (pour la gestion des chaines)

## Integration avec les frameworks

### Laravel

```php
// Dans un Service Provider
$this->app->singleton(AdvancedTemplateEngine::class, function ($app) {
    return new AdvancedTemplateEngine(
        resource_path('templates'),
        storage_path('framework/cache/templates')
    );
});
```

### Symfony

```yaml
# services.yaml
services:
    Lunar\Template\AdvancedTemplateEngine:
        arguments:
            $templatePath: '%kernel.project_dir%/templates'
            $cachePath: '%kernel.cache_dir%/templates'
```

## Contribution

Les contributions sont les bienvenues ! Veuillez lire notre [Guide de contribution](CONTRIBUTING.md) pour plus de details.

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de details.

## Journal des modifications

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
