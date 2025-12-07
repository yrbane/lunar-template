<?php

/**
 * @since 0.0.1
 * @link https://nethttp.net
 *
 * @Author seb@nethttp.net
 */
declare(strict_types=1);

namespace Lunar\Template;

use Exception;
use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Macro\MacroInterface;
use ReflectionClass;
use Throwable;

/**
 * Class AdvancedTemplateEngine.
 *
 * Un moteur de template avancé qui supporte :
 * - Les variables avec la syntaxe [[ ... ]].
 * - Les conditions avec [% if ... %], [% elseif ... %], [% else %], [% endif %].
 * - Les boucles avec [% for variable in array %] et [% endfor %].
 * - L'héritage et les blocs via [% extends 'parent.tpl' %], [% block blockName %] ... [% endblock %].
 * - Les macros avec la syntaxe ##macroName(arg1, arg2)##, par exemple pour générer des URLs.
 *
 * Les templates sources sont attendus dans un dossier (ex : template/) au format .tpl.
 * Les templates compilés seront stockés dans un dossier de cache.
 */
class AdvancedTemplateEngine
{
    /** @var string Chemin absolu vers le dossier des templates */
    protected string $templatePath;

    /** @var string Chemin absolu vers le dossier de cache des templates compilés */
    protected string $cachePath;

    /** @var array<string, callable> Liste des macros enregistrées */
    protected array $macros = [];

    /** @var bool Si true, une exception est levée pour les variables indéfinies ou null. */
    protected bool $strictVariables = false;

    /**
     * AdvancedTemplateEngine constructor.
     *
     * @param string $templatePath répertoire où se trouvent les templates source
     * @param string $cachePath répertoire où seront stockés les templates compilés
     */
    public function __construct(string $templatePath, string $cachePath)
    {
        // Normalisation des chemins
        $this->templatePath = $this->normalizePath($templatePath);
        $this->cachePath = $this->normalizePath($cachePath);

        // Vérification et création des répertoires
        $this->ensureDirectoryExists($this->templatePath, 'Template directory');
        $this->ensureDirectoryExists($this->cachePath, 'Cache directory', true);
    }

    /**
     * Normalise un chemin de fichier.
     *
     * @param string $path
     *
     * @return string
     */
    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    /**
     * S'assure qu'un répertoire existe.
     *
     * @param string $path
     * @param string $description
     * @param bool $create
     *
     * @throws Exception
     */
    private function ensureDirectoryExists(string $path, string $description, bool $create = false): void
    {
        if (!is_dir($path)) {
            if ($create) {
                if (!mkdir($path, 0o755, true) && !is_dir($path)) {
                    // @codeCoverageIgnoreStart
                    throw TemplateException::unableToCreateCacheDirectory($path);
                    // @codeCoverageIgnoreEnd
                }
            } else {
                throw TemplateException::directoryNotFound($path);
            }
        }

        if (!is_readable($path)) {
            throw TemplateException::directoryNotReadable($path);
        }

        if ($create && !is_writable($path)) {
            throw TemplateException::directoryNotWritable($path);
        }
    }

