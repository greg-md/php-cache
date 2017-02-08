<?php

declare(strict_types=1);

namespace Greg\Cache;

abstract class CacheAbstract implements CacheStrategy
{
    protected $ttl = 300;

    public function fetch(string $key, callable $callable, ?int $ttl = null)
    {
        if (!$this->has($key)) {
            $this->set($key, $value = call_user_func_array($callable, []), $ttl);
        } else {
            $value = $this->get($key);
        }

        return $value;
    }

    public function hasMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->has($key)) {
                return false;
            }
        }

        return true;
    }

    public function getMultiple(array $keys, $default = null)
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    public function setMultiple(array $values, ?int $ttl = null)
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return $this;
    }

    public function setForever(string $key, $value)
    {
        return $this->set($key, $value, 0);
    }

    public function setMultipleForever(array $values)
    {
        return $this->setMultiple($values, 0);
    }

    public function deleteMultiple(array $keys)
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return $this;
    }

    protected function getTTL(?int $ttl = null)
    {
        if ($ttl === null) {
            $ttl = $this->ttl;
        }

        return $this->validateTTL($ttl);
    }

    protected function validateTTL(int $ttl)
    {
        if ($ttl < 0) {
            throw new \InvalidArgumentException('TTL could not be negative.');
        }

        return $ttl;
    }

    protected function isExpired(?int $time): bool
    {
        return $time === null or $time === '' or $time < 0 or ($time > 0 and $time <= time());
    }

    protected function getExpiresAt(?int $ttl = null)
    {
        $ttl = $this->getTTL($ttl);

        return $ttl > 0 ? time() + $ttl : 0;
    }
}
