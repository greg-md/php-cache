<?php

declare(strict_types=1);

namespace Greg\Cache\Tests;

use Greg\Cache\SqliteCache;
use PHPUnit\Framework\TestCase;

class SqliteCacheTest extends TestCase
{
    private $db = __DIR__ . '/storage/cache.sqlite';

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var SqliteCache
     */
    private $cache;

    public function setUp()
    {
        parent::setUp();

        file_put_contents($this->db, '');

        $this->pdo = new \PDO('sqlite:' . $this->db);

        $this->cache = new SqliteCache($this->pdo);
    }

    public function tearDown()
    {
        parent::tearDown();

        unlink($this->db);
    }

    /** @test */
    public function it_determines_if_a_key_exists()
    {
        $this->add('foo', 'FOO');

        $this->add('expired', 'EXPIRED', -1);

        $this->assertTrue($this->cache->has('foo'));

        $this->assertFalse($this->cache->has('expired'));

        $this->assertFalse($this->cache->has('undefined'));
    }

    /** @test */
    public function it_determines_if_multiple_keys_exists()
    {
        $this->add('foo', 'FOO');

        $this->add('bar', 'BAR');

        $this->add('expired', 'EXPIRED', -1);

        $this->assertTrue($this->cache->hasMultiple(['foo', 'bar']));

        $this->assertFalse($this->cache->hasMultiple(['foo', 'expired']));
    }

    /** @test */
    public function it_gets_a_key()
    {
        $this->add('foo', 'FOO');

        $this->add('expired', 'EXPIRED', -1);

        $this->assertEquals('FOO', $this->cache->get('foo'));

        $this->assertNull($this->cache->get('expired'));

        $this->assertNull($this->cache->get('undefined'));
    }

    /** @test */
    public function it_gets_multiple_keys()
    {
        $this->add('foo', 'FOO');

        $this->add('bar', 'BAR');

        $this->add('expired', 'EXPIRED', -1);

        $this->assertEquals(['foo' => 'FOO', 'bar' => 'BAR'], $this->cache->getMultiple(['foo', 'bar']));

        $this->assertEquals(['foo' => 'FOO', 'expired' => null], $this->cache->getMultiple(['foo', 'expired']));
    }

    /** @test */
    public function it_sets_a_key_value()
    {
        $this->cache->set('foo', 'FOO');

        $stmt = $this->pdo->query('SELECT Value from Cache WHERE Key = "foo"');

        $this->assertEquals(serialize('FOO'), $stmt->fetchColumn());
    }

    /** @test */
    public function it_replaces_a_key_value()
    {
        $this->cache->set('foo', 'FOO');

        $this->cache->set('foo', 'FOO2');

        $stmt = $this->pdo->query('SELECT Value from Cache WHERE Key = "foo"');

        $this->assertEquals(serialize('FOO2'), $stmt->fetchColumn());
    }

    /** @test */
    public function it_deletes_a_key()
    {
        $this->add('foo', 'FOO');

        $this->cache->delete('foo');

        $stmt = $this->pdo->query('SELECT Value from Cache WHERE Key = "foo"');

        $this->assertFalse($stmt->fetchColumn());
    }

    /** @test */
    public function it_clears_keys()
    {
        $this->add('foo', 'FOO');

        $this->add('bar', 'BAR');

        $this->cache->clear();

        $stmt = $this->pdo->query('SELECT COUNT(*) from Cache');

        $this->assertEquals(0, $stmt->fetchColumn());
    }

    protected function add(string $key, $value, int $ttl = 300)
    {
        $stmt = $this->pdo->prepare('INSERT INTO Cache(Key, Value, ExpiresAt) VALUES (:Key, :Value, :ExpiresAt)');

        $stmt->bindValue(':Key', $key);

        $stmt->bindValue(':Value', serialize($value), \PDO::PARAM_LOB);

        $stmt->bindValue(':ExpiresAt', $ttl ? time() + $ttl : 0);

        $stmt->execute();
    }
}
