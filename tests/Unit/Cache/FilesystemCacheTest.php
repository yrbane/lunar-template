<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Cache;

use Lunar\Template\Cache\CacheInterface;
use Lunar\Template\Cache\FilesystemCache;
use PHPUnit\Framework\TestCase;

class FilesystemCacheTest extends TestCase
{
    private string $tempDir;

    private FilesystemCache $cache;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/cache-test-' . uniqid();
        mkdir($this->tempDir, 0o755, true);
        $this->cache = new FilesystemCache($this->tempDir);
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

    public function testImplementsCacheInterface(): void
    {
        $this->assertInstanceOf(CacheInterface::class, $this->cache);
    }

    public function testSetAndGet(): void
    {
        $this->cache->set('test-key', '<?php echo "Hello";');

        $content = $this->cache->get('test-key');

        $this->assertSame('<?php echo "Hello";', $content);
    }

    public function testGetReturnsNullForMissing(): void
    {
        $result = $this->cache->get('nonexistent');

        $this->assertNull($result);
    }

    public function testHasReturnsTrueWhenFresh(): void
    {
        $this->cache->set('test-key', 'content');

        $sourceTime = time() - 100; // Source is older

        $this->assertTrue($this->cache->has('test-key', $sourceTime));
    }

    public function testHasReturnsFalseWhenStale(): void
    {
        $this->cache->set('test-key', 'content');

        // Touch the file to make it older
        touch($this->tempDir . '/test-key.php', time() - 200);

        $sourceTime = time(); // Source is newer

        $this->assertFalse($this->cache->has('test-key', $sourceTime));
    }

    public function testHasReturnsFalseForMissing(): void
    {
        $this->assertFalse($this->cache->has('nonexistent', time()));
    }

    public function testDelete(): void
    {
        $this->cache->set('test-key', 'content');
        $this->assertNotNull($this->cache->get('test-key'));

        $this->cache->delete('test-key');

        $this->assertNull($this->cache->get('test-key'));
    }

    public function testDeleteNonexistent(): void
    {
        // Should not throw
        $this->cache->delete('nonexistent');

        $this->assertNull($this->cache->get('nonexistent'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'content1');
        $this->cache->set('key2', 'content2');

        $this->cache->clear();

        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    public function testGetPath(): void
    {
        $this->cache->set('test-key', 'content');

        $path = $this->cache->getPath('test-key');

        $this->assertNotNull($path);
        $this->assertFileExists($path);
    }

    public function testGetPathReturnsNullForMissing(): void
    {
        $result = $this->cache->getPath('nonexistent');

        $this->assertNull($result);
    }

    public function testGetDirectory(): void
    {
        $this->assertSame($this->tempDir, $this->cache->getDirectory());
    }

    public function testCustomExtension(): void
    {
        $cache = new FilesystemCache($this->tempDir, '.cache');
        $cache->set('test', 'content');

        $this->assertFileExists($this->tempDir . '/test.cache');
    }

    public function testCreatesDirectoryIfNotExists(): void
    {
        $newDir = $this->tempDir . '/new-cache-dir';
        $this->assertDirectoryDoesNotExist($newDir);

        new FilesystemCache($newDir);

        $this->assertDirectoryExists($newDir);
    }

    public function testTrailingSlashNormalized(): void
    {
        $cache = new FilesystemCache($this->tempDir . '/');

        $this->assertSame($this->tempDir, $cache->getDirectory());
    }
}
