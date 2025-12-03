<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Security;

use Lunar\Template\Exception\TemplateException;
use Lunar\Template\Security\PathValidator;
use PHPUnit\Framework\TestCase;

class PathValidatorTest extends TestCase
{
    private string $tempDir;

    private PathValidator $validator;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/path-validator-test-' . uniqid();
        mkdir($this->tempDir, 0o755, true);
        mkdir($this->tempDir . '/templates', 0o755, true);
        file_put_contents($this->tempDir . '/templates/test.tpl', 'test');

        $this->validator = new PathValidator($this->tempDir . '/templates');
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = scandir($dir);
        if ($files === false) {
            return;
        }
        foreach (array_diff($files, ['.', '..']) as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testValidPathReturnsRealPath(): void
    {
        $result = $this->validator->validate('test.tpl');

        $this->assertStringContainsString('test.tpl', $result);
    }

    public function testDirectoryTraversalThrowsException(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Path traversal detected');

        $this->validator->validate('../../../etc/passwd');
    }

    public function testBackslashTraversalThrowsException(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Path traversal detected');

        $this->validator->validate('..\\..\\..\\etc\\passwd');
    }

    public function testNullByteRemoved(): void
    {
        $result = $this->validator->validate("test\0.tpl");

        $this->assertStringNotContainsString("\0", $result);
    }

    public function testGetBasePath(): void
    {
        $this->assertStringContainsString('templates', $this->validator->getBasePath());
    }

    public function testInvalidBasePathThrowsException(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Base path does not exist');

        new PathValidator('/non/existent/path');
    }

    public function testLeadingSlashRemoved(): void
    {
        $result = $this->validator->validate('/test.tpl');

        $this->assertStringContainsString('test.tpl', $result);
    }

    public function testNonExistentFileInValidDirectory(): void
    {
        $result = $this->validator->validate('nonexistent.tpl');

        $this->assertStringContainsString('nonexistent.tpl', $result);
    }

    public function testNonExistentFileWithTraversalThrowsException(): void
    {
        $this->expectException(TemplateException::class);
        $this->expectExceptionMessage('Path traversal detected');

        $this->validator->validate('../nonexistent.tpl');
    }
}