    /**
     * Rendu d'un template avec injection de variables.
     *
     * @param string $template Nom du template (sans extension, fichier attendu en .tpl)
     * @param array<string, mixed> $variables variables à injecter dans le template
     *
     * @throws Exception si le template source n'existe pas
     *
     * @return string le contenu HTML généré
     */
    public function render(string $template, array $variables = []): string
    {
        // Construit le chemin complet du fichier template
        $templateFile = $this->templatePath . '/' . $template . '.tpl';
        if (!file_exists($templateFile)) {
            throw TemplateException::templateNotFound($templateFile);
        }

        $compiledFile = $this->cachePath . '/' . md5($templateFile) . '.php';

        // Vérification de la nécessité de compiler
        $needsCompilation = false;

        if (!file_exists($compiledFile)) {
            $needsCompilation = true;
        } elseif (filemtime($compiledFile) < filemtime($templateFile)) {
            $needsCompilation = true;
        } else {
            // Vérification des dépendances (parents)
            $handle = fopen($compiledFile, 'r');
            if ($handle) {
                $line = fgets($handle);
                fclose($handle);

                if ($line !== false && str_starts_with($line, '<?php /* DEPENDENCIES: ')) {
                    $depsString = substr($line, 23, strpos($line, ' */') - 23);
                    $dependencies = explode(';', $depsString);

                    foreach ($dependencies as $dep) {
                        if ($dep !== '' && file_exists($dep) && filemtime($compiledFile) < filemtime($dep)) {
                            $needsCompilation = true;
                            break;
                        }
                    }
                }
            }
        }

        // Si le template compilé n'existe pas ou est périmé, le recompiler.
        if ($needsCompilation) {
            $source = file_get_contents($templateFile);
            if ($source === false) {
                // @codeCoverageIgnoreStart
                throw TemplateException::unableToReadTemplate($templateFile);
                // @codeCoverageIgnoreEnd
            }
            $dependencies = [];
            $compiled = $this->compileTemplate($source, $dependencies, $templateFile);
            
            $header = '';
            if (!empty($dependencies)) {
                $header = '<?php /* DEPENDENCIES: ' . implode(';', $dependencies) . ' */ ?>' . PHP_EOL;
            }
            
            file_put_contents($compiledFile, $header . $compiled);
        }

        // Merge avec les variables par defaut
        $variables = array_merge($this->getDefaultVariables(), $variables);

        extract($variables, EXTR_OVERWRITE);

        // Variable pour accéder au moteur dans le contexte du template
        $engine = $this;

        ob_start();

        try {
            include $compiledFile;
        } catch (Throwable $e) {
            ob_end_clean();

            // Debug: Inspect the caught exception
            file_put_contents('/tmp/debug_lunar_exception.txt', sprintf("Exception: %s (%s) in %s:%d\n", $e->getMessage(), get_class($e), $e->getFile(), $e->getLine()), FILE_APPEND);

            // Source Map Logic
            $originalLine = null;
            $originalFile = $templateFile; // Default to the main template file
            
            // Si l'erreur provient du fichier compilé
            if ($e->getFile() === $compiledFile) {
                $compiledFileContent = file($compiledFile); // Lire le fichier compilé ligne par ligne
                if ($compiledFileContent !== false) {
                    $errorLineInCompiled = $e->getLine();
                    // Remonter pour trouver le dernier marqueur #LUNAR_LINE avant la ligne d'erreur
                    for ($i = $errorLineInCompiled - 1; $i >= 0; $i--) {
                        if (isset($compiledFileContent[$i]) && preg_match('#/\* LUNAR_LINE:(\d+):(.*?) \*/#', $compiledFileContent[$i], $matches)) {
                            $originalLine = (int) $matches[1];
                            $originalFile = $matches[2];
                            break;
                        }
                    }
                }
            }

            $errorMessage = sprintf(
                'Error in template "%s" at line %s: %s',
                basename($originalFile), // Nom de fichier simple
                $originalLine ?? $e->getLine(), // Utiliser la ligne originale si trouvée
                $e->getMessage()
            );

            throw new TemplateException($errorMessage, 0, $e);
        }

        return (string) ob_get_clean();
    }

    /**
     * Enregistre une macro réutilisable dans les templates.
     *
     * @param string $name nom de la macro
     * @param callable $callback fonction à appeler pour générer le contenu
     */
    public function registerMacro(string $name, callable $callback): void
    {
        // Stocke le callback dans le tableau des macros
        // Le moteur l'exécutera dynamiquement quand il rencontre ##macroName()##
        $this->macros[$name] = $callback;
    }

    /**
     * Enregistre une macro via une instance qui implémente MacroInterface.
     *
     * @param MacroInterface $macro instance de la macro
     */
    public function registerMacroInstance(MacroInterface $macro): void
    {
        // Le tableau [$macro, 'execute'] est un callable valide SI la méthode est publique
        $this->registerMacro($macro->getName(), [$macro, 'execute']);
    }

