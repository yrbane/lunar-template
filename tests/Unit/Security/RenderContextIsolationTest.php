<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Security;

use Lunar\Template\AdvancedTemplateEngine;
use Lunar\Template\Cache\FilesystemCache;
use Lunar\Template\Renderer\TemplateRenderer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Vérifie l'isolation du contexte de rendu : les variables fournies par
 * l'utilisateur ne doivent jamais pouvoir écraser les variables internes
 * du moteur (notamment $compiledFile / $__lunar_*) — sous peine
 * d'autoriser une inclusion de fichier arbitraire (RCE).
 */
class RenderContextIsolationTest extends TestCase
{
    private string $templatesDir;

    private string $cacheDir;

    private string $maliciousFile;

    protected function setUp(): void
    {
        $this->templatesDir = sys_get_temp_dir() . '/lunar-iso-tpl-' . uniqid();
        $this->cacheDir = sys_get_temp_dir() . '/lunar-iso-cache-' . uniqid();
        mkdir($this->templatesDir, 0o755, true);
        mkdir($this->cacheDir, 0o755, true);

        // Fichier "attaquant" qui imite un template compilé : si l'include
        // est détourné, son contenu sera émis dans la sortie.
        $this->maliciousFile = sys_get_temp_dir() . '/lunar-iso-evil-' . uniqid() . '.php';
        file_put_contents($this->maliciousFile, '<?php echo "PWNED"; ?>');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->templatesDir);
        $this->removeDirectory($this->cacheDir);
        if (file_exists($this->maliciousFile)) {
            unlink($this->maliciousFile);
        }
    }

    public function testAdvancedEngineIgnoresCompiledFileVariable(): void
    {
        file_put_contents($this->templatesDir . '/safe.tpl', 'Hello [[ name ]]');

        $engine = new AdvancedTemplateEngine(
            $this->templatesDir,
            $this->cacheDir,
            new FilesystemCache($this->cacheDir),
        );

        $output = $engine->render('safe', [
            'name' => 'World',
            'compiledFile' => $this->maliciousFile,
            '__lunar_compiled_file' => $this->maliciousFile,
        ]);

        $this->assertStringNotContainsString('PWNED', $output);
        $this->assertStringContainsString('Hello World', $output);
    }

    public function testTemplateRendererIgnoresCompiledFileVariable(): void
    {
        // TemplateRenderer attend un fichier déjà compilé : on en fabrique un
        // bénin et on tente de détourner l'include via la variable utilisateur.
        $compiled = $this->cacheDir . '/safe.compiled.php';
        file_put_contents($compiled, '<?php echo "Hello " . htmlspecialchars($name, ENT_QUOTES, "UTF-8"); ?>');

        $renderer = new TemplateRenderer($this->templatesDir, $this->cacheDir);

        $reflection = new ReflectionClass($renderer);
        $execute = $reflection->getMethod('executeTemplate');

        $output = $execute->invoke($renderer, $compiled, [
            'name' => 'World',
            'compiledFile' => $this->maliciousFile,
            '__lunar_compiled_file' => $this->maliciousFile,
        ]);

        $this->assertStringNotContainsString('PWNED', $output);
        $this->assertStringContainsString('Hello World', $output);
    }

    public function testEngineVariableCannotBeOverridden(): void
    {
        // Le compilé n'utilise pas $engine ici, mais on vérifie qu'on ne
        // peut pas injecter une valeur arbitraire qui survive à l'extract.
        file_put_contents(
            $this->templatesDir . '/probe.tpl',
            '[[ probe_value ]]',
        );

        $engine = new AdvancedTemplateEngine(
            $this->templatesDir,
            $this->cacheDir,
            new FilesystemCache($this->cacheDir),
        );

        $output = $engine->render('probe', [
            'probe_value' => 'safe',
            'engine' => 'HIJACKED',
            'this' => 'HIJACKED',
        ]);

        $this->assertStringNotContainsString('HIJACKED', $output);
        $this->assertStringContainsString('safe', $output);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $entries = scandir($dir);
        $files = $entries !== false ? array_diff($entries, ['.', '..']) : [];
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}
