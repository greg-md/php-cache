<?php

declare(strict_types=1);

namespace Greg\Cache\Tests;

use Greg\Cache\CacheException;
use Greg\Cache\FileCache;
use PHPUnit\Framework\TestCase;

class FileCacheTest extends TestCase
{
    /**
     * @var FileCache
     */
    private $cache;

    private $storage = __DIR__ . '/storage';

    public function setUp()
    {
        parent::setUp();

        $this->cache = new FileCache($this->storage, 300, 1, 1);
    }

    public function tearDown()
    {
        parent::tearDown();

        foreach (glob($this->storage . DIRECTORY_SEPARATOR . '*.cache.txt') as $file) {
            unlink($file);
        }

        if (is_dir(__DIR__ . '/read')) {
            rmdir(__DIR__ . '/read');
        }

        if (is_dir(__DIR__ . '/write')) {
            rmdir(__DIR__ . '/write');
        }
    }

    /** @test */
    public function it_collects_garbage()
    {
        $this->cache->set('foo', 'BAR', 1);

        sleep(1);

        $this->cache->collectGarbage();

        $this->assertFileNotExists($this->getFile('foo'));
    }

    /** @test */
    public function it_determine_if_a_key_exists()
    {
        $this->cache->set('foo', 'FOO');

        $this->cache->set('bar', 'BAR');

        $this->assertTrue($this->cache->has('foo'));

        $this->assertFalse($this->cache->has('expired'));

        $this->assertTrue($this->cache->hasMultiple(['foo', 'bar']));

        $this->assertFalse($this->cache->hasMultiple(['foo', 'expired']));
    }

    /** @test */
    public function it_gets_a_key()
    {
        $this->cache->set('foo', 'FOO');

        $this->cache->set('bar', 'BAR');

        $this->assertEquals('FOO', $this->cache->get('foo'));

        $this->assertEmpty($this->cache->get('expired'));

        $this->assertEquals(['foo' => 'FOO', 'bar' => 'BAR'], $this->cache->getMultiple(['foo', 'bar']));

        $this->assertEquals(['foo' => 'FOO', 'expired' => null], $this->cache->getMultiple(['foo', 'expired']));
    }

    /** @test */
    public function it_sets_a_key_value()
    {
        $this->cache->set('foo', 'FOO');

        $this->cache->setMultiple(['bar' => 'BAR', 'baz' => 'BAZ']);

        $this->assertFileExists($this->getFile('foo'));

        $this->assertFileExists($this->getFile('bar'));

        $this->assertFileExists($this->getFile('baz'));

        $this->assertFileNotExists($this->getFile('expired'));
    }

    /** @test */
    public function it_sets_a_key_value_forever()
    {
        $this->cache->setForever('foo', 'FOO');

        $this->cache->setMultipleForever(['bar' => 'BAR', 'baz' => 'BAZ']);

        $this->cache->collectGarbage();

        $this->assertFileExists($this->getFile('foo'));

        $this->assertFileExists($this->getFile('bar'));

        $this->assertFileExists($this->getFile('baz'));
    }

    /** @test */
    public function it_replaces_a_key_value()
    {
        $this->cache->set('foo', 'FOO');

        $this->cache->set('foo', 'FOO2');

        $this->assertEquals('FOO2', $this->cache->get('foo'));
    }

    /** @test */
    public function it_deletes_a_key()
    {
        $this->cache->set('foo', 'FOo');

        $this->cache->setMultiple(['bar' => 'BAR', 'baz' => 'BAZ']);

        $this->cache->delete('foo');

        $this->cache->deleteMultiple(['bar', 'baz']);

        $this->assertFalse($this->cache->has('foo'));

        $this->assertFalse($this->cache->hasMultiple(['bar', 'baz']));
    }

    /** @test */
    public function it_clears_keys()
    {
        $this->cache->set('foo', 'FOo');

        $this->cache->setMultiple(['bar' => 'BAR', 'baz' => 'BAZ']);

        $this->cache->clear();

        $this->assertFalse($this->cache->has('foo'));

        $this->assertFalse($this->cache->hasMultiple(['bar', 'baz']));
    }

    /** @test */
    public function it_fetches_a_key()
    {
        $this->cache->fetch('foo', function () {
            return 'FOO';
        });

        // Value is already registered and will not use the new one.
        $value = $this->cache->fetch('foo', function () {
            return 'FOO2';
        });

        $this->assertEquals('FOO', $value);
    }

    /** @test */
    public function it_throws_exception_if_ttl_is_negative()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->cache->set('foo', 'FOO', -1);
    }

    /** @test */
    public function it_throws_exception_if_path_not_exists()
    {
        $this->expectException(CacheException::class);

        $cache = new FileCache(__DIR__ . '/undefined');

        $cache->set('foo', 'FOO');
    }

    /** @test */
    public function it_throws_exception_if_path_not_readable()
    {
        mkdir(__DIR__ . '/write', 0300);

        $this->expectException(CacheException::class);

        $cache = new FileCache(__DIR__ . '/write');

        $cache->set('foo', 'FOO');
    }

    /** @test */
    public function it_throws_exception_if_path_not_writable()
    {
        mkdir(__DIR__ . '/read', 0500);

        $this->expectException(CacheException::class);

        $cache = new FileCache(__DIR__ . '/read');

        $cache->set('foo', 'FOO');
    }

    /** @test */
    public function it_throws_exception_if_cache_item_not_readable()
    {
        $this->cache->set('foo', 'FOO');

        chmod($this->getFile('foo'), 0300);

        $this->expectException(CacheException::class);

        $this->cache->get('foo');
    }

    protected function getFile(string $key): string
    {
        return $this->storage . DIRECTORY_SEPARATOR . md5($key) . '.cache.txt';
    }
}