    /**
     * Appelle une macro enregistrée.
     *
     * @param string $name nom de la macro
     * @param array<int, mixed> $args arguments passés à la macro
     *
     * @throws Exception si la macro n'est pas définie
     *
     * @return mixed résultat renvoyé par la macro
     */
    public function callMacro(string $name, array $args): mixed
    {
        if (!isset($this->macros[$name])) {
            throw TemplateException::macroNotFound($name);
        }

        $callback = $this->macros[$name];

        // Si c'est un callable de type [object, 'execute'] (MacroInterface),
        // on passe le tableau d'arguments directement
        if (\is_array($callback) && isset($callback[0]) && $callback[0] instanceof MacroInterface) {
            return $callback[0]->execute($args);
        }

        // Pour les closures, on spread les arguments
        return $callback(...$args);
    }

    /**
     * Compile le template source en code PHP.
     *
     * @param string $source contenu du template source
     * @param array<string> $dependencies liste des dépendances (fichiers parents)
     * @param string $templateFilePath Chemin absolu du template original (pour la source map)
     *
     * @return string code PHP généré
     */
    protected function compileTemplate(string $source, array &$dependencies = [], string $templateFilePath = ''): string
    {
        // Traitement de l'héritage (extends et blocs)
        $source = $this->processExtends($source, $dependencies);

        $sourceWithLineMarkers = [];
        $sourceLines = explode("\n", $source);
        foreach ($sourceLines as $lineNum => $originalLine) {
            // Ajouter un marqueur de source map au début de chaque ligne compilée
            // Le marqueur inclut le numéro de ligne original et le chemin du fichier pour le débogage
            $sourceWithLineMarkers[] = '/* LUNAR_LINE:' . ($lineNum + 1) . ':' . $templateFilePath . ' */' . $originalLine;
        }
        $source = implode("\n", $sourceWithLineMarkers);

        // Conversion des variables [[ ... ]] en affichage PHP sécurisé.
        $source = preg_replace_callback('/\[\[\s*(.*?)\s*\]\]/', function ($matches) {
            $expression = trim($matches[1]);

            // Si l'expression est vide, retourner une chaîne vide
            if ('' === $expression) {
                return '';
            }

            // Convertit la notation point en acces tableau/objet PHP
            $phpVar = $this->convertDotNotation($expression);
            
            // Injecter la logique du mode strict directement dans le code PHP généré
            $phpCode = '<?php ';
            $phpCode .= 'if ($engine->isStrictMode()) { ';
            // Vérifier si la variable finale est UNDEFINED
            $phpCode .= '    if (!isset(' . $phpVar . ')) { ';
            $phpCode .= '        throw new Lunar\\Template\\Exception\\TemplateException(sprintf("Undefined variable \\"%s\\" in strict mode.", \'' . $expression . '\')); ';
            $phpCode .= '    } ';
            
            // Vérifier si la variable finale est NULL
            $phpCode .= '    if (' . $phpVar . ' === null) { ';
            $phpCode .= '        throw new Lunar\\Template\\Exception\\TemplateException(sprintf("Variable \\"%s\\" is null in strict mode.", \'' . $expression . '\')); ';
            $phpCode .= '    } ';
            $phpCode .= '    echo htmlspecialchars((string)' . $phpVar . ', ENT_QUOTES, \'UTF-8\'); ';
            $phpCode .= '} else { '; // Else du if ($engine->isStrictMode())
            // Comportement par défaut (non strict): afficher une chaîne vide si indéfinie ou null.
            $phpCode .= '    echo htmlspecialchars((string)(' . $phpVar . ' ?? \'\'), ENT_QUOTES, \'UTF-8\'); ';
            $phpCode .= '} ?>'; // Fermeture du bloc PHP
            
            return $phpCode;
        }, $source);

        // Traitement des conditions.
        $source = preg_replace_callback('/\[%\s*if\s+(.*?)\s*%\]/', function ($matches) {
            $condition = $this->processCondition($matches[1]);

            return '<?php if (' . $condition . '): ?>';
        }, $source);
        $source = preg_replace_callback('/\[%\s*elseif\s+(.*?)\s*%\]/', function ($matches) {
            $condition = $this->processCondition($matches[1]);

            return '<?php elseif (' . $condition . '): ?>';
        }, $source);
        $source = preg_replace('/\[%\s*else\s*%\]/', '<?php else: ?>', $source);
        $source = preg_replace('/\[%\s*endif\s*%\]/', '<?php endif; ?>', $source);

        // Traitement des boucles.
        $source = preg_replace_callback('/\[%\s*for\s+(\S+)\s+in\s+(\S+)\s*%\]/', function ($matches) {
            $variable = ltrim($matches[1], '$');
            $array = $this->addDollarToVariables($matches[2]);

            return '<?php foreach((' . $array . ' ?? []) as $' . $variable . '): ?>';
        }, $source);
        $source = preg_replace('/\[%\s*endfor\s*%\]/', '<?php endforeach; ?>', $source);

        // Traitement des macros avec la syntaxe ##macroName(arg1, arg2)##.
        $source = preg_replace_callback('/##(\w+)\((.*?)\)##/', function ($matches) {
            $macroName = $matches[1];
            $args = $matches[2];

            // Parse les arguments pour créer un tableau PHP
            $parsedArgs = $this->parseMacroArguments($args);

            return '<?= $this->callMacro(\'' . $macroName . '\', ' . $parsedArgs . ') ?>';
        }, $source);

        // Nettoyage des éventuelles balises de blocs non remplacées
        $source = preg_replace('/\[%\s*block\s+\S+\s*%\]/', '', $source);

        return (string) preg_replace('/\[%\s*endblock\s*%\]/', '', $source);
    }

