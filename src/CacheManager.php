<?php

declare(strict_types=1);

namespace Greg\Cache;

class CacheManager implements CacheStrategy
{
    private $strategies = [];

    private $defaultStrategy;

    public function defaultStrategy(string $name)
    {
        $this->defaultStrategy = $name;

        return $this;
    }

    public function register($name, callable $callable, bool $default = false)
    {
        $this->strategies[$name] = $callable;

        if ($default) {
            $this->defaultStrategy($name);
        }

        return $this;
    }

    public function registerStrategy($name, CacheStrategy $strategy, bool $default = false)
    {
        $this->strategies[$name] = $strategy;

        if ($default) {
            $this->defaultStrategy($name);
        }

        return $this;
    }

    public function strategy(?string $name = null): CacheStrategy
    {
        if (!$name = $name ?: $this->defaultStrategy) {
            throw new \Exception('Default cache strategy was not defined.');
        }

        if (!$strategy = $this->strategies[$name] ?? null) {
            throw new \Exception('Cache strategy `' . $name . '` was not defined.');
        }

        if (is_callable($strategy)) {
            $strategy = call_user_func_array($strategy, []);

            if (!($strategy instanceof CacheStrategy)) {
                throw new \Exception('Cache strategy `' . $name . '` must be an instance of `' . CacheStrategy::class . '`');
            }

            $this->strategies[$name] = $strategy;
        }

        return $this->get($name);
    }

    public function has(string $key): bool
    {
        return $this->strategy()->has($key);
    }

    public function hasMultiple(array $keys): bool
    {
        return $this->strategy()->hasMultiple($keys);
    }

    public function get(string $key, $default = null)
    {
        return $this->strategy()->get($key, $default);
    }

    public function getMultiple(array $keys, $default = null)
    {
        return $this->strategy()->getMultiple($keys, $default);
    }

    public function set(string $key, $value, ?int $ttl = null)
    {
        $this->strategy()->set($key, $value, $ttl);

        return $this;
    }

    public function setMultiple(array $values, ?int $ttl = null)
    {
        $this->strategy()->setMultiple($values, $ttl);

        return $this;
    }

    public function setForever(string $key, $value)
    {
        $this->strategy()->setForever($key, $value);

        return $this;
    }

    public function setMultipleForever(array $values)
    {
        $this->strategy()->setMultipleForever($values);

        return $this;
    }

    public function delete(string $key)
    {
        $this->strategy()->delete($key);

        return $this;
    }

    public function deleteMultiple(array $keys)
    {
        $this->strategy()->deleteMultiple($keys);

        return $this;
    }

    public function clear()
    {
        $this->strategy()->clear();

        return $this;
    }

    public function fetch(string $key, callable $callable, ?int $ttl = null)
    {
        return $this->strategy()->fetch($key, $callable, $ttl);
    }
}
