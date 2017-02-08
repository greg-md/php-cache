<?php
declare(strict_types=1);

namespace Greg\Cache;

class RedisCache extends CacheAbstract
{
    private $adapter;

    public function __construct(\Redis $adapter, int $ttl = 300)
    {
        $this->adapter = $adapter;

        $this->ttl = 300;

        return $this;
    }

    public function has(string $key): bool
    {
        return $this->adapter->exists($key);
    }

    public function get(string $key, $default = null)
    {
        if ($contents = $this->adapter->get($key)) {
            return unserialize($contents);
        }

        return null;
    }

    public function set(string $key, $value, ?int $ttl = null)
    {
        if ($ttl = $this->getTTL($ttl)) {
            $this->adapter->setex($key, $ttl, serialize($value));
        } else {
            $this->adapter->set($key, serialize($value));
        }

        return $this;
    }

    public function delete(string $key)
    {
        $this->adapter->delete($key);

        return $this;
    }

    public function clear()
    {
        $prefix = $this->adapter->getOption(\Redis::OPT_PREFIX);

        $keys = $this->adapter->keys('*');

        $this->adapter->setOption(\Redis::OPT_PREFIX, '');

        $this->adapter->delete($keys);

        $this->adapter->setOption(\Redis::OPT_PREFIX, $prefix);

        return $this;
    }
}