    /**
     * Gère l'héritage de templates.
     *
     * @param string $source contenu du template enfant
     * @param array<string> $dependencies liste des dépendances (fichiers parents)
     *
     * @throws Exception si le template parent n'existe pas
     *
     * @return string contenu final après fusion avec le template parent
     */
    protected function processExtends(string $source, array &$dependencies = []): string
    {
        if (preg_match('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', $source, $matches)) {
            $parentTemplate = $matches[1];
            $source = preg_replace('/\[%\s*extends\s+[\'"](.+?)[\'"]\s*%\]/', '', $source);
            $blocks = $this->extractBlocks($source);
            $parentFile = "{$this->templatePath}/{$parentTemplate}";
            if (!file_exists($parentFile)) {
                throw TemplateException::parentTemplateNotFound($parentFile);
            }

            if (!in_array($parentFile, $dependencies, true)) {
                $dependencies[] = $parentFile;
            }

            $parentSource = file_get_contents($parentFile);
            if ($parentSource === false) {
                // @codeCoverageIgnoreStart
                throw TemplateException::unableToReadTemplate($parentFile);
                // @codeCoverageIgnoreEnd
            }

            $parentSource = $this->processExtends($parentSource, $dependencies);

            return preg_replace_callback('/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s', function ($matches) use ($blocks) {
                $blockName = $matches[1];

                return $blocks[$blockName] ?? $matches[2];
            }, $parentSource);
        }

        return $source;
    }

    /**
     * Extrait les blocs du template.
     *
     * @param string $source contenu du template
     *
     * @return array<string, string> tableau associatif blockName => contenu
     */
    protected function extractBlocks(string $source): array
    {
        $blocks = [];
        if (preg_match_all('/\[%\s*block\s+(\w+)\s*%\](.*?)\[%\s*endblock\s*%\]/s', $source, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $blocks[$match[1]] = $match[2];
            }
        }

        return $blocks;
    }

