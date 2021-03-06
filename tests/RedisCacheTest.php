<?php

namespace Greg\Cache;

use PHPUnit\Framework\TestCase;

class RedisCacheTest extends TestCase
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var RedisCache
     */
    private $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = new \Redis();

        $this->redis->connect('127.0.0.1');

        $this->redis->setOption(\Redis::OPT_PREFIX, 'test_');

        $this->cache = new RedisCache($this->redis);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $prefix = $this->redis->getOption(\Redis::OPT_PREFIX);

        $keys = $this->redis->keys('*');

        $this->redis->setOption(\Redis::OPT_PREFIX, '');

        $this->redis->del($keys);

        $this->redis->setOption(\Redis::OPT_PREFIX, (string) $prefix);
    }

    /** @test */
    public function it_determines_if_a_key_exists()
    {
        $this->redis->set('foo', serialize('FOO'));

        $this->assertTrue($this->cache->has('foo'));
    }

    /** @test */
    public function it_gets_a_key()
    {
        $this->redis->set('foo', serialize('FOO'));

        $this->assertEquals('FOO', $this->cache->get('foo'));
    }

    /** @test */
    public function it_sets_a_key_value()
    {
        $this->cache->set('foo', 'FOO');

        $this->assertEquals(serialize('FOO'), $this->redis->get('foo'));
    }

    /** @test */
    public function it_deletes_a_key()
    {
        $this->redis->set('foo', serialize('FOO'));

        $this->cache->delete('foo');

        $this->assertEquals(0, $this->redis->exists('foo'));
    }

    /** @test */
    public function it_clears_keys()
    {
        $this->redis->set('foo', serialize('FOO'));

        $this->redis->set('bar', serialize('BAR'));

        $this->cache->clear();

        $this->assertEmpty($this->redis->keys('*'));
    }
}
