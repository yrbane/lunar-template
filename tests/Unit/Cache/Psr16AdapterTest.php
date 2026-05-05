<?php

declare(strict_types=1);

namespace Lunar\Template\Tests\Unit\Cache;

use Lunar\Template\Cache\CacheInterface;
use Lunar\Template\Cache\Psr16Adapter;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface as Psr16CacheInterface;

class Psr16AdapterTest extends TestCase
{
    private string $tempDir;
    private Psr16CacheInterface $psr;
    private Psr16Adapter $adapter;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/lunar_psr16_' . uniqid();
        mkdir($this->tempDir);
        $this->psr = $this->createInMemoryPsr16();
        $this->adapter = new Psr16Adapter($this->psr, $this->tempDir);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testImplementsCacheInterface(): void
    {
        $this->assertInstanceOf(CacheInterface::class, $this->adapter);
    }

    public function testSetAndGet(): void
    {
        $this->adapter->set('hello', '<?php echo "Hi";');

        $this->assertSame('<?php echo "Hi";', $this->adapter->get('hello'));
    }

    public function testGetReturnsNullForMissing(): void
    {
        $this->assertNull($this->adapter->get('missing'));
    }

    public function testHasFalseForMissing(): void
    {
        $this->assertFalse($this->adapter->has('missing', time()));
    }

    public function testHasTrueWhenFreshComparedToSource(): void
    {
        $this->adapter->set('k', 'content');

        // Source plus ancienne que le cache → has() vrai
        $sourceTime = time() - 100;
        $this->assertTrue($this->adapter->has('k', $sourceTime));
    }

    public function testHasFalseWhenStale(): void
    {
        // Cache écrit AVEC un mtime stocké dans le passé
        $this->adapter->set('k', 'content');
        // Forcer un timestamp plus ancien (la clé compagne suit la convention
        // documentée par l'adaptateur : '<key>.lunar_mtime').
        $this->psr->set('k.lunar_mtime', time() - 200);

        $this->assertFalse($this->adapter->has('k', time()));
    }

    public function testDelete(): void
    {
        $this->adapter->set('k', 'content');
        $this->adapter->delete('k');

        $this->assertNull($this->adapter->get('k'));
    }

    public function testClear(): void
    {
        $this->adapter->set('a', '1');
        $this->adapter->set('b', '2');
        $this->adapter->clear();

        $this->assertNull($this->adapter->get('a'));
        $this->assertNull($this->adapter->get('b'));
    }

    public function testGetPathMaterialisesContent(): void
    {
        $this->adapter->set('k', '<?= 1 ?>');

        $path = $this->adapter->getPath('k');

        $this->assertNotNull($path);
        $this->assertFileExists($path);
        $this->assertSame('<?= 1 ?>', file_get_contents($path));
    }

    public function testGetPathReturnsNullForMissing(): void
    {
        $this->assertNull($this->adapter->getPath('missing'));
    }

    public function testGetDirectoryReturnsWriteDir(): void
    {
        $this->assertSame($this->tempDir, $this->adapter->getDirectory());
    }

    private function createInMemoryPsr16(): Psr16CacheInterface
    {
        return new class () implements Psr16CacheInterface {
            /** @var array<string, mixed> */
            private array $data = [];

            public function get(string $key, mixed $default = null): mixed
            {
                return $this->data[$key] ?? $default;
            }

            public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
            {
                $this->data[$key] = $value;

                return true;
            }

            public function delete(string $key): bool
            {
                unset($this->data[$key]);

                return true;
            }

            public function clear(): bool
            {
                $this->data = [];

                return true;
            }

            public function getMultiple(iterable $keys, mixed $default = null): iterable
            {
                $result = [];
                foreach ($keys as $key) {
                    $result[$key] = $this->data[$key] ?? $default;
                }

                return $result;
            }

            public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
            {
                foreach ($values as $k => $v) {
                    $this->data[$k] = $v;
                }

                return true;
            }

            public function deleteMultiple(iterable $keys): bool
            {
                foreach ($keys as $k) {
                    unset($this->data[$k]);
                }

                return true;
            }

            public function has(string $key): bool
            {
                return \array_key_exists($key, $this->data);
            }
        };
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $f) {
            $p = $dir . '/' . $f;
            is_dir($p) ? $this->removeDirectory($p) : unlink($p);
        }
        rmdir($dir);
    }
}