    /**
     * Parse les arguments d'une macro en tableau PHP.
     *
     * @param string $args Arguments de la macro
     *
     * @return string Code PHP pour le tableau d'arguments
     */
    protected function parseMacroArguments(string $args): string
    {
        if (empty(trim($args))) {
            return '[]';
        }

        // Sépare les arguments par les virgules (en tenant compte des chaînes)
        $arguments = [];
        $current = '';
        $inQuotes = false;
        $quoteChar = '';

        for ($i = 0; $i < \strlen($args); $i++) {
            $char = $args[$i];

            if (!$inQuotes && ($char === '"' || $char === "'")) {
                $inQuotes = true;
                $quoteChar = $char;
                $current .= $char;
            } elseif ($inQuotes && $char === $quoteChar) {
                $inQuotes = false;
                $current .= $char;
            } elseif (!$inQuotes && $char === ',') {
                $arguments[] = $this->convertMacroArgument(trim($current));
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (!empty(trim($current))) {
            $arguments[] = $this->convertMacroArgument(trim($current));
        }

        return '[' . implode(', ', $arguments) . ']';
    }

    /**
     * Convertit un argument de macro en expression PHP valide.
     *
     * @param string $arg Argument de macro
     *
     * @return string Expression PHP
     */
    protected function convertMacroArgument(string $arg): string
    {
        // Si c'est une chaine entre guillemets, la garder telle quelle
        if (preg_match('/^(["\']).*\1$/', $arg)) {
            return $arg;
        }

        // Si c'est un nombre, le garder tel quel
        if (is_numeric($arg)) {
            return $arg;
        }

        // Si c'est un mot-cle PHP, le garder tel quel
        $phpKeywords = ['true', 'false', 'null'];
        if (\in_array(strtolower($arg), $phpKeywords, true)) {
            return $arg;
        }

        // Sinon, c'est une variable - convertir la notation point
        return $this->convertDotNotation($arg);
    }

    /**
     * Convertit la notation point en acces tableau PHP.
     *
     * Exemple: "user.profile.name" devient "$user['profile']['name']"
     *
     * @param string $expression Expression avec notation point
     *
     * @return string Expression PHP valide
     */
    protected function convertDotNotation(string $expression): string
    {
        // Si deja prefixe par $, retirer le $ pour traitement uniforme
        if (str_starts_with($expression, '$')) {
            $expression = substr($expression, 1);
        }

        // Separe par le point
        $parts = explode('.', $expression);

        if (\count($parts) === 1) {
            // Pas de notation point, simple variable
            return '$' . $parts[0];
        }

        // Premier element est la variable racine
        $result = '$' . array_shift($parts);

        // Les autres elements deviennent des acces tableau
        foreach ($parts as $part) {
            // Gere les index numeriques et les cles string
            if (ctype_digit($part)) {
                $result .= '[' . $part . ']';
            } else {
                $result .= '[\'' . $part . '\']';
            }
        }

        return $result;
    }

    /**
     * Process a condition expression for safe PHP compilation.
     *
     * Handles simple variable checks (like "if user") by wrapping them with !empty()
     * to avoid undefined variable warnings. Complex conditions with operators are
     * passed through with dollar sign prefixes added.
     *
     * @param string $condition The raw condition from the template
     *
     * @return string Safe PHP condition expression
     */
    protected function processCondition(string $condition): string
    {
        $condition = trim($condition);

        // Check if it's a simple variable check (no operators)
        // Simple pattern: just a variable name with optional dot notation
        if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $condition)) {
            $phpVar = $this->convertDotNotation($condition);

            return '!empty(' . $phpVar . ')';
        }

        // For complex conditions, add dollar signs and let PHP handle it
        return $this->addDollarToVariables($condition);
    }

    /**
     * Ajoute le préfixe $ aux variables dans les expressions PHP.
     *
     * @param string $expression Expression PHP
     *
     * @return string Expression avec variables préfixées
     */
    protected function addDollarToVariables(string $expression): string
    {
        // D'abord, extraire et proteger les chaines de caracteres
        $strings = [];
        $placeholder = '___STRING_PLACEHOLDER_%d___';
        $index = 0;

        // Protege les chaines entre guillemets doubles et simples
        $expression = preg_replace_callback('/(["\'])(?:(?!\1)[^\\\\]|\\\\.)*\1/', function ($match) use (&$strings, &$index, $placeholder) {
            $key = \sprintf($placeholder, $index++);
            $strings[$key] = $match[0];

            return $key;
        }, $expression);

        // Remplace les identifiants par des variables
        $expression = preg_replace_callback('/\b([a-zA-Z_][a-zA-Z0-9_]*(?:\.[a-zA-Z_][a-zA-Z0-9_]*)*)\b/', function ($matches) {
            $var = $matches[1];

            // Ne pas modifier les mots-clés PHP, constantes, ou appels de fonctions
            $phpKeywords = ['true', 'false', 'null', 'and', 'or', 'not', 'isset', 'empty', 'array', 'count'];
            if (\in_array(strtolower($var), $phpKeywords, true)) {
                return $var;
            }

            // Ne pas modifier si c'est déjà une variable (défense en profondeur)
            // @codeCoverageIgnoreStart
            if (str_starts_with($var, '$')) {
                return $var;
            }
            // @codeCoverageIgnoreEnd

            // Ne pas modifier les placeholders
            if (str_contains($var, '___STRING_PLACEHOLDER_')) {
                return $var;
            }

            // Convertit la notation point
            return $this->convertDotNotation($var);
        }, $expression);

        // Restaure les chaines de caracteres
        foreach ($strings as $key => $value) {
            $expression = str_replace($key, $value, $expression);
        }

        return $expression;
    }

    /**
     * Retourne les variables par défaut pour les templates.
     * Cette méthode peut être surchargée pour personnaliser les variables par défaut.
     *
     * @return array<string, mixed>
     */
    protected function getDefaultVariables(): array
    {
        return [
            'title' => '',
            'description' => '',
            'keywords' => '',
            'author' => '',
            'charset' => 'UTF-8',
            'viewport' => 'width=device-width, initial-scale=1.0',
            'lang' => 'en',
            'favicon' => '/favicon.ico',
            'baseUrl' => '/',
            'basePath' => '/',
        ];
    }

    /**
     * Charge toutes les macros d'un répertoire.
     *
     * @param string $namespace Namespace des classes de macros
     * @param string $directory Répertoire contenant les fichiers de macros
     */
    public function loadMacrosFromDirectory(string $namespace, string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*.php');
        // @codeCoverageIgnoreStart
        if ($files === false) {
            return;
        }
        // @codeCoverageIgnoreEnd

        foreach ($files as $file) {
            require_once $file;

            $className = $namespace . '\\' . pathinfo($file, PATHINFO_FILENAME);

            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);

                if ($reflection->implementsInterface(MacroInterface::class) && $reflection->isInstantiable()) {
                    $constructor = $reflection->getConstructor();

                    if ($constructor === null || $constructor->getNumberOfRequiredParameters() === 0) {
                        /** @var MacroInterface $instance */
                        $instance = new $className();
                        $this->registerMacroInstance($instance);
                    }
                }
            }
        }
    }

    /**
     * Vérifie si un template existe.
     *
     * @param string $template Nom du template
     *
     * @return bool
     */
    public function templateExists(string $template): bool
    {
        $templateFile = $this->templatePath . '/' . $template . '.tpl';

        return file_exists($templateFile);
    }

    /**
     * Vide le cache des templates compilés.
     *
     * @param string|null $template Template spécifique à vider (optionnel)
     */
    public function clearCache(?string $template = null): void
    {
        if ($template !== null) {
            $templateFile = $this->templatePath . '/' . $template . '.tpl';
            $compiledFile = $this->cachePath . '/' . md5($templateFile) . '.php';

            if (file_exists($compiledFile)) {
                unlink($compiledFile);
            }
        } else {
            $files = glob($this->cachePath . '/*.php');
            if ($files !== false) {
                foreach ($files as $file) {
                    unlink($file);
                }
            }
        }
    }

    /**
     * Retourne la liste des macros enregistrées.
     *
     * @return array<string, callable>
     */
    public function getRegisteredMacros(): array
    {
        return $this->macros;
    }

    /**
     * Définit si le moteur doit être en mode strict pour les variables.
     * En mode strict, une exception est levée pour les variables indéfinies ou null.
     *
     * @param bool $strict
     */
    public function setStrictVariables(bool $strict): void
    {
        $this->strictVariables = $strict;
    }

    /**
     * Retourne si le moteur est en mode strict.
     */
    public function isStrictMode(): bool
    {
        return $this->strictVariables;
    }
}
